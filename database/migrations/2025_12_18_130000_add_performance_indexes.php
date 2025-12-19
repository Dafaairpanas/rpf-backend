<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Check if index exists (database-agnostic)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $result = DB::select("
                SELECT indexname FROM pg_indexes 
                WHERE tablename = ? AND indexname = ?
            ", [$table, $indexName]);
            return count($result) > 0;
        }

        if ($driver === 'sqlite') {
            $result = DB::select("
                SELECT name FROM sqlite_master 
                WHERE type = 'index' AND tbl_name = ? AND name = ?
            ", [$table, $indexName]);
            return count($result) > 0;
        }

        if ($driver === 'mysql') {
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        }

        // Default: skip check, let it fail if duplicate
        return false;
    }

    /**
     * Safely add index if it doesn't exist
     */
    private function addIndexSafe(string $table, array|string $columns, ?string $indexName = null): void
    {
        $cols = is_array($columns) ? $columns : [$columns];
        $name = $indexName ?? $table . '_' . implode('_', $cols) . '_index';

        if (!$this->indexExists($table, $name)) {
            Schema::table($table, function (Blueprint $t) use ($cols, $name) {
                $t->index($cols, $name);
            });
        }
    }

    /**
     * Performance indexes untuk foreign keys dan filter columns.
     * PostgreSQL tidak auto-create index untuk FK, jadi kita buat manual.
     */
    public function up(): void
    {
        // Products - FK indexes dan filter columns
        $this->addIndexSafe('products', 'master_category_id');
        $this->addIndexSafe('products', 'dimension_id');
        $this->addIndexSafe('products', 'create_by');
        $this->addIndexSafe('products', 'is_featured');
        $this->addIndexSafe('products', ['is_featured', 'created_at']);

        // News - FK indexes
        $this->addIndexSafe('news', 'create_by');
        $this->addIndexSafe('news', 'is_top_news');

        // CSRs - FK indexes
        $this->addIndexSafe('csrs', 'create_by');

        // Users - FK indexes
        $this->addIndexSafe('users', 'role_id');

        // Content tables - FK indexes
        $this->addIndexSafe('news_contents', 'news_id');
        $this->addIndexSafe('csr_contents', 'csr_id');

        // Image tables - FK indexes
        $this->addIndexSafe('product_images', 'product_id');
        $this->addIndexSafe('teak_images', 'product_id');
        $this->addIndexSafe('cover_images', 'product_id');

        // Banners - filter columns
        $this->addIndexSafe('banners', 'is_active');
        $this->addIndexSafe('banners', 'order');

        // Contact messages - filter columns
        $this->addIndexSafe('contact_messages', 'status');
        $this->addIndexSafe('contact_messages', 'created_at');
    }

    public function down(): void
    {
        $indexes = [
            'products' => ['master_category_id', 'dimension_id', 'create_by', 'is_featured', ['is_featured', 'created_at']],
            'news' => ['create_by', 'is_top_news'],
            'csrs' => ['create_by'],
            'users' => ['role_id'],
            'news_contents' => ['news_id'],
            'csr_contents' => ['csr_id'],
            'product_images' => ['product_id'],
            'teak_images' => ['product_id'],
            'cover_images' => ['product_id'],
            'banners' => ['is_active', 'order'],
            'contact_messages' => ['status', 'created_at'],
        ];

        foreach ($indexes as $table => $cols) {
            Schema::table($table, function (Blueprint $t) use ($table, $cols) {
                foreach ($cols as $col) {
                    $colArray = is_array($col) ? $col : [$col];
                    $indexName = $table . '_' . implode('_', $colArray) . '_index';
                    $t->dropIndex($indexName);
                }
            });
        }
    }
};
