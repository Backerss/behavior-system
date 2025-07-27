<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ปิด Foreign Key Checks ชั่วคราว
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ตรวจสอบและลบฟิลด์ classes_academic_year ถ้ามีอยู่
        if (Schema::hasColumn('tb_classes', 'classes_academic_year')) {
            Schema::table('tb_classes', function (Blueprint $table) {
                $table->dropColumn('classes_academic_year');
            });
        }

        // ล้างข้อมูลเก่าใน tb_classes
        DB::table('tb_classes')->truncate();

        // เปิด Foreign Key Checks กลับ
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // เพิ่มข้อมูล stable สำหรับ ม.1-6 แต่ละชั้นมี 12 ห้อง
        $classes = [];
        for ($level = 1; $level <= 6; $level++) {
            for ($room = 1; $room <= 12; $room++) {
                $classes[] = [
                    'classes_level' => 'ม.' . $level,
                    'classes_room_number' => (string)$room,
                    'teachers_id' => null
                ];
            }
        }

        DB::table('tb_classes')->insert($classes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // เพิ่มฟิลด์ classes_academic_year กลับเข้าไป
        Schema::table('tb_classes', function (Blueprint $table) {
            $table->string('classes_academic_year', 10)->nullable();
        });

        // ล้างข้อมูล stable
        DB::table('tb_classes')->truncate();
    }
};
