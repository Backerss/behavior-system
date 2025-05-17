<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddInitialViolations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $violations = [
            [
                'violations_name' => 'ผิดระเบียบการแต่งกาย',
                'violations_description' => 'นักเรียนแต่งกายไม่ถูกระเบียบตามข้อกำหนดของโรงเรียน',
                'violations_category' => 'medium',
                'violations_points_deducted' => 5
            ],
            [
                'violations_name' => 'มาสาย',
                'violations_description' => 'นักเรียนมาโรงเรียนหลังเวลา 08:00 น.',
                'violations_category' => 'light',
                'violations_points_deducted' => 3
            ],
            [
                'violations_name' => 'ทะเลาะวิวาท',
                'violations_description' => 'นักเรียนก่อเหตุทะเลาะวิวาท ทำร้ายร่างกายผู้อื่น',
                'violations_category' => 'severe',
                'violations_points_deducted' => 20
            ],
            [
                'violations_name' => 'ใช้โทรศัพท์ในเวลาเรียน',
                'violations_description' => 'นักเรียนใช้โทรศัพท์มือถือในชั้นเรียนโดยไม่ได้รับอนุญาต',
                'violations_category' => 'medium',
                'violations_points_deducted' => 5
            ],
            [
                'violations_name' => 'ไม่ส่งการบ้าน',
                'violations_description' => 'นักเรียนไม่ส่งการบ้านตามกำหนด',
                'violations_category' => 'light',
                'violations_points_deducted' => 2
            ],
            [
                'violations_name' => 'ขาดเรียน',
                'violations_description' => 'นักเรียนขาดเรียนโดยไม่มีการลาหรือแจ้งล่วงหน้า',
                'violations_category' => 'medium',
                'violations_points_deducted' => 5
            ],
            [
                'violations_name' => 'ลืมอุปกรณ์',
                'violations_description' => 'นักเรียนมาเรียนโดยไม่นำอุปกรณ์การเรียนมาให้ครบ',
                'violations_category' => 'light',
                'violations_points_deducted' => 2
            ],
            [
                'violations_name' => 'ทำลายทรัพย์สิน',
                'violations_description' => 'นักเรียนทำลายทรัพย์สินของโรงเรียนหรือของผู้อื่น',
                'violations_category' => 'severe',
                'violations_points_deducted' => 15
            ],
            [
                'violations_name' => 'หนีเรียน',
                'violations_description' => 'นักเรียนเข้าโรงเรียนแต่ไม่เข้าเรียนตามตารางเรียน',
                'violations_category' => 'medium',
                'violations_points_deducted' => 10
            ],
            [
                'violations_name' => 'ไม่เคารพครู',
                'violations_description' => 'นักเรียนแสดงพฤติกรรมไม่เคารพครูหรือบุคลากรของโรงเรียน',
                'violations_category' => 'severe',
                'violations_points_deducted' => 15
            ]
        ];

        foreach ($violations as $violation) {
            DB::table('tb_violations')->insert($violation);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tb_violations')
            ->whereIn('violations_name', [
                'ผิดระเบียบการแต่งกาย',
                'มาสาย',
                'ทะเลาะวิวาท',
                'ใช้โทรศัพท์ในเวลาเรียน',
                'ไม่ส่งการบ้าน',
                'ขาดเรียน',
                'ลืมอุปกรณ์',
                'ทำลายทรัพย์สิน',
                'หนีเรียน',
                'ไม่เคารพครู'
            ])
            ->delete();
    }
}