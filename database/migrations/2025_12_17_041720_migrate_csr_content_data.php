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
        // Pindahkan semua content dari csrs ke csr_contents
        $csrs = DB::table('csrs')->whereNotNull('content')->get();

        foreach ($csrs as $csr) {
            DB::table('csr_contents')->insert([
                'csr_id' => $csr->id,
                'content' => $csr->content,
                'created_at' => $csr->created_at ?? now(),
                'updated_at' => $csr->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan content ke tabel csrs
        $contents = DB::table('csr_contents')->get();

        foreach ($contents as $content) {
            DB::table('csrs')->where('id', $content->csr_id)->update([
                'content' => $content->content,
            ]);
        }

        DB::table('csr_contents')->truncate();
    }
};
