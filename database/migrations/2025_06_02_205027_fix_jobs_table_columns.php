<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First check if we're using PostgreSQL
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';
        
        Schema::table('jobs', function (Blueprint $table) use ($isPostgres) {
            // Add queue column if not exists
            if (!Schema::hasColumn('jobs', 'queue')) {
                $table->string('queue', 191)->index()->default('default');
            }
            
            // Add attempts column if not exists
            if (!Schema::hasColumn('jobs', 'attempts')) {
                $table->unsignedInteger('attempts')->default(0);
            }
    
                if (!Schema::hasColumn('jobs', 'reserved_at')) {
                    $table->timestamp('reserved_at')->nullable();
                }
                if (!Schema::hasColumn('jobs', 'available_at')) {
                    $table->timestamp('available_at')->nullable();
                }
                if (!Schema::hasColumn('jobs', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
            
            if (!Schema::hasColumn('jobs', 'payload')) {
                $table->longText('payload')->nullable();
            }
        });


    }

    public function down(): void
    {
        // Only drop columns that were added by this migration
        Schema::table('jobs', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('jobs', 'queue')) {
                $columnsToDrop[] = 'queue';
            }
            if (Schema::hasColumn('jobs', 'attempts')) {
                $columnsToDrop[] = 'attempts';
            }
            if (Schema::hasColumn('jobs', 'payload')) {
                $columnsToDrop[] = 'payload';
            }

            if (!Schema::hasColumn('jobs', 'reserved_at') || DB::getSchemaBuilder()->getColumnType('jobs', 'reserved_at') === 'timestamp') {
                $columnsToDrop[] = 'reserved_at';
            }
            if (!Schema::hasColumn('jobs', 'available_at') || DB::getSchemaBuilder()->getColumnType('jobs', 'available_at') === 'timestamp') {
                $columnsToDrop[] = 'available_at';
            }
            if (!Schema::hasColumn('jobs', 'created_at') || DB::getSchemaBuilder()->getColumnType('jobs', 'created_at') === 'timestamp') {
                $columnsToDrop[] = 'created_at';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};