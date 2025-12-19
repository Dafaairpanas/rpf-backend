<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->string('material', 255)->nullable();

            $table->foreignId('master_category_id')->nullable()
                ->constrained('master_categories')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('dimension_id')->nullable()
                ->constrained('dimensions')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('create_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
