<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reputation_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();          // machine key, e.g. "ask_question"
            $table->string('label', 120);                  // human label shown in admin
            $table->string('group', 40)->default('earn'); // 'earn' | 'lose'
            $table->integer('points');                     // positive = earn, negative = lose
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reputation_settings');
    }
};
