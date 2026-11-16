<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Common
            $table->id();
            $table->string('user_id',20)->unique()->nullable();
            $table->string('name',70)->comment('comapny owner, sub agent, superadmin');
            $table->char('mobile',10)->unique();
            $table->string('email',70)->unique();
            $table->string('password',100);
            $table->rememberToken();
            $table->char('otp',4)->nullable();
            $table->enum('role', ['Superadmin','Agent','Sub Agent'])->default('Agent');
            $table->char('profile_photo',30)->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->char('pin_code',6)->nullable();
            $table->text('address')->nullable();
            $table->char('aadhar_no',12)->nullable();
            $table->char('aadhar_front_photo',30)->nullable();
            $table->char('aadhar_back_photo',30)->nullable();
            $table->char('pan_no',10)->nullable();
            $table->char('pan_photo',30)->nullable();

            // Agent
            $table->string('company_name')->nullable();
            $table->char('gst_no',15)->nullable();
            $table->char('company_logo',30)->nullable();

            // Sub Agent
            $table->bigInteger('company_id')->nullable()->comment('Company Id in Users Table');
            $table->string('agent_code', 30)->nullable()->index();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->onDelete('set null');
            $table->date('dob')->nullable();

            $table->tinyInteger('status')->default(0)->comment('0 = Pending, 1 = Approved, 2 = Rejected, 3 = Deactivated');
            $table->bigInteger('deactivated_by_superadmin_id')->nullable();
            $table->bigInteger('deactivated_agent_subagent_by_superadmin_id')->nullable();
            $table->date('approved_date')->nullable()->comment('joining date');
            $table->string('rejected_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
