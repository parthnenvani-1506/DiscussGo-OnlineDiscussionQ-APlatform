<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['color', 'icon', 'image']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('color', 20)->default('#2563eb')->after('description');
            $table->string('icon', 50)->default('bi bi-folder')->after('color');
            $table->string('image')->nullable()->after('icon');
        });
    }
};
