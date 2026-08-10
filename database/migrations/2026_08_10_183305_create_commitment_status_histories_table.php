<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commitment_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commitment_id')->constrained('commitments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['commitment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commitment_status_histories');
    }
};
