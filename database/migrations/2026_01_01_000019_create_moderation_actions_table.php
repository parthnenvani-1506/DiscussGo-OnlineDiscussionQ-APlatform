<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderator_id')->constrained('users')->onDelete('cascade');
            $table->enum('action_type', [
                'remove_question', 'remove_answer', 'warn_user',
                'suspend_user', 'dismiss_flag', 'dismiss_report', 'escalate'
            ]);
            $table->string('target_type', 50)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('reason');
            $table->unsignedBigInteger('report_id')->nullable();
            $table->boolean('ai_flag_source')->default(false);
            $table->timestamps();
            $table->index('moderator_id');
            $table->index('created_at');
        });

        // Add followers_count and following_count to users
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'moderator'])->default('user')->after('level');
            $table->unsignedInteger('followers_count')->default(0)->after('role');
            $table->unsignedInteger('following_count')->default(0)->after('followers_count');
            $table->unsignedInteger('warning_count')->default(0)->after('following_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_actions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'followers_count', 'following_count', 'warning_count']);
        });
    }
};
