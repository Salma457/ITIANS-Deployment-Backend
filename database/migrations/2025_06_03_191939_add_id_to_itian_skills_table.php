<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
       
        DB::statement('ALTER TABLE itian_skills DROP CONSTRAINT itian_skills_pkey');

       
        Schema::table('itian_skills', function (Blueprint $table) {
            $table->id()->first(); 
        });

       
        Schema::table('itian_skills', function (Blueprint $table) {
            $table->unique(['itian_profile_id', 'skill_name']);
        });
    }

    public function down(): void
    {
       
        Schema::table('itian_skills', function (Blueprint $table) {
            $table->dropUnique(['itian_profile_id', 'skill_name']);
        });

       
        Schema::table('itian_skills', function (Blueprint $table) {
            $table->dropColumn('id');
        });

       
        DB::statement('ALTER TABLE itian_skills ADD PRIMARY KEY (itian_profile_id, skill_name)');
    }
};

