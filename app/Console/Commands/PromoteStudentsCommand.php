<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\GoogleSheetsImportService;

class PromoteStudentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:promote {--year=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote students to next grade level based on student code and update class assignments from Google Sheets';

    protected $googleSheetsService;

    public function __construct(GoogleSheetsImportService $googleSheetsService)
    {
        parent::__construct();
        $this->googleSheetsService = $googleSheetsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentYear = $this->option('year') ?: date('Y') + 543; // ใช้ปี พ.ศ.
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info("DRY RUN MODE - ไม่มีการเปลี่ยนแปลงข้อมูลจริง");
        }

        $this->info("เริ่มกระบวนการเลื่อนชั้นปีสำหรับปีการศึกษา: {$currentYear}");

        // ดึงข้อมูลนักเรียนทั้งหมด
        $students = DB::table('tb_students')->get();

        if ($students->isEmpty()) {
            $this->warn('ไม่พบนักเรียนในระบบ');
            return 0;
        }

        $updated = 0;
        $errors = 0;

        foreach ($students as $student) {
            try {
                // คำนวณชั้นปีจากรหัสนักเรียน
                $studentCode = $student->students_student_code;
                if (strlen($studentCode) < 2) {
                    $this->error("รหัสนักเรียน {$studentCode} ไม่ถูกต้อง (ต้องมีอย่างน้อย 2 ตัวอักษร)");
                    $errors++;
                    continue;
                }

                $studentYearCode = (int)substr($studentCode, 0, 2);
                $studentEnrollmentYear = 2500 + $studentYearCode; // เปลี่ยนจาก 2000 เป็น 2500 เพื่อรองรับปี พ.ศ.
                $currentGrade = $currentYear - $studentEnrollmentYear + 1;

                // ตรวจสอบนักเรียนที่จบการศึกษา (เกิน ม.6 แล้ว หรือ ม.6 ที่เลื่อนชั้นแล้ว)
                if ($currentGrade > 6) {
                    // นักเรียนจบการศึกษาแล้ว
                    if (!$isDryRun) {
                        DB::table('tb_students')
                            ->where('students_id', $student->students_id)
                            ->update([
                                'students_status' => 'graduated',
                                'updated_at' => now()
                            ]);
                    }
                    
                    $this->info("นักเรียน {$studentCode}: จบการศึกษาแล้ว (สถานะ: graduated)");
                    $updated++;
                    continue;
                }

                // ตรวจสอบนักเรียน ม.6 ที่จะจบการศึกษาในปีนี้
                if ($currentGrade == 6) {
                    // เช็คว่าเป็นการเลื่อนชั้นจริงหรือไม่ (เช่น ปีการศึกษาใหม่)
                    // นักเรียน ม.6 จะจบการศึกษาเมื่อเลื่อนชั้น
                    if (!$isDryRun) {
                        DB::table('tb_students')
                            ->where('students_id', $student->students_id)
                            ->update([
                                'students_status' => 'graduated',
                                'updated_at' => now()
                            ]);
                    }
                    
                    $this->info("นักเรียน {$studentCode}: จบการศึกษา ม.6 (สถานะ: graduated)");
                    $updated++;
                    continue;
                }

                // ตรวจสอบว่าชั้นปีถูกต้อง (ม.1-5)
                if ($currentGrade < 1) {
                    $this->warn("นักเรียน {$studentCode} คำนวณชั้นปีได้ {$currentGrade} (ต่ำกว่า ม.1)");
                    continue;
                }

                // ดึงข้อมูลห้องจาก Google Sheets
                $roomNumber = $this->getRoomFromGoogleSheets($studentCode);
                
                if (!$roomNumber) {
                    $this->warn("ไม่พบข้อมูลห้องสำหรับนักเรียน {$studentCode} ใน Google Sheets");
                    continue;
                }

                // หา class_id จากตาราง tb_classes
                $class = DB::table('tb_classes')
                    ->where('classes_level', 'ม.' . $currentGrade)
                    ->where('classes_room_number', (string)$roomNumber)
                    ->first();

                if (!$class) {
                    $this->error("ไม่พบห้องเรียน ม.{$currentGrade}/{$roomNumber} ในตาราง tb_classes");
                    $errors++;
                    continue;
                }

                if (!$isDryRun) {
                    // อัปเดตข้อมูลนักเรียน
                    DB::table('tb_students')
                        ->where('students_id', $student->students_id)
                        ->update([
                            'class_id' => $class->classes_id,
                            'updated_at' => now()
                        ]);
                }

                $this->info("นักเรียน {$studentCode}: ชั้น ม.{$currentGrade} ห้อง {$roomNumber} (class_id: {$class->classes_id})");
                $updated++;

            } catch (\Exception $e) {
                $this->error("เกิดข้อผิดพลาดกับนักเรียน {$student->students_student_code}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("\n=== สรุปผลการดำเนินการ ===");
        $this->info("อัปเดตสำเร็จ: {$updated} คน");
        if ($errors > 0) {
            $this->error("เกิดข้อผิดพลาด: {$errors} คน");
        }

        return 0;
    }

    /**
     * ดึงข้อมูลหมายเลขห้องจาก Google Sheets
     */
    private function getRoomFromGoogleSheets($studentCode)
    {
        try {
            // ดึงข้อมูลจาก Google Sheets gid 0
            $data = $this->googleSheetsService->getSheetData(0);
            
            // หาข้อมูลนักเรียนตามรหัส
            foreach ($data as $row) {
                if (isset($row['รหัสนักเรียน']) && $row['รหัสนักเรียน'] == $studentCode) {
                    $room = isset($row['ห้อง']) ? (int)$row['ห้อง'] : null;
                    if ($room) {
                        return $room;
                    }
                }
            }
            
            // ถ้าไม่พบให้ลองหาจากคอลัมน์อื่น
            foreach ($data as $row) {
                if (isset($row['รหัสนักเรียน']) && $row['รหัสนักเรียน'] == $studentCode) {
                    // ลองหาจากคอลัมน์ที่มีคำว่า "ห้อง" หรือคล้ายๆ
                    foreach ($row as $key => $value) {
                        if (strpos(strtolower($key), 'ห้อง') !== false && !empty($value)) {
                            return (int)$value;
                        }
                    }
                }
            }
            
            $this->warn("ไม่พบข้อมูลห้องสำหรับนักเรียน {$studentCode} ใน Google Sheets - ใช้ค่าเริ่มต้นห้อง 1");
            return 1; // ค่าเริ่มต้น
            
        } catch (\Exception $e) {
            $this->error("เกิดข้อผิดพลาดในการดึงข้อมูลจาก Google Sheets: " . $e->getMessage());
            return 1; // ค่าเริ่มต้น
        }
    }
}
