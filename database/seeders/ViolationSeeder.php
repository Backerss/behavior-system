<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Violation;

class ViolationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $violations = [
            [
                'violations_name' => 'มาสาย',
                'violations_description' => 'เข้าเรียนหลังเวลาที่กำหนด',
                'violations_category' => 'light',
                'violations_points_deducted' => 5
            ],
            [
                'violations_name' => 'ไม่ทำการบ้าน',
                'violations_description' => 'ไม่ส่งงานตามกำหนด',
                'violations_category' => 'light',
                'violations_points_deducted' => 10
            ],
            [
                'violations_name' => 'แต่งกายไม่เรียบร้อย',
                'violations_description' => 'แต่งกายไม่ตามระเบียบของโรงเรียน',
                'violations_category' => 'light',
                'violations_points_deducted' => 15
            ],
            [
                'violations_name' => 'ทะเลาะวิวาท',
                'violations_description' => 'ทะเลาะหรือวิวาทกับเพื่อน',
                'violations_category' => 'medium',
                'violations_points_deducted' => 25
            ],
            [
                'violations_name' => 'สูบบุหรี่',
                'violations_description' => 'สูบบุหรี่ในพื้นที่โรงเรียน',
                'violations_category' => 'severe',
                'violations_points_deducted' => 50
            ]
        ];

        foreach ($violations as $violation) {
            Violation::create($violation);
        }
    }
}
