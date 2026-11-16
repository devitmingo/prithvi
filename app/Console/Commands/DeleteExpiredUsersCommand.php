<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteExpiredUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete all user records and files for users whose deletion request was submitted more than 90 days ago and have not logged in.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(90);
        $this->info("Scanning for users with deletion requests prior to: " . $cutoffDate->toDateTimeString());

        $expiredUsers = User::whereNotNull('deletion_requested_at')
            ->where('deletion_requested_at', '<=', $cutoffDate)
            ->get();

        $count = $expiredUsers->count();

        if ($count === 0) {
            $this->info("No expired deletion requests found.");
            return Command::SUCCESS;
        }

        $this->info("Found {$count} user(s) to permanently purge.");

        $purgedCount = 0;
        foreach ($expiredUsers as $user) {
            try {
                $userData = [
                    'id' => $user->id,
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'deletion_requested_at' => $user->deletion_requested_at,
                ];

                $user->deleteWithAllRecords();
                $purgedCount++;

                Log::info("User account permanently deleted after 90-day grace period.", $userData);
                $this->line("Purged user ID {$userData['id']} ({$userData['name']} - {$userData['email']})");
            } catch (\Exception $e) {
                Log::error("Failed to delete expired user ID {$user->id}: " . $e->getMessage(), [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Error purging user ID {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully purged {$purgedCount} of {$count} expired user account(s).");
        return Command::SUCCESS;
    }
}
