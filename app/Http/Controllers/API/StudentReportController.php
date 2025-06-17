<?php
// filepath: c:\Users\AsanR\OneDrive\Desktop\วิจัยแก้ม\behavior-system\app\Http\Controllers\API\StudentReportController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mpdf\Mpdf;

class StudentReportController extends Controller
{
    /**
     * สร้างรายงาน PDF ของนักเรียน
     */
    public function generatePDF(Request $request, $id)
    {
        try {
            // ตรวจสอบว่าผู้ใช้ login แล้วหรือไม่
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาเข้าสู่ระบบ'
                ], 401);
            }

            // Log การเข้าถึง
            Log::info('StudentReportController@generatePDF accessed.', [
                'student_id' => $id,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip()
            ]);
            
            // ดึงข้อมูลนักเรียน
            $student = Student::with([
                'user',
                'classroom',
                'behaviorReports.violation',
                'behaviorReports.teacher.user',
                'guardians.user'
            ])->findOrFail($id);

            // ตรวจสอบว่ามี user หรือไม่
            if (!$student->user) {
                Log::warning('Student has no associated user: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลผู้ใช้ของนักเรียนรหัส ' . $id
                ], 404);
            }

            // โหลด guardian
            try {
                $guardians = $student->guardians()->with('user')->get();
                $student->guardian = $guardians->first();
            } catch (\Exception $e) {
                Log::warning('Could not load guardian relationship for student: ' . $id, ['error' => $e->getMessage()]);
                $student->guardian = null;
            }

            Log::info('Student data found for ID: ' . $id);
            
            // กำหนดที่อยู่ฟอนต์ไทย
            $fontsPath = public_path('fonts');
            
            // ตรวจสอบว่ามีฟอนต์ไทยหรือไม่
            if (File::isDirectory($fontsPath) && File::exists($fontsPath . '/THSarabunNew.ttf')) {
                try {
                    Log::info('THSarabunNew font found. Using Thai font.');
                    
                    // สร้าง mPDF พร้อมฟอนต์ไทย
                    $mpdf = new Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'orientation' => 'P',
                        'margin_left' => 20,
                        'margin_right' => 20,
                        'margin_top' => 25,
                        'margin_bottom' => 25,
                        'margin_header' => 10,
                        'margin_footer' => 10,
                        'default_font_size' => 16,
                        'default_font' => 'thsarabunnew',
                        'fontDir' => [$fontsPath],
                        'fontdata' => [
                            'thsarabunnew' => [
                                'R' => 'THSarabunNew.ttf',
                                'B' => 'THSarabunNew Bold.ttf',
                                'I' => 'THSarabunNew Italic.ttf',
                                'BI' => 'THSarabunNew BoldItalic.ttf',
                            ]
                        ]
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create mPDF with Thai font: ' . $e->getMessage());
                    // ใช้ฟอนต์เริ่มต้น
                    $mpdf = new Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'orientation' => 'P',
                        'margin_left' => 20,
                        'margin_right' => 20,
                        'margin_top' => 25,
                        'margin_bottom' => 25,
                        'margin_header' => 10,
                        'margin_footer' => 10,
                        'default_font_size' => 16
                    ]);
                }
            } else {
                Log::warning('THSarabunNew font not found. Using default font.');
                // ใช้ฟอนต์เริ่มต้น
                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'orientation' => 'P',
                    'margin_left' => 20,
                    'margin_right' => 20,
                    'margin_top' => 25,
                    'margin_bottom' => 25,
                    'margin_header' => 10,
                    'margin_footer' => 10,
                    'default_font_size' => 16
                ]);
            }
            
            // กำหนดหัวกระดาษและท้ายกระดาษ
            $mpdf->SetHTMLHeader('
                <div style="text-align: center; font-size: 12pt; color: #666; font-family: thsarabunnew, sans-serif;">
                    โรงเรียนนวมินทราชูทิศ มัชฌิม - รายงานพฤติกรรมและความประพฤตินักเรียน
                </div>
            ');
            
            $mpdf->SetHTMLFooter('
                <div style="text-align: center; font-size: 10pt; color: #666; font-family: thsarabunnew, sans-serif;">
                    หน้า {PAGENO} จาก {nbpg} | ระบบสารสนเทศจัดการคะแนนวินัยกรณีศึกษาโรงเรียนนวมินทราชูทิศ มัชฌิม
                </div>
            ');
            
            // สร้าง HTML
            try {
                $html = view('reports.student_report', compact('student'))->render();
            } catch (\Exception $e) {
                Log::error('Error rendering blade template: ' . $e->getMessage());
                $html = $this->generateSimpleHTML($student);
            }
            
            // เพิ่ม HTML เข้าไปใน PDF
            $mpdf->WriteHTML($html);
            
            // สร้างชื่อไฟล์
            $filename = 'รายงานพฤติกรรม_' . ($student->students_student_code ?? $id) . '_' . date('Ymd_His') . '.pdf';
            
            Log::info('PDF generated successfully for student ID: ' . $id);
            
            // ส่งไฟล์ PDF กลับไปยัง client
            return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"$filename\"")
                ->header('Cache-Control', 'private, max-age=0, must-revalidate')
                ->header('Pragma', 'public');
                
        } catch (ModelNotFoundException $e) {
            Log::warning('Student not found for ID: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลนักเรียนรหัส ' . $id
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error generating PDF for student ID: ' . $id, [
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการสร้างรายงาน'
            ], 500);
        }
    }

    /**
     * สร้าง HTML แบบง่ายๆ สำหรับกรณีที่ template ไม่พร้อม
     */
    private function generateSimpleHTML($student)
    {
        $fullName = ($student->user->users_name_prefix ?? '') . 
                   ($student->user->users_first_name ?? '') . ' ' . 
                   ($student->user->users_last_name ?? '');
        
        $classroom = $student->classroom 
            ? $student->classroom->classes_level . '/' . $student->classroom->classes_room_number
            : 'ไม่มีข้อมูล';

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>รายงานพฤติกรรมนักเรียน</title>
            <style>
                body { 
                    font-family: "thsarabunnew", "Garuda", "Browallia New", "MS Sans Serif", sans-serif; 
                    font-size: 16pt; 
                    line-height: 1.5; 
                }
            </style>
        </head>
        <body>
            <h1 style="text-align: center;">รายงานพฤติกรรมนักเรียน</h1>
            <p><strong>ชื่อ-นามสกุล:</strong> ' . htmlspecialchars($fullName) . '</p>
            <p><strong>รหัสนักเรียน:</strong> ' . htmlspecialchars($student->students_student_code ?? 'ไม่มีข้อมูล') . '</p>
            <p><strong>ชั้นเรียน:</strong> ' . htmlspecialchars($classroom) . '</p>
            <p><strong>คะแนนปัจจุบัน:</strong> ' . ($student->students_current_score ?? 100) . '/100</p>
        </body>
        </html>';
    }
}