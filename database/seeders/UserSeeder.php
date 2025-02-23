<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Reviewee A',
            'email' => 'revieweeA@example.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('id', '1')->first()->id,
        ]);

        User::create([
            'name' => 'Reviewer A',
            'email' => 'reviewerA@example.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('id', '2')->first()->id,
        ]);

        User::create([
            'name' => 'Reviewee B',
            'email' => 'revieweeB@example.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('id', '3')->first()->id,
        ]);

        User::create([
            'name' => 'Reviewer B',
            'email' => 'reviewerB@example.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('id', '4')->first()->id,
        ]);

        // User::factory(10)->create();
    }
}
