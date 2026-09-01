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
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->string('feature', 100); // 'summarization', 'duplicate_check', 'tag_suggestion', 'moderation'
            $table->unsignedInteger('input_length')->nullable();
            $table->float('response_time')->nullable(); // in seconds
            $table->boolean('success')->default(true);
            $table->unsignedBigInteger('question_id')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->index('feature');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
