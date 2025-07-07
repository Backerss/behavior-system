<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GuardianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // เพิ่มข้อมูล users สำหรับผู้ปกครองก่อน
        $guardianUsers = [
            ['users_id' => 39, 'users_name_prefix' => 'นาย', 'users_first_name' => 'สมศักดิ์', 'users_last_name' => 'รักเรียน', 'users_email' => 'somsak.rakrian@gmail.com', 'users_phone_number' => '0891234567', 'users_password' => Hash::make('password123'), 'users_role' => 'guardian', 'users_birthdate' => '1975-05-15'],
            ['users_id' => 40, 'users_name_prefix' => 'นาง', 'users_first_name' => 'วนิดา', 'users_last_name' => 'รักเรียน', 'users_email' => 'wanida.rakrian@gmail.com', 'users_phone_number' => '0891234568', 'users_password' => Hash::make('password123'), 'users_role' => 'guardian', 'users_birthdate' => '1978-08-20'],
            ['users_id' => 41, 'users_name_prefix' => 'นาย', 'users_first_name' => 'ประสิทธิ์', 'users_last_name' => 'คงแก้ว', 'users_email' => 'prasit.kongkaew@gmail.com', 'users_phone_number' => '0891234569', 'users_password' => Hash::make('password123'), 'users_role' => 'guardian', 'users_birthdate' => '1972-12-10'],
            ['users_id' => 42, 'users_name_prefix' => 'นาง', 'users_first_name' => 'มาลี', 'users_last_name' => 'คงแก้ว', 'users_email' => 'malee.kongkaew@gmail.com', 'users_phone_number' => '0891234570', 'users_password' => Hash::make('password123'), 'users_role' => 'guardian', 'users_birthdate' => '1976-03-25'],
            ['users_id' => 43, 'users_name_prefix' => 'นาย', 'users_first_name' => 'รัตนชัย', 'users_last_name' => 'ทองแท้', 'users_email' => 'rattanachai.tongtae@gmail.com', 'users_phone_number' => '0891234571', 'users_password' => Hash::make('password123'), 'users_role' => 'guardian', 'users_birthdate' => '1974-11-05'],
            ['users_id' => 44, 'users_name_prefix' => 'นาง', 'users_first_name' => 'สุนทรา', 'users_last_name' => 'ทองแท้', 'users_email' => 'suntra.tongtae@gmail.com', 'users_phone_number' => '0891234572', 'users_password' => Hash::make('password123'), 'users_role' => 'guardian', 'users_birthdate' => '1977-07-17'],
        ];

        foreach ($guardianUsers as $userData) {
            User::create($userData);
        }

        // เพิ่มข้อมูลผู้ปกครอง
        $guardians = [
            ['guardians_id' => 1, 'user_id' => 39, 'guardians_relationship_to_student' => 'บิดา', 'guardians_phone' => '0891234567', 'guardians_email' => 'somsak.rakrian@gmail.com', 'guardians_line_id' => 'somsak_line', 'guardians_preferred_contact_method' => 'phone'],
            ['guardians_id' => 2, 'user_id' => 40, 'guardians_relationship_to_student' => 'มารดา', 'guardians_phone' => '0891234568', 'guardians_email' => 'wanida.rakrian@gmail.com', 'guardians_line_id' => 'wanida_line', 'guardians_preferred_contact_method' => 'phone'],
            ['guardians_id' => 3, 'user_id' => 41, 'guardians_relationship_to_student' => 'บิดา', 'guardians_phone' => '0891234569', 'guardians_email' => 'prasit.kongkaew@gmail.com', 'guardians_line_id' => 'prasit_line', 'guardians_preferred_contact_method' => 'phone'],
            ['guardians_id' => 4, 'user_id' => 42, 'guardians_relationship_to_student' => 'มารดา', 'guardians_phone' => '0891234570', 'guardians_email' => 'malee.kongkaew@gmail.com', 'guardians_line_id' => 'malee_line', 'guardians_preferred_contact_method' => 'phone'],
            ['guardians_id' => 5, 'user_id' => 43, 'guardians_relationship_to_student' => 'บิดา', 'guardians_phone' => '0891234571', 'guardians_email' => 'rattanachai.tongtae@gmail.com', 'guardians_line_id' => 'rattanachai_line', 'guardians_preferred_contact_method' => 'phone'],
            ['guardians_id' => 6, 'user_id' => 44, 'guardians_relationship_to_student' => 'มารดา', 'guardians_phone' => '0891234572', 'guardians_email' => 'suntra.tongtae@gmail.com', 'guardians_line_id' => 'suntra_line', 'guardians_preferred_contact_method' => 'phone'],
        ];

        foreach ($guardians as $guardian) {
            DB::table('tb_guardians')->insert($guardian);
        }
    }
}
