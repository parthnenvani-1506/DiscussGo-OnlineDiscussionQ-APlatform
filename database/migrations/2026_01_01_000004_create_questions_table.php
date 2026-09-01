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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('title', 300);
            $table->string('slug', 255)->unique();
            $table->longText('description');
            $table->unsignedInteger('view_count')->default(0);
            $table->integer('vote_score')->default(0);
            $table->unsignedInteger('answer_count')->default(0);
            $table->unsignedInteger('bookmark_count')->default(0);
            $table->boolean('is_answered')->default(false);
            $table->unsignedBigInteger('accepted_answer_id')->nullable();
            $table->text('ai_summary')->nullable();
            $table->timestamp('ai_summary_at')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('category_id');
            $table->index('vote_score');
            $table->index('created_at');
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
