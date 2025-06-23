<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->foreignId('reporter_user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->string('report_status', 50)->default('Pending');
            $table->foreignId('resolved_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); // يضيف created_at و updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
