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
        if (!Schema::hasColumn('tb_behavior_reports', 'reports_points_deducted')) {
            Schema::table('tb_behavior_reports', function (Blueprint $table) {
                $table->integer('reports_points_deducted')->default(0)->after('violation_id');
            });

            // Fill snapshot from current violation points
            try {
                DB::statement(
                    'UPDATE tb_behavior_reports br 
                     JOIN tb_violations v ON br.violation_id = v.violations_id 
                     SET br.reports_points_deducted = ABS(IFNULL(v.violations_points_deducted, 0))'
                );
            } catch (\Throwable $e) {
                // Fallback: set to 0 if join update is not supported
                DB::table('tb_behavior_reports')->whereNull('reports_points_deducted')->update(['reports_points_deducted' => 0]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tb_behavior_reports', 'reports_points_deducted')) {
            Schema::table('tb_behavior_reports', function (Blueprint $table) {
                $table->dropColumn('reports_points_deducted');
            });
        }
    }
};
