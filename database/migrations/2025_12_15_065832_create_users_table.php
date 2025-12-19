<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique()->nullable();
            $table->string('name', 100)->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->string('password');
            $table->string('division', 100)->nullable();
            $table->foreignId('role_id')->nullable()
                ->constrained('roles')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
