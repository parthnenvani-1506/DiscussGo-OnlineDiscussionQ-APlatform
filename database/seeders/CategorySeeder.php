<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technology & AI',
                'slug' => 'technology-ai',
                'description' => 'Discussions on emerging technology, software, machine intelligence, and digital trends.',
                'color' => '#2563eb',
                'icon' => 'bi bi-laptop',
            ],
            [
                'name' => 'Business & Startups',
                'slug' => 'business-startups',
                'description' => 'Entrepreneurship, product building, funding strategies, and market economics.',
                'color' => '#0ea5e9',
                'icon' => 'bi bi-briefcase',
            ],
            [
                'name' => 'Science & Innovation',
                'slug' => 'science-innovation',
                'description' => 'Scientific discoveries, physics, space exploration, psychology, and modern research.',
                'color' => '#8b5cf6',
                'icon' => 'bi bi-lightbulb',
            ],
            [
                'name' => 'Design & Creative Arts',
                'slug' => 'design-creative',
                'description' => 'UI/UX architecture, visual branding, creative writing, typography, and aesthetics.',
                'color' => '#ec4899',
                'icon' => 'bi bi-palette',
            ],
            [
                'name' => 'Career & Leadership',
                'slug' => 'career-leadership',
                'description' => 'Career progression, leadership insights, resume guidance, and professional growth.',
                'color' => '#f59e0b',
                'icon' => 'bi bi-mortarboard',
            ],
            [
                'name' => 'Philosophy & Society',
                'slug' => 'philosophy-society',
                'description' => 'Thought-provoking perspectives, ethics, culture, history, and human behavior.',
                'color' => '#10b981',
                'icon' => 'bi bi-globe2',
            ],
            [
                'name' => 'Software & Engineering',
                'slug' => 'software-engineering',
                'description' => 'Software architecture, algorithms, coding practices, and system design.',
                'color' => '#6366f1',
                'icon' => 'bi bi-code-slash',
            ],
            [
                'name' => 'Productivity & Habits',
                'slug' => 'productivity-habits',
                'description' => 'Time management, effective workflows, mental models, and personal productivity.',
                'color' => '#f43f5e',
                'icon' => 'bi bi-lightning-charge',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
