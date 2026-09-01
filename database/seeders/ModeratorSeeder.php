<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ModeratorSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'moderator@discusshub.ai'],
            [
                'user_name'               => 'mod_team',
                'email'                   => 'moderator@discusshub.ai',
                'password'                => Hash::make('mod123456'),
                'city'                    => 'Platform',
                'bio'                     => 'Official DiscussHub community moderator. Here to keep discussions healthy and constructive.',
                'role'                    => 'moderator',
                'reputation'              => 500,
                'level'                   => 'experienced',
                'password_reset_required' => false,
            ]
        );
    }
}
