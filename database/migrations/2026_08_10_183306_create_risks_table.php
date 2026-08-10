<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description');
            $table->string('probability');
            $table->string('impact');
            $table->string('level');
            $table->foreignId('responsible_id')->constrained('users')->restrictOnDelete();
            $table->string('status');
            $table->timestamps();

            $table->index('level');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
