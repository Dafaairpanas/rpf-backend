<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pindahkan semua content dari news ke news_contents
        $newsItems = DB::table('news')->whereNotNull('content')->get();

        foreach ($newsItems as $news) {
            DB::table('news_contents')->insert([
                'news_id' => $news->id,
                'content' => $news->content,
                'created_at' => $news->created_at ?? now(),
                'updated_at' => $news->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan content ke tabel news
        $contents = DB::table('news_contents')->get();

        foreach ($contents as $content) {
            DB::table('news')->where('id', $content->news_id)->update([
                'content' => $content->content,
            ]);
        }

        DB::table('news_contents')->truncate();
    }
};
