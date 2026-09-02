<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            // What to measure
            $table->string('condition_type', 60)->nullable()->after('criteria')
                ->comment('min_questions|min_answers|min_accepted|min_upvotes_on_answers|min_reputation|min_views_on_question|min_followers');
            // The threshold value
            $table->unsignedInteger('condition_value')->default(1)->after('condition_type');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['condition_type', 'condition_value']);
        });
    }
};
