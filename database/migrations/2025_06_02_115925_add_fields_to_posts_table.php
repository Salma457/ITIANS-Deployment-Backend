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
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('itian_id');
            $table->string('title');
            $table->text('content');
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->boolean('is_published')->default(true);

            $table->foreign('itian_id')->references('itian_profile_id')->on('itian_profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
             $table->dropForeign(['itian_id']);
            $table->dropColumn([
                'post_id',
                'itian_id',
                'title',
                'content',
                'views_count',
                'likes_count',
                'is_published'

            ]);

        });
    }
};
