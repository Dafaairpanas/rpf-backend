<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name');
            $table->index('created_at');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->index('title');
            $table->index('created_at');
        });

        Schema::table('csrs', function (Blueprint $table) {
            $table->index('title');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('csrs', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['created_at']);
        });
    }
};
