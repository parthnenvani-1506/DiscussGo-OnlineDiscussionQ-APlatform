<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DiscussHubFeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user1;
    protected User $user2;
    protected Category $category;
    protected Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::create([
            'user_name' => 'testdeveloper1',
            'email' => 'dev1_' . uniqid() . '@example.com',
            'password' => Hash::make('secret123'),
            'reputation' => 10,
            'level' => 'newcomer',
        ]);

        $this->user2 = User::create([
            'user_name' => 'testdeveloper2',
            'email' => 'dev2_' . uniqid() . '@example.com',
            'password' => Hash::make('secret123'),
            'reputation' => 10,
            'level' => 'newcomer',
        ]);

        $this->category = Category::firstOrCreate(
            ['slug' => 'test-category'],
            ['name' => 'Test Category', 'icon' => 'bi bi-code', 'color' => '#2563eb']
        );

        $this->tag = Tag::firstOrCreate(
            ['slug' => 'laravel'],
            ['name' => 'laravel', 'usage_count' => 1]
        );
    }

    /**
     * Test User Registration and Login.
     */
    public function test_user_registration_and_authentication(): void
    {
        $response = $this->post(route('register.post'), [
            'user_name' => 'newuser_' . rand(100, 999),
            'email' => 'new_' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'city' => 'Mumbai',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    /**
     * Test Question Creation & Reputation Award.
     */
    public function test_question_creation_and_reputation_award(): void
    {
        $this->actingAs($this->user1);

        $initialRep = $this->user1->fresh()->reputation;

        $response = $this->post(route('questions.store'), [
            'title' => 'How to optimize Eloquent queries in Laravel 11 application?',
            'category_id' => $this->category->id,
            'description' => 'I am looking for best practices when dealing with eager loading and query caching in Laravel.',
            'tags' => [$this->tag->id],
        ]);

        $question = Question::where('title', 'How to optimize Eloquent queries in Laravel 11 application?')->first();
        $this->assertNotNull($question);
        $response->assertRedirect(route('questions.show', [$question->id, $question->slug]));

        // Check reputation +5
        $this->assertEquals($initialRep + 5, $this->user1->fresh()->reputation);
    }

    /**
     * Test Answer Posting & Solution Acceptance.
     */
    public function test_answer_posting_and_acceptance(): void
    {
        $question = Question::create([
            'user_id' => $this->user1->id,
            'category_id' => $this->category->id,
            'title' => 'How to use Service Providers in Laravel 11?',
            'slug' => 'how-to-use-service-providers-laravel-11',
            'description' => 'Looking for examples on registering custom singleton services in Laravel 11.',
        ]);

        // User 2 posts an answer
        $this->actingAs($this->user2);
        $initialUser2Rep = $this->user2->fresh()->reputation;

        $ansResponse = $this->post(route('answers.store'), [
            'question_id' => $question->id,
            'answer' => 'You can register your bindings in AppServiceProvider register method using app->singleton.',
        ]);

        $ansResponse->assertRedirect(route('questions.show', [$question->id, $question->slug]));
        $this->assertEquals($initialUser2Rep + 10, $this->user2->fresh()->reputation);

        $answer = Answer::where('question_id', $question->id)->first();
        $this->assertNotNull($answer);

        // User 1 accepts User 2's answer as solution
        $this->actingAs($this->user1);
        $acceptResponse = $this->post(route('answers.accept', $answer));

        $acceptResponse->assertRedirect();
        $this->assertTrue((bool)$answer->fresh()->is_accepted);
        $this->assertTrue((bool)$question->fresh()->is_answered);

        // Check answer author got +50 reputation
        $this->assertEquals($initialUser2Rep + 10 + 50, $this->user2->fresh()->reputation);
    }

    /**
     * Test Voting System & AJAX endpoints.
     */
    public function test_ajax_voting_flow(): void
    {
        $question = Question::create([
            'user_id' => $this->user1->id,
            'category_id' => $this->category->id,
            'title' => 'Understanding Polymorphic relationships in Eloquent',
            'slug' => 'understanding-polymorphic-relationships-eloquent',
            'description' => 'Can someone explain how morphTo and morphMany work with real examples?',
        ]);

        // User 2 upvotes User 1's question
        $this->actingAs($this->user2);
        $initialUser1Rep = $this->user1->fresh()->reputation;

        $voteResponse = $this->postJson(route('vote'), [
            'votable_type' => 'question',
            'votable_id' => $question->id,
            'value' => 1,
        ]);

        $voteResponse->assertOk();
        $voteResponse->assertJson(['success' => true, 'new_score' => 1, 'user_vote' => 1]);
        $this->assertEquals(1, $question->fresh()->vote_score);
        $this->assertEquals($initialUser1Rep + 5, $this->user1->fresh()->reputation);
    }

    /**
     * Test AI Duplicate Detection API.
     */
    public function test_ai_duplicate_detection_api(): void
    {
        Question::create([
            'user_id' => $this->user1->id,
            'category_id' => $this->category->id,
            'title' => 'How to configure MySQL database in Laravel .env file?',
            'slug' => 'how-to-configure-mysql-database-in-laravel-env-file',
            'description' => 'Steps to connect MySQL with DB_DATABASE and DB_PASSWORD variables in application.',
        ]);

        $response = $this->postJson(route('api.ai.check-duplicate'), [
            'title' => 'How to configure MySQL database connection in Laravel?',
            'description' => 'Need help setting up MySQL .env connection in Laravel.',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['is_duplicate', 'max_score', 'similar_questions']);
    }

    /**
     * Test AI Tag Suggestion API.
     */
    public function test_ai_tag_suggestions_api(): void
    {
        $response = $this->postJson(route('api.ai.suggest-tags'), [
            'title' => 'Building REST API with Laravel and PHP authentication middleware',
            'description' => 'How do I protect routes using Laravel middleware and return JSON responses?',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['tags']);
    }

    /**
     * Test Admin Authentication and Dashboard Access.
     */
    public function test_admin_authentication_and_dashboard(): void
    {
        $admin = Admin::firstOrCreate(
            ['email' => 'testadmin@discusshub.com'],
            ['username' => 'testadmin', 'password' => Hash::make('admin123')]
        );

        $loginResponse = $this->post(route('admin.login.post'), [
            'email' => 'testadmin@discusshub.com',
            'password' => 'admin123',
        ]);

        $loginResponse->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session()->has('admin_id'));

        // Visit Admin Dashboard with admin session
        $dashResponse = $this->withSession(['admin_id' => $admin->id, 'admin_name' => $admin->username])
            ->get(route('admin.dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('System Dashboard');
    }
}
