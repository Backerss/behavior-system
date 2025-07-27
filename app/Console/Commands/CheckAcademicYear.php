<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class CheckAcademicYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'academic:check {--force} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ตรวจสอบและจัดการปีการศึกษา ภาคเรียน และการเลื่อนชั้นอัตโนมัติ';

    protected $academicService;

    public function __construct(AcademicYearService $academicService)
    {
        parent::__construct();
        $this->academicService = $academicService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 เริ่มตรวจสอบสถานะปีการศึกษาและภาคเรียน...');
        
        // แสดงสถานะปัจจุบัน
        $this->showCurrentStatus();
        
        // ตรวจสอบการแจ้งเตือน
        $this->checkNotifications();
        
        // ตรวจสอบการเลื่อนชั้นอัตโนมัติ
        $this->checkAutoPromotion();
        
        $this->info('✅ การตรวจสอบเสร็จสิ้น');
        
        return 0;
    }

    /**
     * แสดงสถานะปัจจุบัน
     */
    private function showCurrentStatus()
    {
        $status = $this->academicService->getAcademicStatus();
        
        $this->info("\n📚 สถานะปัจจุบัน");
        $this->line("   ปีการศึกษา: {$status['academic_year']}");
        $this->line("   ภาคเรียน: {$status['semester_name']}");
        $this->line("   แสดงผล: {$status['display_text']}");
        
        // บันทึก Log
        Log::info('Academic status checked', $status);
    }

    /**
     * ตรวจสอบการแจ้งเตือน
     */
    private function checkNotifications()
    {
        $notifications = $this->academicService->getNotifications();
        
        if (empty($notifications)) {
            $this->info("\n✅ ไม่มีการแจ้งเตือนในขณะนี้");
            return;
        }
        
        $this->warn("\n⚠️  การแจ้งเตือน:");
        foreach ($notifications as $notification) {
            $icon = $notification['type'] === 'warning' ? '⚠️' : 'ℹ️';
            $this->line("   {$icon} {$notification['message']}");
            
            // บันทึก Log
            Log::info('Academic notification', $notification);
        }
    }

    /**
     * ตรวจสอบการเลื่อนชั้นอัตโนมัติ
     */
    private function checkAutoPromotion()
    {
        $shouldPromote = $this->academicService->shouldPromoteAcademicYear();
        $isForced = $this->option('force');
        $isDryRun = $this->option('dry-run');
        
        if (!$shouldPromote && !$isForced) {
            $this->info("\n📅 ยังไม่ถึงเวลาการเลื่อนชั้น");
            return;
        }
        
        if ($isForced) {
            $this->warn("\n🔧 บังคับการเลื่อนชั้น (--force)");
        } else {
            $this->info("\n🎓 ถึงเวลาการเลื่อนชั้นอัตโนมัติ!");
        }
        
        if ($isDryRun) {
            $this->warn("🧪 โหมดทดสอบ (--dry-run) - ไม่มีการเปลี่ยนแปลงข้อมูลจริง");
        }
        
        // รันการเลื่อนชั้น
        $this->runStudentPromotion($isDryRun);
        
        // อัปเดตปีการศึกษา (เฉพาะเมื่อไม่ใช่ dry-run)
        if (!$isDryRun) {
            $this->updateAcademicYear();
        }
    }

    /**
     * รันการเลื่อนชั้นนักเรียน
     */
    private function runStudentPromotion($isDryRun = false)
    {
        $this->info("\n🎯 เริ่มกระบวนการเลื่อนชั้นนักเรียน...");
        
        $currentYear = $this->academicService->getCurrentAcademicYear();
        $newYear = $currentYear + 1;
        
        // เรียกใช้ Command การเลื่อนชั้น
        $options = [
            '--year' => $newYear
        ];
        
        if ($isDryRun) {
            $options['--dry-run'] = true;
        }
        
        try {
            $exitCode = Artisan::call('students:promote', $options);
            
            if ($exitCode === 0) {
                $output = Artisan::output();
                $this->line($output);
                
                if (!$isDryRun) {
                    $this->info("✅ การเลื่อนชั้นสำเร็จ!");
                    
                    // บันทึก Log
                    Log::info('Student promotion completed successfully', [
                        'old_year' => $currentYear,
                        'new_year' => $newYear
                    ]);
                }
            } else {
                $this->error("❌ การเลื่อนชั้นล้มเหลว (Exit Code: {$exitCode})");
                Log::error('Student promotion failed', ['exit_code' => $exitCode]);
            }
            
        } catch (\Exception $e) {
            $this->error("❌ เกิดข้อผิดพลาดในการเลื่อนชั้น: " . $e->getMessage());
            Log::error('Student promotion error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * อัปเดตปีการศึกษาและภาคเรียน
     */
    private function updateAcademicYear()
    {
        $currentYear = $this->academicService->getCurrentAcademicYear();
        $newYear = $currentYear + 1;
        
        try {
            // อัปเดตปีการศึกษา
            $this->academicService->updateAcademicSettings($newYear, 1);
            
            // ล้าง Cache
            $this->academicService->clearAcademicCache();
            
            $this->info("📅 อัปเดตปีการศึกษาเป็น {$newYear} ภาคเรียนที่ 1");
            
            // บันทึก Log
            Log::info('Academic year updated', [
                'old_year' => $currentYear,
                'new_year' => $newYear
            ]);
            
        } catch (\Exception $e) {
            $this->error("❌ ไม่สามารถอัปเดตปีการศึกษาได้: " . $e->getMessage());
            Log::error('Academic year update failed', ['error' => $e->getMessage()]);
        }
    }
}
