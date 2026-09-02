<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReputationSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Earn
            ['key' => 'ask_question',        'label' => 'Ask a question',                        'group' => 'earn', 'points' =>  5,   'description' => 'Awarded when a user publishes a new question.'],
            ['key' => 'post_answer',         'label' => 'Post an answer',                         'group' => 'earn', 'points' =>  10,  'description' => 'Awarded when a user submits an answer.'],
            ['key' => 'answer_accepted',     'label' => 'Answer accepted as solution',            'group' => 'earn', 'points' =>  50,  'description' => 'Awarded when a question owner marks an answer as accepted.'],
            ['key' => 'like_answer',         'label' => 'Someone likes your answer',              'group' => 'earn', 'points' =>  5,   'description' => 'Awarded to the answer author when someone likes it.'],
            ['key' => 'like_question',       'label' => 'Someone likes your question',            'group' => 'earn', 'points' =>  3,   'description' => 'Awarded to the question author when someone likes it.'],
            // Lose
            ['key' => 'delete_question',     'label' => 'Delete your own question',               'group' => 'lose', 'points' => -5,   'description' => 'Deducted when a user deletes one of their questions.'],
            ['key' => 'delete_answer',       'label' => 'Delete your own answer',                 'group' => 'lose', 'points' => -10,  'description' => 'Deducted when a user deletes one of their answers.'],
            ['key' => 'answer_unaccepted',   'label' => 'Accepted answer is un-accepted',         'group' => 'lose', 'points' => -50,  'description' => 'Deducted when a question owner unmarks an accepted answer.'],
            ['key' => 'unlike_answer',       'label' => 'Someone removes their like on your answer',   'group' => 'lose', 'points' => -5, 'description' => 'Deducted when a like is removed from an answer.'],
            ['key' => 'unlike_question',     'label' => 'Someone removes their like on your question', 'group' => 'lose', 'points' => -3, 'description' => 'Deducted when a like is removed from a question.'],
        ];

        foreach ($settings as $s) {
            DB::table('reputation_settings')->updateOrInsert(
                ['key' => $s['key']],
                array_merge($s, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
