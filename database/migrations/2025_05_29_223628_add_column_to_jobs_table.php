<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'queue',
                'payload',
                'attempts',
                'reserved_at',
                'available_at',
                'created_at'
            ]);

            $table->string('job_title', 255)->nullable();
            $table->text('description')->nullable()->after('job_title');
            $table->text('requirements')->nullable()->after('description');
            $table->text('qualifications')->nullable()->after('requirements');
            
            // Job location with enum-like values
            $table->string('job_location', 20)
                  ->nullable()
                  ->after('qualifications')
                  ->comment('on-site, Remote, Hybrid');
            
            // Job type with enum-like values
            $table->string('job_type', 20)
                  ->nullable()
                  ->after('job_location')
                  ->comment('Full-time, Part-time, Internship, Freelance');
            
            $table->decimal('salary_range_min', 10, 2)->nullable()->after('job_type');
            $table->decimal('salary_range_max', 10, 2)->nullable()->after('salary_range_min');
            $table->string('currency', 10)->nullable()->after('salary_range_max');
            $table->timestamp('posted_date')->nullable()->after('currency');
            $table->date('application_deadline')->nullable()->after('posted_date');
            
            $table->string('status', 20)
                ->default('Open')
                ->after('application_deadline')
                ->comment('Open, Closed, Pending');
            
            // Add indexes
            $table->index('job_type');
            $table->index('status');
            $table->index('posted_date');
        });

        // Add database-level enum constraints (for MySQL/PostgreSQL)
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE jobs ADD CONSTRAINT chk_job_type CHECK (job_type IN ('Full-time', 'Part-time', 'Internship', 'Freelance'))");
            DB::statement("ALTER TABLE jobs ADD CONSTRAINT chk_status CHECK (status IN ('pending', 'approved', 'rejected'))");
            DB::statement("ALTER TABLE jobs ADD CONSTRAINT chk_job_location CHECK (job_location IN ('on-site', 'Remote', 'Hybrid'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Drop constraints first (for MySQL/PostgreSQL)
            if (config('database.default') !== 'sqlite') {
                DB::statement('ALTER TABLE jobs DROP CONSTRAINT IF EXISTS chk_job_type');
                DB::statement('ALTER TABLE jobs DROP CONSTRAINT IF EXISTS chk_status');
                DB::statement('ALTER TABLE jobs DROP CONSTRAINT IF EXISTS chk_job_location');
            }

            // Drop indexes
            $table->dropIndex(['job_type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['posted_date']);
            
            // Drop the added columns
            $table->dropColumn([
                'job_title',
                'description',
                'requirements',
                'qualifications',
                'job_location',
                'job_type',
                'salary_range_min',
                'salary_range_max',
                'currency',
                'posted_date',
                'application_deadline',
                'status',
                'views_count'
            ]);
        });
    }
};