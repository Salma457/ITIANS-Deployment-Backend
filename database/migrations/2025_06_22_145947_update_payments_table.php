<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn('status');
        $table->boolean('used_for_job')->default(false);
    });
}

public function down()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->boolean('status')->default('pending');
        $table->dropColumn('used_for_job');
    });
}
};
