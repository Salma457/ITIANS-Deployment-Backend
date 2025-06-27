<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::connection('supabase')->statement("
            CREATE TABLE IF NOT EXISTS itian_rag_knowledge (
                id SERIAL PRIMARY KEY,
                source_type VARCHAR(50),
                source_id INTEGER,
                title TEXT,
                content TEXT,
                embedding VECTOR(1536)
            );
        ");
    }

    public function down(): void
    {
        DB::connection('supabase')->statement("DROP TABLE IF EXISTS itian_rag_knowledge;");
    }
};
