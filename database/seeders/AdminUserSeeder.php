<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'koumoto41@gmail.com')],
            [
                'name' => 'koumoto41',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'koumoto41')),
                'email_verified_at' => now(),
            ]
        );
    }
}
