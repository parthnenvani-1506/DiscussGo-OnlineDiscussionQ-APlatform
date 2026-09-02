<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name'            => 'First Question',
                'description'     => 'Asked your first question on DiscussHub.',
                'icon'            => 'bi bi-patch-question',
                'tier'            => 'bronze',
                'criteria'        => 'first_question',
                'condition_type'  => 'min_questions',
                'condition_value' => 1,
            ],
            [
                'name'            => 'First Answer',
                'description'     => 'Contributed your first solution to help a peer.',
                'icon'            => 'bi bi-chat-left-dots',
                'tier'            => 'bronze',
                'criteria'        => 'first_answer',
                'condition_type'  => 'min_answers',
                'condition_value' => 1,
            ],
            [
                'name'            => 'Helpful Contributor',
                'description'     => 'Received 10 total likes across your posted answers.',
                'icon'            => 'bi bi-hand-thumbs-up',
                'tier'            => 'silver',
                'criteria'        => 'helpful_10_upvotes',
                'condition_type'  => 'min_upvotes_on_answers',
                'condition_value' => 10,
            ],
            [
                'name'            => 'Popular Discussion',
                'description'     => 'Posted a question that reached 100+ views.',
                'icon'            => 'bi bi-eye',
                'tier'            => 'silver',
                'criteria'        => 'popular_100_views',
                'condition_type'  => 'min_views_on_question',
                'condition_value' => 100,
            ],
            [
                'name'            => 'Accepted Solution',
                'description'     => 'Had an answer marked as the accepted solution.',
                'icon'            => 'bi bi-check-circle',
                'tier'            => 'gold',
                'criteria'        => 'first_accepted_answer',
                'condition_type'  => 'min_accepted',
                'condition_value' => 1,
            ],
            [
                'name'            => 'Active Member',
                'description'     => 'Asked 5 or more questions on the platform.',
                'icon'            => 'bi bi-calendar-check',
                'tier'            => 'silver',
                'criteria'        => 'veteran_30_days',
                'condition_type'  => 'min_questions',
                'condition_value' => 5,
            ],
            [
                'name'            => 'Knowledge Leader',
                'description'     => 'Reached 1,000+ reputation points through community contributions.',
                'icon'            => 'bi bi-stars',
                'tier'            => 'gold',
                'criteria'        => 'reputation_1000',
                'condition_type'  => 'min_reputation',
                'condition_value' => 1000,
            ],
            [
                'name'            => 'Top Contributor',
                'description'     => 'Answered 50 or more community discussions.',
                'icon'            => 'bi bi-trophy',
                'tier'            => 'gold',
                'criteria'        => 'top_50_answers',
                'condition_type'  => 'min_answers',
                'condition_value' => 50,
            ],
            [
                'name'            => 'Well Followed',
                'description'     => 'Gained 10 or more followers in the community.',
                'icon'            => 'bi bi-people-fill',
                'tier'            => 'silver',
                'criteria'        => 'well_followed',
                'condition_type'  => 'min_followers',
                'condition_value' => 10,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['criteria' => $badge['criteria']], $badge);
        }
    }
}
