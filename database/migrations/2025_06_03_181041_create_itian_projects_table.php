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
        Schema::create('itian_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('itian_profile_id');
            $table->string('project_title', 255);
            $table->text('description')->nullable();
            $table->string('project_link')->nullable();
            $table->timestamps();
    
            $table->foreign('itian_profile_id')->references('itian_profile_id')->on('itian_profiles')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itian_projects');
    }
};
