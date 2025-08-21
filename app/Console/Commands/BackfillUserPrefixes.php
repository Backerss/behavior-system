<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillUserPrefixes extends Command
{
    protected $signature = 'users:backfill-prefix {--dry-run} {--only-null}';
    protected $description = 'เติมคำนำหน้าชื่อ (users_name_prefix) ตามอายุ/เพศ: <15 → เด็กชาย/เด็กหญิง, >=15 → นาย/นางสาว (รองรับปี พ.ศ.)';

    public function handle()
    {
        $dry = (bool)$this->option('dry-run');
        $onlyNull = (bool)$this->option('only-null');

        $this->info('เริ่มเติมคำนำหน้าชื่อจากวันเกิดและเพศ' . ($dry ? ' (โหมดทดสอบ)' : ''));

        $query = DB::table('tb_users')->select('users_id','users_name_prefix','users_role','users_birthdate');
        if ($onlyNull) { $query->whereNull('users_name_prefix'); }
        $users = $query->orderBy('users_id')->get();

        $total = $users->count();
        if ($total === 0) { $this->info('ไม่มีรายการให้ปรับปรุง'); return self::SUCCESS; }

        $updated = 0; $skipped = 0; $errors = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($users as $u) {
            try {
                $gender = null;
                if ($u->users_role === 'student') {
                    // ใช้เพศจากตารางนักเรียนถ้ามี
                    $stu = DB::table('tb_students')->where('user_id', $u->users_id)->select('students_gender')->first();
                    $gender = $stu ? $stu->students_gender : null;
                }

                $prefix = $this->choosePrefixByAgeGender($gender, $u->users_birthdate);
                if (!$prefix) { $prefix = $gender === 'female' ? 'นางสาว' : 'นาย'; }

                if ($onlyNull && $u->users_name_prefix !== null) { $skipped++; $bar->advance(); continue; }
                if ($u->users_name_prefix === $prefix) { $skipped++; $bar->advance(); continue; }

                if (!$dry) {
                    DB::table('tb_users')->where('users_id',$u->users_id)->update([
                        'users_name_prefix' => $prefix,
                        'users_updated_at' => now(),
                    ]);
                }
                $updated++;
            } catch (\Throwable $e) { $errors++; }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("ทั้งหมด: {$total}, อัปเดต: {$updated}, ข้าม: {$skipped}, ผิดพลาด: {$errors}");

        return self::SUCCESS;
    }

    private function choosePrefixByAgeGender($gender = null, $birthYmd = null)
    {
        $age = null; $norm = $birthYmd;
        if ($birthYmd) {
            // รองรับ พ.ศ.
            try {
                $y = (int)substr($birthYmd,0,4);
                if ($y >= 2400) { $y -= 543; }
                $norm = sprintf('%04d-%s', $y, substr($birthYmd,5));
            } catch (\Throwable $e) { $norm = $birthYmd; }
            try { $age = (new \DateTime())->diff(new \DateTime($norm))->y; } catch (\Exception $e) { $age = null; }
        }
        if ($age !== null) {
            if ($age >= 15) { return ($gender === 'female' ? 'นางสาว' : 'นาย'); }
            return ($gender === 'female' ? 'เด็กหญิง' : 'เด็กชาย');
        }
        return $gender === 'female' ? 'นางสาว' : 'นาย';
    }
}
