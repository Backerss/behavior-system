<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tb_behavior_reports', function (Blueprint $table) {
            // เพิ่มคอลัมน์ผู้บันทึก (nullable) หรือปรับให้ nullable หากมีอยู่แล้ว
            if (!Schema::hasColumn('tb_behavior_reports', 'recorded_by')) {
                $table->bigInteger('recorded_by')->nullable()->after('teacher_id');
            } else {
                $table->bigInteger('recorded_by')->nullable()->change();
            }
        });

        // ปรับแก้ foreign keys และ nullable
        Schema::table('tb_behavior_reports', function (Blueprint $table) {
            // ทำให้ teacher_id nullable และผูก FK ใหม่
            try { $table->dropForeign(['teacher_id']); } catch (\Throwable $e) {}
            $table->bigInteger('teacher_id')->nullable()->change();
            $table->foreign('teacher_id')->references('teachers_id')->on('tb_teachers')->onDelete('cascade');

            // ตั้งค่า 0 ให้เป็น null เพื่อให้ผ่าน FK
            try { \Illuminate\Support\Facades\DB::table('tb_behavior_reports')->where('recorded_by', 0)->update(['recorded_by' => null]); } catch (\Throwable $e) {}

            // ผูก FK recorded_by -> tb_users (ยอมรับค่า null)
            $table->foreign('recorded_by')->references('users_id')->on('tb_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tb_behavior_reports', function (Blueprint $table) {
            // ย้อนกลับ FK recorded_by และลบคอลัมน์
            try { $table->dropForeign(['recorded_by']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('tb_behavior_reports', 'recorded_by')) {
                $table->dropColumn('recorded_by');
            }

            // กลับค่า teacher_id เป็น not null และ FK เดิม
            try { $table->dropForeign(['teacher_id']); } catch (\Throwable $e) {}
            $table->bigInteger('teacher_id')->nullable(false)->change();
            $table->foreign('teacher_id')->references('teachers_id')->on('tb_teachers')->onDelete('cascade');
        });
    }
};
