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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->longText('detailed_description')->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->decimal('price', 8, 2)->nullable();
            $table->string('rera_no',50)->nullable();
            $table->string('image',30)->nullable();
            $table->string('document',30)->nullable();
            $table->string('availability_document',30)->nullable();
            $table->enum('project_status', ['Ongoing', 'Upcoming', 'Completed'])->default('Upcoming');
            $table->boolean('status')->default(true)->comment('0 = Inactive, 1 = Active');
            $table->timestamps();

            $table->unique(['user_id', 'city_id', 'name'], 'unique_user_city_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
