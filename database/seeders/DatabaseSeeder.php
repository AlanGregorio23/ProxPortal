<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@proxportal.com',
            'password' => Hash::make('admin123'),
            'type' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('users')->insert([
            'name' => 'Test User',
            'email' => 'user@proxportal.com',
            'password' => Hash::make('user123'),
            'type' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
