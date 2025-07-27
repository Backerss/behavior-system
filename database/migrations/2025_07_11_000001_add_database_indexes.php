<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to tb_users table
        Schema::table('tb_users', function (Blueprint $table) {
            $table->index('users_email', 'idx_users_email');
            $table->index('users_role', 'idx_users_role');
            $table->index(['users_role', 'users_status'], 'idx_users_role_status');
        });

        // Add indexes to tb_students table
        Schema::table('tb_students', function (Blueprint $table) {
            $table->index('user_id', 'idx_students_user_id');
            $table->index('class_id', 'idx_students_class_id');
            $table->index('students_student_code', 'idx_students_code');
            $table->index('students_status', 'idx_students_status');
            $table->index('students_current_score', 'idx_students_score');
            $table->index(['class_id', 'students_current_score'], 'idx_students_class_score');
            $table->index(['students_status', 'students_current_score'], 'idx_students_status_score');
        });

        // Add indexes to tb_teachers table
        Schema::table('tb_teachers', function (Blueprint $table) {
            $table->index('users_id', 'idx_teachers_user_id');
            $table->index('teachers_employee_code', 'idx_teachers_employee_code');
            $table->index('teachers_department', 'idx_teachers_department');
            $table->index('teachers_position', 'idx_teachers_position');
            $table->index('teachers_is_homeroom_teacher', 'idx_teachers_homeroom');
            $table->index('assigned_class_id', 'idx_teachers_assigned_class');
        });

        // Add indexes to tb_classes table
        Schema::table('tb_classes', function (Blueprint $table) {
            $table->index('teachers_id', 'idx_classes_teacher_id');
            $table->index(['classes_level', 'classes_room_number'], 'idx_classes_level_room');
            $table->index(['classes_academic_year', 'classes_level'], 'idx_classes_year_level');
        });

        // Add indexes to tb_behavior_reports table
        Schema::table('tb_behavior_reports', function (Blueprint $table) {
            $table->index('student_id', 'idx_reports_student_id');
            $table->index('teacher_id', 'idx_reports_teacher_id');
            $table->index('violation_id', 'idx_reports_violation_id');
            $table->index('reports_report_date', 'idx_reports_date');
            $table->index(['student_id', 'reports_report_date'], 'idx_reports_student_date');
            $table->index(['teacher_id', 'reports_report_date'], 'idx_reports_teacher_date');
            $table->index(['violation_id', 'reports_report_date'], 'idx_reports_violation_date');
            $table->index(['reports_report_date', 'student_id'], 'idx_reports_date_student');
        });

        // Add indexes to tb_violations table
        Schema::table('tb_violations', function (Blueprint $table) {
            $table->index('violations_category', 'idx_violations_category');
            $table->index('violations_points_deducted', 'idx_violations_points');
            $table->index(['violations_category', 'violations_points_deducted'], 'idx_violations_category_points');
        });

        // Add indexes to tb_guardians table
        Schema::table('tb_guardians', function (Blueprint $table) {
            $table->index('user_id', 'idx_guardians_user_id');
            $table->index('guardians_phone', 'idx_guardians_phone');
            $table->index('guardians_email', 'idx_guardians_email');
            $table->index('guardians_preferred_contact_method', 'idx_guardians_contact_method');
        });

        // Add indexes to tb_guardian_student table
        Schema::table('tb_guardian_student', function (Blueprint $table) {
            $table->index('guardian_id', 'idx_guardian_student_guardian');
            $table->index('student_id', 'idx_guardian_student_student');
            $table->index(['guardian_id', 'student_id'], 'idx_guardian_student_both');
        });

        // Add indexes to tb_notifications table
        Schema::table('tb_notifications', function (Blueprint $table) {
            $table->index('user_id', 'idx_notifications_user_id');
            $table->index('type', 'idx_notifications_type');
            $table->index('read_at', 'idx_notifications_read_at');
            $table->index('created_at', 'idx_notifications_created_at');
            $table->index(['user_id', 'read_at'], 'idx_notifications_user_read');
            $table->index(['user_id', 'created_at'], 'idx_notifications_user_created');
            $table->index(['user_id', 'type', 'read_at'], 'idx_notifications_user_type_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from tb_notifications table
        Schema::table('tb_notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_id');
            $table->dropIndex('idx_notifications_type');
            $table->dropIndex('idx_notifications_read_at');
            $table->dropIndex('idx_notifications_created_at');
            $table->dropIndex('idx_notifications_user_read');
            $table->dropIndex('idx_notifications_user_created');
            $table->dropIndex('idx_notifications_user_type_read');
        });

        // Drop indexes from tb_guardian_student table
        Schema::table('tb_guardian_student', function (Blueprint $table) {
            $table->dropIndex('idx_guardian_student_guardian');
            $table->dropIndex('idx_guardian_student_student');
            $table->dropIndex('idx_guardian_student_both');
        });

        // Drop indexes from tb_guardians table
        Schema::table('tb_guardians', function (Blueprint $table) {
            $table->dropIndex('idx_guardians_user_id');
            $table->dropIndex('idx_guardians_phone');
            $table->dropIndex('idx_guardians_email');
            $table->dropIndex('idx_guardians_contact_method');
        });

        // Drop indexes from tb_violations table
        Schema::table('tb_violations', function (Blueprint $table) {
            $table->dropIndex('idx_violations_category');
            $table->dropIndex('idx_violations_points');
            $table->dropIndex('idx_violations_category_points');
        });

        // Drop indexes from tb_behavior_reports table
        Schema::table('tb_behavior_reports', function (Blueprint $table) {
            $table->dropIndex('idx_reports_student_id');
            $table->dropIndex('idx_reports_teacher_id');
            $table->dropIndex('idx_reports_violation_id');
            $table->dropIndex('idx_reports_date');
            $table->dropIndex('idx_reports_student_date');
            $table->dropIndex('idx_reports_teacher_date');
            $table->dropIndex('idx_reports_violation_date');
            $table->dropIndex('idx_reports_date_student');
        });

        // Drop indexes from tb_classes table
        Schema::table('tb_classes', function (Blueprint $table) {
            $table->dropIndex('idx_classes_teacher_id');
            $table->dropIndex('idx_classes_academic_year');
            $table->dropIndex('idx_classes_level_room');
            $table->dropIndex('idx_classes_year_level');
        });

        // Drop indexes from tb_teachers table
        Schema::table('tb_teachers', function (Blueprint $table) {
            $table->dropIndex('idx_teachers_user_id');
            $table->dropIndex('idx_teachers_employee_code');
            $table->dropIndex('idx_teachers_department');
            $table->dropIndex('idx_teachers_position');
            $table->dropIndex('idx_teachers_homeroom');
            $table->dropIndex('idx_teachers_assigned_class');
        });

        // Drop indexes from tb_students table
        Schema::table('tb_students', function (Blueprint $table) {
            $table->dropIndex('idx_students_user_id');
            $table->dropIndex('idx_students_class_id');
            $table->dropIndex('idx_students_code');
            $table->dropIndex('idx_students_status');
            $table->dropIndex('idx_students_score');
            $table->dropIndex('idx_students_class_score');
            $table->dropIndex('idx_students_status_score');
        });

        // Drop indexes from tb_users table
        Schema::table('tb_users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_role_status');
        });
    }
};
