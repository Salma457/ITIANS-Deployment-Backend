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
        Schema::create('itian_profiles', function (Blueprint $table) {
            $table->id('itian_profile_id');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('profile_picture', 500)->nullable();
            $table->text('bio')->nullable();
            $table->string('iti_track', 100);
            $table->integer('graduation_year');
            $table->string('cv', 500)->nullable();
            $table->string('portfolio_url', 500)->nullable();
            $table->string('linkedin_profile_url', 500)->nullable();
            $table->string('github_profile_url', 500)->nullable();
            $table->boolean('is_open_to_work')->default(false);
            $table->integer('experience_years')->default(0);
            $table->string('current_job_title', 255)->nullable();
            $table->string('current_company', 255)->nullable();
            $table->string('preferred_job_locations', 500)->nullable(); 
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('itian_profiles');
    }
    
};
