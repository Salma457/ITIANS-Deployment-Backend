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
        Schema::create('itian_skills', function (Blueprint $table) {
            $table->unsignedBigInteger('itian_profile_id');
           
            
            $table->string('skill_name', 100);
            $table->primary(['itian_profile_id', 'skill_name']);
            $table->timestamps();
    
            $table->foreign('itian_profile_id')->references('itian_profile_id')->on('itian_profiles')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itian_skills');
    }
};
