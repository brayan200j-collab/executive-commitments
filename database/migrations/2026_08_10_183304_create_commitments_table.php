<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commitments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('meeting_id')->constrained('meetings')->restrictOnDelete();
            $table->text('description');
            $table->foreignId('responsible_id')->constrained('users')->restrictOnDelete();
            $table->date('due_date');
            $table->string('priority');
            $table->string('status');
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->text('evidence')->nullable();
            $table->timestamps();

            $table->index('due_date');
            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commitments');
    }
};
