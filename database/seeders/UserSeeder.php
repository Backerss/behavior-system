<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Admin user only (for system administration)
            ['users_id' => 1, 'users_name_prefix' => 'นาย', 'users_first_name' => 'ผู้ดูแลระบบ', 'users_last_name' => 'Admin', 'users_email' => 'admin@school.ac.th', 'users_phone_number' => '0800000000', 'users_password' => '$2y$12$Yq98CXdMRT3w20RJM2vyYuyhS918XgHt2afpZKqQqrDYXJ5V447w.', 'users_role' => 'admin', 'users_birthdate' => '1990-01-01'],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
