<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            TagSeeder::class,
            BadgeSeeder::class,
            AdminSeeder::class,
            ModeratorSeeder::class,
        ]);

        // Seed demo thought-leaders and contributors
        $demoUser = User::updateOrCreate(
            ['email' => 'john@example.com'],
            [
                'user_name' => 'johndoe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'city' => 'San Francisco',
                'bio' => 'Engineering Director & Tech Writer. Exploring systems thinking, distributed architecture, and high-leverage mental models.',
                'reputation' => 840,
                'level' => 'expert',
                'password_reset_required' => false,
            ]
        );

        $demoUser2 = User::updateOrCreate(
            ['email' => 'sarah@example.com'],
            [
                'user_name' => 'sarah_dev',
                'email' => 'sarah@example.com',
                'password' => Hash::make('password'),
                'city' => 'London',
                'bio' => 'Product Strategist & 2x Founder. Writing about venture growth, product-market fit, and user psychology.',
                'reputation' => 1450,
                'level' => 'mentor',
                'password_reset_required' => false,
            ]
        );

        $demoUser3 = User::updateOrCreate(
            ['email' => 'alex@example.com'],
            [
                'user_name' => 'alex_cloud',
                'email' => 'alex@example.com',
                'password' => Hash::make('password'),
                'city' => 'Berlin',
                'bio' => 'Systems Researcher & Futurist interested in decentralized computing, cognitive science, and sustainable tech.',
                'reputation' => 520,
                'level' => 'experienced',
                'password_reset_required' => false,
            ]
        );

        // Fetch categories & tags
        $bizCat = Category::where('slug', 'business-startups')->first() ?? Category::first();
        $techCat = Category::where('slug', 'technology-ai')->first() ?? Category::first();
        $prodCat = Category::where('slug', 'productivity-habits')->first() ?? Category::first();

        $startupTag = Tag::where('slug', 'startups')->first();
        $growthTag = Tag::where('slug', 'growth-mindset')->first();
        $decisionTag = Tag::where('slug', 'decision-making')->first();
        $aiTag = Tag::where('slug', 'ai')->first();
        $techTag = Tag::where('slug', 'technology')->first();

        // 1. Business & Scaling Discussion
        if ($bizCat && $demoUser) {
            $q1 = Question::updateOrCreate(
                ['slug' => 'counterintuitive-lessons-scaling-early-stage-startups-to-product-market-fit'],
                [
                    'user_id' => $demoUser->id,
                    'category_id' => $bizCat->id,
                    'title' => 'What are the most counterintuitive lessons founders learn when scaling from 0 to product-market fit?',
                    'slug' => 'counterintuitive-lessons-scaling-early-stage-startups-to-product-market-fit',
                    'description' => "Most conventional startup advice focuses on growth hacks and raising capital early. However, what are the subtle, counterintuitive realities that founders only discover once they are actually in the trenches trying to find true product-market fit?\n\nSpecifically, how should early teams balance doing things that don't scale versus building automated systems?",
                    'view_count' => 620,
                    'vote_score' => 48,
                    'answer_count' => 2,
                    'bookmark_count' => 34,
                    'is_answered' => true,
                    'is_featured' => true,
                    'ai_summary' => "Focus relentlessly on 10 passionate users rather than 1,000 lukewarm ones. Early automation is often premature optimization—doing manual onboarding provides deep customer empathy that algorithms cannot replicate.",
                    'ai_summary_at' => now(),
                ]
            );

            if ($startupTag && $growthTag) {
                $q1->tags()->sync([$startupTag->id, $growthTag->id]);
            }

            $a1 = Answer::updateOrCreate(
                ['question_id' => $q1->id, 'user_id' => $demoUser2->id],
                [
                    'question_id' => $q1->id,
                    'user_id' => $demoUser2->id,
                    'answer' => "Having scaled two B2B companies, here are three counterintuitive lessons:\n\n1. **Retention always precedes growth:** If your day-30 retention curve does not flatten, acquiring more users is simply pouring water into a leaky bucket.\n\n2. **Manual work is your secret weapon:** Paul Graham's advice to *'do things that don't scale'* is timeless. Onboarding the first 50 customers over 1-on-1 Zoom calls uncovered product flaws we would have never spotted in Google Analytics.\n\n3. **Charge earlier than you feel comfortable:** Willingness to pay is the only honest validation metric in business. Free users give compliments; paying users give real feedback.",
                    'vote_score' => 42,
                    'is_accepted' => true,
                ]
            );

            $q1->update(['accepted_answer_id' => $a1->id]);

            Answer::updateOrCreate(
                ['question_id' => $q1->id, 'user_id' => $demoUser3->id],
                [
                    'question_id' => $q1->id,
                    'user_id' => $demoUser3->id,
                    'answer' => "Another key element is velocity of experimentation. The winner isn't the team with the smartest initial idea, but the team that can complete the most build-measure-learn loops per month.",
                    'vote_score' => 12,
                    'is_accepted' => false,
                ]
            );
        }

        // 2. Mental Models & Decision Making
        if ($prodCat && $demoUser3) {
            $q2 = Question::updateOrCreate(
                ['slug' => 'which-mental-models-provide-the-highest-return-on-investment-in-decision-making'],
                [
                    'user_id' => $demoUser3->id,
                    'category_id' => $prodCat->id,
                    'title' => 'Which mental models provide the highest return on investment for navigating complex life and career decisions?',
                    'slug' => 'which-mental-models-provide-the-highest-return-on-investment-in-decision-making',
                    'description' => "With so much cognitive noise in the modern world, clear thinking is a rare competitive advantage. What are the 2-3 foundational mental models you repeatedly rely on to evaluate risk, avoid cognitive traps, and make high-stakes choices under uncertainty?",
                    'view_count' => 780,
                    'vote_score' => 56,
                    'answer_count' => 1,
                    'bookmark_count' => 45,
                    'is_answered' => true,
                    'is_featured' => true,
                    'ai_summary' => "The highest-leverage decision frameworks are Inversion (solving problems backwards), Second-Order Thinking (evaluating consequences of consequences), and the Regret Minimization Framework for long-term clarity.",
                    'ai_summary_at' => now(),
                ]
            );

            if ($decisionTag && $growthTag) {
                $q2->tags()->sync([$decisionTag->id, $growthTag->id]);
            }

            $a2 = Answer::updateOrCreate(
                ['question_id' => $q2->id, 'user_id' => $demoUser->id],
                [
                    'question_id' => $q2->id,
                    'user_id' => $demoUser->id,
                    'answer' => "Here are the three foundational mental frameworks that generate consistent high-leverage outcomes:\n\n### 1. Inversion (Carl Jacobi)\nInstead of asking *'How do I succeed?'*, ask *'What would cause total failure?'*, and methodically eliminate those risks. Avoiding stupidity is much easier than seeking brilliance.\n\n### 2. Second-Order Thinking (Howard Marks)\nFirst-order thinking asks: *'What is the immediate outcome?'*\nSecond-order thinking asks: *'And then what happens after that?'*\nMost terrible decisions feel great in the first order (eating junk food, delaying hard conversations), while high-leverage decisions feel uncomfortable initially.\n\n### 3. Regret Minimization\nProject yourself to age 80 looking back on your life. Which path leaves you with fewer lingering 'what-ifs'? It cuts through temporary fear and social pressure instantly.",
                    'vote_score' => 51,
                    'is_accepted' => true,
                ]
            );

            $q2->update(['accepted_answer_id' => $a2->id]);
        }

        // 3. Technology & Emerging Trends
        if ($techCat && $demoUser2) {
            $q3 = Question::updateOrCreate(
                ['slug' => 'how-will-local-and-edge-intelligence-transform-consumer-privacy-in-the-next-decade'],
                [
                    'user_id' => $demoUser2->id,
                    'category_id' => $techCat->id,
                    'title' => 'How will edge computing and local machine intelligence reshape data privacy over the next decade?',
                    'slug' => 'how-will-local-and-edge-intelligence-transform-consumer-privacy-in-the-next-decade',
                    'description' => "As on-device neural chips become standard on smartphones and laptops, what are the broader societal and economic implications of shifting AI workloads from centralized server farms to private local hardware?",
                    'view_count' => 410,
                    'vote_score' => 29,
                    'answer_count' => 1,
                    'bookmark_count' => 18,
                    'is_answered' => true,
                    'is_featured' => false,
                    'ai_summary' => "On-device intelligence dramatically reduces bandwidth costs, guarantees complete user data confidentiality, and enables zero-latency offline interactions across medical, financial, and personal software.",
                    'ai_summary_at' => now(),
                ]
            );

            if ($aiTag && $techTag) {
                $q3->tags()->sync([$aiTag->id, $techTag->id]);
            }

            Answer::updateOrCreate(
                ['question_id' => $q3->id, 'user_id' => $demoUser3->id],
                [
                    'question_id' => $q3->id,
                    'user_id' => $demoUser3->id,
                    'answer' => "The fundamental paradigm shift is that **the model moves to the data, instead of the data moving to the model**.\n\nThis provides:\n- **Zero Exposure Risk:** Sensitive personal notes or biometric health data never traverse public networks.\n- **Offline Reliability:** Critical systems continue functioning without internet dependencies.\n- **Democratized Access:** Developers can ship smart applications with zero recurring inference API bills.",
                    'vote_score' => 27,
                    'is_accepted' => true,
                ]
            );
        }

        // Sync all answer_counts and user reputation to match actual data
        \App\Models\Question::all()->each(function ($q) {
            $q->update(['answer_count' => $q->answers()->count()]);
        });

        \App\Models\Tag::all()->each(function ($tag) {
            $tag->update(['usage_count' => $tag->questions()->count()]);
        });
    }
}
