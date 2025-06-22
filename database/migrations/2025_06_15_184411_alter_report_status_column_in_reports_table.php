<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('report_status');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->enum('report_status', ['Pending', 'Resolved', 'Rejected'])->default('Pending');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('report_status');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->string('report_status', 50)->default('Pending');
        });
    }
};
