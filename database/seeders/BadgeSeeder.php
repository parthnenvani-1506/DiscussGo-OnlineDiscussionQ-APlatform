<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => 'First Question',
                'description' => 'Asked your first technical question on DiscussHub.',
                'icon' => 'bi bi-patch-question',
                'tier' => 'bronze',
                'criteria' => 'first_question',
            ],
            [
                'name' => 'First Answer',
                'description' => 'Contributed your first solution to help a peer.',
                'icon' => 'bi bi-chat-left-dots',
                'tier' => 'bronze',
                'criteria' => 'first_answer',
            ],
            [
                'name' => 'Helpful Contributor',
                'description' => 'Received 10 total upvotes across your posted solutions.',
                'icon' => 'bi bi-hand-thumbs-up',
                'tier' => 'silver',
                'criteria' => 'helpful_10_upvotes',
            ],
            [
                'name' => 'Popular Discussion',
                'description' => 'Posted a discussion that reached 100+ views.',
                'icon' => 'bi bi-eye',
                'tier' => 'silver',
                'criteria' => 'popular_100_views',
            ],
            [
                'name' => 'Accepted Solution',
                'description' => 'Had an answer marked as the accepted solution by the question author.',
                'icon' => 'bi bi-check-circle',
                'tier' => 'gold',
                'criteria' => 'first_accepted_answer',
            ],
            [
                'name' => 'Active Member',
                'description' => 'Active developer on the platform.',
                'icon' => 'bi bi-calendar-check',
                'tier' => 'silver',
                'criteria' => 'veteran_30_days',
            ],
            [
                'name' => 'Knowledge Leader',
                'description' => 'Reached 1,000+ reputation points through community peer reviews.',
                'icon' => 'bi bi-stars',
                'tier' => 'gold',
                'criteria' => 'reputation_1000',
            ],
            [
                'name' => 'Top Contributor',
                'description' => 'Answered 50 or more community discussions.',
                'icon' => 'bi bi-trophy',
                'tier' => 'gold',
                'criteria' => 'top_50_answers',
            ],
            [
                'name' => 'Well Followed',
                'description' => 'Gained 10 or more followers in the community.',
                'icon' => 'bi bi-people-fill',
                'tier' => 'silver',
                'criteria' => 'well_followed',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['criteria' => $badge['criteria']], $badge);
        }
    }
}
