<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('alt', 255)->nullable()->after('image_url');
            $table->unsignedInteger('order')->default(0)->after('alt');

            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex(['order']);
            $table->dropColumn(['alt', 'order']);
        });
    }
};
