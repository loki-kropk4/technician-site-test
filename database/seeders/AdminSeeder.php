<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id' => 2,
            'name' => 'admin',
            'email' => 'mindurown@parsley.com',
            'email_verified_at' => now(),
            'password' => Hash::make('jawaj4w4123'),
            'role' => 'admin'
        ]);
    }
}
