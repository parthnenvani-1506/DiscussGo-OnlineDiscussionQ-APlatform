<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use App\Models\Question;
use App\Models\Answer;
use App\Policies\QuestionPolicy;
use App\Policies\AnswerPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Register Eloquent Policies
        Gate::policy(Question::class, QuestionPolicy::class);
        Gate::policy(Answer::class, AnswerPolicy::class);

        // Morph map for polymorphic relationships
        Relation::morphMap([
            'question' => Question::class,
            'answer'   => Answer::class,
        ]);
    }
}
