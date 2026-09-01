<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'technology', 'startups', 'leadership', 'career', 'productivity',
            'psychology', 'ai', 'philosophy', 'design', 'architecture',
            'remote-work', 'innovation', 'decision-making', 'business-strategy',
            'software', 'web-development', 'data-science', 'learning', 'culture',
            'creative-writing', 'growth-mindset', 'economics', 'communication'
        ];

        foreach ($tags as $tagName) {
            Tag::updateOrCreate(
                ['slug' => Str::slug($tagName)],
                [
                    'name' => $tagName,
                    'slug' => Str::slug($tagName),
                    'description' => "Insightful discussions and perspectives tagged with #{$tagName}.",
                    'usage_count' => rand(5, 38),
                ]
            );
        }
    }
}
