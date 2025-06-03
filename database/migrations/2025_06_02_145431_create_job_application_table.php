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
        Schema::create('job_application', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itian_id')->constrained('itian_profiles', 'itian_profile_id')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->timestamp('application_date');
            $table->text('cover_letter');
            $table->string('status', 50);
            $table->string('cv')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_application');
    }
};
