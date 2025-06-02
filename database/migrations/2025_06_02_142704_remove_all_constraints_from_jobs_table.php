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
        Schema::table('jobs', function (Blueprint $table) {
            DB::statement('ALTER TABLE jobs DROP CONSTRAINT IF EXISTS chk_job_type');
            DB::statement('ALTER TABLE jobs DROP CONSTRAINT IF EXISTS chk_status');
            DB::statement('ALTER TABLE jobs DROP CONSTRAINT IF EXISTS chk_job_location');
            
            // Drop any other potential constraints (add more if you have them)
            DB::statement('ALTER TABLE jobs DROP CONSTRAINT IF EXISTS chk_status_correct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (config('database.default') !== 'sqlite') {
                DB::statement("ALTER TABLE jobs ADD CONSTRAINT chk_job_type CHECK (job_type IN ('Full-time', 'Part-time', 'Internship', 'Freelance'))");
                DB::statement("ALTER TABLE jobs ADD CONSTRAINT chk_status CHECK (status IN ('pending', 'approved', 'rejected'))");
                DB::statement("ALTER TABLE jobs ADD CONSTRAINT chk_job_location CHECK (job_location IN ('on-site', 'Remote', 'Hybrid'))");
            }
        });
    }
};
