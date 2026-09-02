<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Technology & AI',
                'slug'        => 'technology-ai',
                'description' => 'Discussions on emerging technology, software, machine intelligence, and digital trends.',
            ],
            [
                'name'        => 'Business & Startups',
                'slug'        => 'business-startups',
                'description' => 'Entrepreneurship, product building, funding strategies, and market economics.',
            ],
            [
                'name'        => 'Science & Innovation',
                'slug'        => 'science-innovation',
                'description' => 'Scientific discoveries, physics, space exploration, psychology, and modern research.',
            ],
            [
                'name'        => 'Design & Creative Arts',
                'slug'        => 'design-creative',
                'description' => 'UI/UX architecture, visual branding, creative writing, typography, and aesthetics.',
            ],
            [
                'name'        => 'Career & Leadership',
                'slug'        => 'career-leadership',
                'description' => 'Career progression, leadership insights, resume guidance, and professional growth.',
            ],
            [
                'name'        => 'Philosophy & Society',
                'slug'        => 'philosophy-society',
                'description' => 'Thought-provoking perspectives, ethics, culture, history, and human behavior.',
            ],
            [
                'name'        => 'Software & Engineering',
                'slug'        => 'software-engineering',
                'description' => 'Software architecture, algorithms, coding practices, and system design.',
            ],
            [
                'name'        => 'Productivity & Habits',
                'slug'        => 'productivity-habits',
                'description' => 'Time management, effective workflows, mental models, and personal productivity.',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
