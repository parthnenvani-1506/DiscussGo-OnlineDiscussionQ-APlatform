<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Category;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Vote;
use App\Models\Tag;
use App\Models\ContactMessage;
use App\Models\Admin;

class MigrateV1Command extends Command
{
    protected $signature = 'app:migrate-v1 {--legacy-db=discussgo : The name of the legacy database}';
    protected $description = 'Migrate data from legacy v1 database into DiscussHub v2';

    public function handle(): int
    {
        $legacyDb = $this->option('legacy-db');
        $this->info("Starting migration from legacy database: {$legacyDb}...");

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$legacyDb}'");
            if (!$stmt->fetch()) {
                $this->error("Legacy database '{$legacyDb}' not found.");
                return 1;
            }

            // 1. Migrate Users
            $this->info("Migrating users...");
            $oldUsers = DB::select("SELECT * FROM `{$legacyDb}`.users");
            $userMap = []; // old_id => new_id

            foreach ($oldUsers as $oldUser) {
                $existing = User::where('email', $oldUser->email)->first();
                if (!$existing) {
                    $newUser = User::create([
                        'user_name' => $oldUser->user_name ?? 'User_' . $oldUser->id,
                        'email' => $oldUser->email,
                        'password' => Hash::make('password123'), // Secure default with reset flag
                        'city' => $oldUser->city ?? null,
                        'profile_image' => !empty($oldUser->profile_image) ? $oldUser->profile_image : 'default_profile.png',
                        'reputation' => 20,
                        'level' => 'newcomer',
                        'password_reset_required' => true,
                    ]);
                    $userMap[$oldUser->id] = $newUser->id;
                } else {
                    $userMap[$oldUser->id] = $existing->id;
                }
            }
            $this->info("Migrated " . count($userMap) . " users.");

            // 2. Migrate Categories
            $this->info("Migrating categories...");
            $oldCategories = DB::select("SELECT * FROM `{$legacyDb}`.category");
            $catMap = []; // old_id => new_id

            foreach ($oldCategories as $oldCat) {
                $cat = Category::firstOrCreate(
                    ['name' => $oldCat->name],
                    [
                        'name' => $oldCat->name,
                        'slug' => Str::slug($oldCat->name),
                        'description' => "Discussions in {$oldCat->name}",
                        'color' => '#3b82f6',
                        'icon' => 'fas fa-folder',
                    ]
                );
                $catMap[$oldCat->id] = $cat->id;
            }
            $this->info("Migrated " . count($catMap) . " categories.");

            // 3. Migrate Questions
            $this->info("Migrating questions...");
            $oldQuestions = DB::select("SELECT * FROM `{$legacyDb}`.questions");
            $qMap = []; // old_id => new_id

            foreach ($oldQuestions as $oldQ) {
                $userId = $userMap[$oldQ->user_id] ?? User::first()->id;
                $catId = $catMap[$oldQ->category_id] ?? Category::first()->id;

                $q = Question::create([
                    'user_id' => $userId,
                    'category_id' => $catId,
                    'title' => $oldQ->title,
                    'description' => $oldQ->description,
                    'view_count' => rand(10, 80),
                    'vote_score' => 0,
                    'answer_count' => 0,
                    'created_at' => $oldQ->created_at ?? now(),
                ]);
                $qMap[$oldQ->id] = $q->id;
            }
            $this->info("Migrated " . count($qMap) . " questions.");

            // 4. Migrate Answers
            $this->info("Migrating answers...");
            $oldAnswers = DB::select("SELECT * FROM `{$legacyDb}`.answers");
            $aMap = [];

            foreach ($oldAnswers as $oldA) {
                $qId = $qMap[$oldA->question_id] ?? null;
                $uId = $userMap[$oldA->user_id] ?? User::first()->id;

                if ($qId) {
                    $ans = Answer::create([
                        'question_id' => $qId,
                        'user_id' => $uId,
                        'answer' => $oldA->answer,
                        'vote_score' => 0,
                        'created_at' => $oldA->created_at ?? now(),
                    ]);
                    $aMap[$oldA->id] = $ans->id;
                    Question::where('id', $qId)->increment('answer_count');
                }
            }
            $this->info("Migrated " . count($aMap) . " answers.");

            // 5. Migrate Answer Likes → Votes
            $this->info("Migrating answer likes to votes...");
            try {
                $oldLikes = DB::select("SELECT * FROM `{$legacyDb}`.answer_likes");
                $likeCount = 0;
                foreach ($oldLikes as $like) {
                    $answerId = $aMap[$like->answer_id] ?? null;
                    $userId   = $userMap[$like->user_id] ?? null;
                    if ($answerId && $userId) {
                        \App\Models\Vote::firstOrCreate(
                            ['user_id' => $userId, 'votable_type' => 'answer', 'votable_id' => $answerId],
                            ['value' => 1]
                        );
                        $likeCount++;
                    }
                }
                $this->info("Migrated {$likeCount} answer likes to votes.");
            } catch (\Exception $e) {
                $this->warn("Could not migrate answer_likes (table may not exist): " . $e->getMessage());
            }

            // 6. Migrate Contact Messages
            $this->info("Migrating contact messages...");
            $oldMsgs = DB::select("SELECT * FROM `{$legacyDb}`.contact_messages");
            foreach ($oldMsgs as $msg) {
                ContactMessage::create([
                    'name' => $msg->name ?? 'Guest',
                    'email' => $msg->email ?? 'guest@example.com',
                    'message' => $msg->message ?? '',
                    'created_at' => $msg->created_at ?? now(),
                ]);
            }

            $this->info("✓ Migration from legacy database completed successfully!");
            return 0;

        } catch (\Exception $e) {
            $this->error("Migration error: " . $e->getMessage());
            return 1;
        }
    }
}
