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
        // UTF8MB4 ve Turkish collation ayarları
        DB::statement('ALTER TABLE atik_merkezleri CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        
        // FULLTEXT index ekle (ngram parser olmadan - daha uyumlu)
        DB::statement('ALTER TABLE atik_merkezleri ADD FULLTEXT search_index (title, content, adres)');
        
        // Ayrı ayrı FULLTEXT indexler de ekleyebiliriz (daha esnek arama için)
        DB::statement('ALTER TABLE atik_merkezleri ADD FULLTEXT title_index (title)');
        DB::statement('ALTER TABLE atik_merkezleri ADD FULLTEXT content_index (content)');
        DB::statement('ALTER TABLE atik_merkezleri ADD FULLTEXT adres_index (adres)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // FULLTEXT indexleri kaldır
        DB::statement('ALTER TABLE atik_merkezleri DROP INDEX search_index');
        DB::statement('ALTER TABLE atik_merkezleri DROP INDEX title_index');
        DB::statement('ALTER TABLE atik_merkezleri DROP INDEX content_index');
        DB::statement('ALTER TABLE atik_merkezleri DROP INDEX adres_index');
    }
};
