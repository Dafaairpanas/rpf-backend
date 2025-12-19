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
        Schema::create('csr_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('csr_id')
                ->constrained('csrs')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->longText('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csr_contents');
    }
};
