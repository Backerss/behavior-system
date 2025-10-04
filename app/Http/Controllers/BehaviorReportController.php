<?php
// filepath: c:\Users\AsanR\OneDrive\Desktop\behavior-system\app\Http\Controllers\BehaviorReportController.php

namespace App\Http\Controllers;

use App\Models\BehaviorReport;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Violation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\BehaviorLog;

class BehaviorReportController extends Controller
{
    /**
     * บันทึกพฤติกรรมนักเรียนใหม่
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // ตรวจสอบข้อมูลที่ส่งมา
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:tb_students,students_id',
                'violation_id' => 'required|exists:tb_violations,violations_id',
                'violation_datetime' => 'required|date_format:Y-m-d H:i',
                'description' => 'nullable|string|max:1000',
                'evidence' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Max 2MB
            ], [
                'student_id.required' => 'กรุณาเลือกนักเรียน',
                'student_id.exists' => 'ไม่พบข้อมูลนักเรียนที่เลือก',
                'violation_id.required' => 'กรุณาเลือกประเภทการกระทำผิด',
                'violation_id.exists' => 'ไม่พบข้อมูลประเภทการกระทำผิด',
                'violation_datetime.required' => 'กรุณาระบุวันและเวลาที่เกิดเหตุ',
                'violation_datetime.date_format' => 'รูปแบบวันและเวลาไม่ถูกต้อง',
                'evidence.image' => 'ไฟล์ที่แนบต้องเป็นรูปภาพเท่านั้น',
                'evidence.mimes' => 'รองรับเฉพาะไฟล์รูปภาพนามสกุล: jpeg, png, jpg, gif',
                'evidence.max' => 'ขนาดไฟล์รูปภาพต้องไม่เกิน 2MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ข้อมูลไม่ถูกต้อง',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลผู้ใช้'
                ], 403);
            }

            // ต้องเป็นครูที่มีข้อมูลใน tb_teachers เท่านั้น
            $teacher = DB::table('tb_teachers')
                ->where('users_id', $user->users_id)
                ->first();
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลครูผู้บันทึก'
                ], 403);
            }

            $student = DB::table('tb_students')
                ->where('students_id', $request->student_id)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลนักเรียน'
                ], 404);
            }

            $violation = DB::table('tb_violations')
                ->where('violations_id', $request->violation_id)
                ->first();

            if (!$violation) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลประเภทการกระทำผิด'
                ], 404);
            }

            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $file = $request->file('evidence');
                $filename = time() . '_' . $file->getClientOriginalName();
                // Store in 'storage/app/public/behavior_evidences'
                $path = $file->storeAs('public/behavior_evidences', $filename);
                // Path for database will be 'behavior_evidences/filename.ext'
                $evidencePath = str_replace('public/', '', $path);
            }

            // สร้างรายงานพฤติกรรมใหม่ พร้อม snapshot คะแนนที่หัก ณ ตอนบันทึก
            $snapshotPoints = abs($violation->violations_points_deducted ?? 0);
            $reportId = DB::table('tb_behavior_reports')->insertGetId([
                'student_id' => $request->student_id,
                'teacher_id' => $teacher->teachers_id,
                'violation_id' => $request->violation_id,
                'reports_points_deducted' => $snapshotPoints,
                'reports_description' => $request->description,
                'reports_evidence_path' => $evidencePath,
                'reports_report_date' => Carbon::parse($request->violation_datetime), // Combined date and time
                'created_at' => now(),
            ]);

            // อัพเดทคะแนนพฤติกรรมนักเรียนตาม snapshot
            $newScore = max(0, ($student->students_current_score ?? 100) - $snapshotPoints);
            
            DB::table('tb_students')
                ->where('students_id', $request->student_id)
                ->update([
                    'students_current_score' => $newScore,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'บันทึกพฤติกรรมสำเร็จ',
                'data' => [
                    'report_id' => $reportId,
                    'reports_points_deducted' => $snapshotPoints,
                    'student_updated_score' => $newScore
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error saving behavior report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการบันทึกพฤติกรรม',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ค้นหานักเรียนสำหรับฟอร์มบันทึกพฤติกรรม
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchStudents(Request $request)
    {
        try {
            $searchTerm = $request->input('term', '');
            $classId = $request->input('class_id', '');
            
            $query = DB::table('tb_students as s')
                ->join('tb_users as u', 's.user_id', '=', 'u.users_id')
                ->leftJoin('tb_classes as c', 's.class_id', '=', 'c.classes_id')
                ->select(
                    's.students_id',
                    's.students_current_score',
                    's.students_student_code',
                    'u.users_name_prefix',
                    'u.users_first_name',
                    'u.users_last_name',
                    'c.classes_level',
                    'c.classes_room_number'
                );
            
            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('u.users_first_name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('u.users_last_name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('s.students_student_code', 'LIKE', '%' . $searchTerm . '%');
                });
            }
            
            if (!empty($classId)) {
                $query->where('s.class_id', $classId);
            }
            
            $students = $query->limit(10)->get();
            
            $results = $students->map(function($student) {
                return [
                    'id' => $student->students_id,
                    'name' => ($student->users_name_prefix ?? '') . 
                             ($student->users_first_name ?? '') . ' ' . 
                             ($student->users_last_name ?? ''),
                    'student_id' => $student->students_student_code ?? '',
                    'class' => $student->classes_level && $student->classes_room_number 
                              ? $student->classes_level . '/' . $student->classes_room_number 
                              : 'ไม่ระบุ',
                    'current_score' => $student->students_current_score ?? 100
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error searching students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการค้นหานักเรียน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ดึงรายงานพฤติกรรมล่าสุด
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentReports(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            
            $reports = DB::table('tb_behavior_reports as br')
                ->join('tb_students as s', 'br.student_id', '=', 's.students_id')
                ->join('tb_users as su', 's.user_id', '=', 'su.users_id')
                ->join('tb_teachers as t', 'br.teacher_id', '=', 't.teachers_id')
                ->join('tb_users as tu', 't.users_id', '=', 'tu.users_id')
                ->join('tb_violations as v', 'br.violation_id', '=', 'v.violations_id')
                ->select(
                    'br.reports_id',
                    'br.reports_description',
                    'br.reports_report_date',
                    'br.created_at',
                    'br.reports_points_deducted',
                    'su.users_name_prefix as student_prefix',
                    'su.users_first_name as student_first_name',
                    'su.users_last_name as student_last_name',
                    'tu.users_name_prefix as teacher_prefix',
                    'tu.users_first_name as teacher_first_name',
                    'tu.users_last_name as teacher_last_name',
                    'v.violations_name'
                )
                ->orderBy('br.created_at', 'desc')
                ->limit($limit)
                ->get();
            
            $results = $reports->map(function($report) {
                return [
                    'id' => $report->reports_id,
                    'student_name' => ($report->student_prefix ?? '') . 
                                    ($report->student_first_name ?? '') . ' ' . 
                                    ($report->student_last_name ?? ''),
                    'violation_name' => $report->violations_name ?? '',
                    'points_deducted' => $report->reports_points_deducted ?? 0,
                    'teacher_name' => ($report->teacher_prefix ?? '') . 
                                     ($report->teacher_first_name ?? '') . ' ' . 
                                     ($report->teacher_last_name ?? ''),
                    'report_date' => $report->reports_report_date,
                    'created_at' => Carbon::parse($report->created_at)->format('d/m/Y H:i'),
                    'description' => $report->reports_description ?? ''
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching recent reports: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลรายงาน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ดึงรายละเอียดรายงานพฤติกรรมตาม ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $report = DB::table('tb_behavior_reports as br')
                ->join('tb_students as s', 'br.student_id', '=', 's.students_id')
                ->join('tb_users as su', 's.user_id', '=', 'su.users_id')
                ->join('tb_teachers as t', 'br.teacher_id', '=', 't.teachers_id')
                ->join('tb_users as tu', 't.users_id', '=', 'tu.users_id')
                ->join('tb_violations as v', 'br.violation_id', '=', 'v.violations_id')
                ->leftJoin('tb_classes as c', 's.class_id', '=', 'c.classes_id')
                ->select(
                    'br.reports_id',
                    'br.reports_description',
                    'br.reports_evidence_path',
                    'br.reports_report_date',
                    'br.created_at',
                    'br.reports_points_deducted',
                    // Student info
                    'su.users_name_prefix as student_prefix',
                    'su.users_first_name as student_first_name',
                    'su.users_last_name as student_last_name',
                    's.students_student_code',
                    's.students_current_score',
                    // Teacher info
                    'tu.users_name_prefix as teacher_prefix',
                    'tu.users_first_name as teacher_first_name',
                    'tu.users_last_name as teacher_last_name',
                    // Violation info
                    'v.violations_name',
                    'v.violations_category',
                    // note: keep current violation info for metadata, but use br.reports_points_deducted as snapshot
                    'v.violations_description as violation_description',
                    // Class info
                    'c.classes_level',
                    'c.classes_room_number'
                )
                ->where('br.reports_id', $id)
                ->first();

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลรายงานพฤติกรรม'
                ], 404);
            }

            $result = [
                'id' => $report->reports_id,
                'student' => [
                    'name' => ($report->student_prefix ?? '') . 
                             ($report->student_first_name ?? '') . ' ' . 
                             ($report->student_last_name ?? ''),
                    'student_code' => $report->students_student_code ?? '',
                    'current_score' => $report->students_current_score ?? 100,
                    'class' => $report->classes_level && $report->classes_room_number 
                              ? $report->classes_level . '/' . $report->classes_room_number 
                              : 'ไม่ระบุ',
                    'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode(($report->student_first_name ?? '') . ' ' . ($report->student_last_name ?? '')) . '&background=95A4D8&color=fff'
                ],
                'violation' => [
                    'name' => $report->violations_name ?? '',
                    'category' => $report->violations_category ?? '',
                    'points_deducted' => $report->reports_points_deducted ?? 0,
                    'description' => $report->violation_description ?? ''
                ],
                'teacher' => [
                    'name' => ($report->teacher_prefix ?? '') . 
                             ($report->teacher_first_name ?? '') . ' ' . 
                             ($report->teacher_last_name ?? '')
                ],
                'report' => [
                    'description' => $report->reports_description ?? '',
                    'evidence_path' => $report->reports_evidence_path ?? null,
                    'evidence_url' => $report->reports_evidence_path 
                                     ? asset('storage/' . $report->reports_evidence_path)
                                     : null,
                    'report_date' => Carbon::parse($report->reports_report_date)->format('j M Y, H:i น.'),
                    'report_date_thai' => Carbon::parse($report->reports_report_date)->locale('th')->format('j F Y, H:i น.'),
                    'report_datetime' => $report->reports_report_date, // Raw datetime for editing
                    'created_at' => Carbon::parse($report->created_at)->format('d/m/Y H:i')
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching behavior report detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลรายละเอียดรายงาน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * อัปเดตรายงานพฤติกรรมนักเรียน
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // ค้นหารายงานพฤติกรรม
            $behaviorReport = BehaviorReport::find($id);
            
            if (!$behaviorReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบรายงานพฤติกรรมที่ต้องการแก้ไข'
                ], 404);
            }

            // ตรวจสอบสิทธิ์ในการแก้ไข (เฉพาะครูที่บันทึกหรือ admin)
            $user = Auth::user();
            // ตรวจสิทธิ์: admin ทำได้ทุกอย่าง, ครูต้องเป็นคนที่สร้างรายงานนี้
            if ($user->users_role !== 'admin') {
                // หา teacher จาก users_id ของ user ปัจจุบัน
                $currentTeacher = DB::table('tb_teachers')
                    ->where('users_id', $user->users_id)
                    ->first();
                if (!$currentTeacher || (int)$behaviorReport->teacher_id !== (int)$currentTeacher->teachers_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'คุณไม่มีสิทธิ์แก้ไขรายงานนี้'
                    ], 403);
                }
            }

            // ตรวจสอบข้อมูลที่ส่งมา
            $validator = Validator::make($request->all(), [
                'violation_id' => 'required|exists:tb_violations,violations_id',
                // JS ส่งมาเป็น 'report_datetime' แบบ Y-m-d H:i:s ถ้าไม่มีวินาทีจะเติม :00 แล้ว
                'report_datetime' => 'required|date_format:Y-m-d H:i:s',
                'description' => 'nullable|string|max:1000',
                'evidence' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'violation_id.required' => 'กรุณาเลือกประเภทการกระทำผิด',
                'violation_id.exists' => 'ไม่พบข้อมูลประเภทการกระทำผิด',
                'report_datetime.required' => 'กรุณาระบุวันและเวลาที่เกิดเหตุ',
                'report_datetime.date_format' => 'รูปแบบวันและเวลาไม่ถูกต้อง',
                'evidence.image' => 'ไฟล์ที่แนบต้องเป็นรูปภาพเท่านั้น',
                'evidence.mimes' => 'รองรับเฉพาะไฟล์รูปภาพนามสกุล: jpeg, png, jpg, gif',
                'evidence.max' => 'ขนาดไฟล์รูปภาพต้องไม่เกิน 2MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ข้อมูลไม่ถูกต้อง',
                    'errors' => $validator->errors()
                ], 422);
            }

            // เริ่ม transaction
            DB::beginTransaction();

            // ข้อมูลการกระทำผิดใหม่
            $violation = Violation::find($request->violation_id);
            
            // Snapshot เก่าก่อนแก้ไข
            $before = [
                'violation_id' => $behaviorReport->violation_id,
                'reports_points_deducted' => $behaviorReport->reports_points_deducted,
                'reports_report_date' => (string)$behaviorReport->reports_report_date,
                'reports_description' => $behaviorReport->reports_description,
            ];

            // จัดการไฟล์หลักฐานใหม่ (ถ้ามี)
            $evidencePath = $behaviorReport->reports_evidence_path;
            if ($request->hasFile('evidence')) {
                // ลบไฟล์เก่า
                if ($evidencePath && Storage::disk('public')->exists($evidencePath)) {
                    Storage::disk('public')->delete($evidencePath);
                }
                // อัปโหลดไฟล์ใหม่ ให้ path สอดคล้องกับ store()
                $file = $request->file('evidence');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('public/behavior_evidences', $filename);
                $evidencePath = str_replace('public/', '', $path); // เก็บเฉพาะส่วนภายใน storage
            }

            // อัปเดตรายงาน + อัปเดต snapshot คะแนนตามประเภทปัจจุบัน
            $behaviorReport->update([
                'violation_id' => $request->violation_id,
                'reports_points_deducted' => abs($violation->violations_points_deducted ?? 0),
                'reports_report_date' => $request->report_datetime,
                'reports_description' => $request->description,
                'reports_evidence_path' => $evidencePath,
            ]);

            // คำนวณคะแนนสะสมใหม่จาก snapshot ทั้งหมดของนักเรียน
            $student = Student::find($behaviorReport->student_id);
            if ($student) {
                $totalDeducted = BehaviorReport::where('student_id', $student->students_id)
                    ->sum('reports_points_deducted');
                $student->students_current_score = max(0, 100 - (int)$totalDeducted);
                $student->save();
            }

            // บันทึก Behavior Log
            BehaviorLog::create([
                'behavior_report_id' => $behaviorReport->reports_id,
                'action_type' => 'update',
                'performed_by' => $user->users_id,
                'before_change' => $before,
                'after_change' => [
                    'violation_id' => $behaviorReport->violation_id,
                    'reports_points_deducted' => $behaviorReport->reports_points_deducted,
                    'reports_report_date' => (string)$behaviorReport->reports_report_date,
                    'reports_description' => $behaviorReport->reports_description,
                ],
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขรายงานพฤติกรรมเรียบร้อยแล้ว',
                'data' => $behaviorReport
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating behavior report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการแก้ไขรายงาน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ลบรายงานพฤติกรรมนักเรียน
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // ค้นหารายงานพฤติกรรม
            $behaviorReport = BehaviorReport::find($id);
            
            if (!$behaviorReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบรายงานพฤติกรรมที่ต้องการลบ'
                ], 404);
            }

            // ตรวจสอบสิทธิ์ในการลบ (เฉพาะครูที่บันทึกหรือ admin)
            $user = Auth::user();
            if ($user->users_role !== 'admin') {
                $currentTeacherId = DB::table('tb_teachers')->where('users_id', $user->users_id)->value('teachers_id');
                if (!$currentTeacherId || (int)$behaviorReport->teacher_id !== (int)$currentTeacherId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'คุณไม่มีสิทธิ์ลบรายงานนี้'
                    ], 403);
                }
            }

            // เริ่ม transaction
            DB::beginTransaction();

            // เก็บค่า before สำหรับ log
            $before = [
                'violation_id' => $behaviorReport->violation_id,
                'reports_points_deducted' => $behaviorReport->reports_points_deducted,
            ];

            // ลบไฟล์หลักฐาน (ถ้ามี)
            if ($behaviorReport->reports_evidence_path && Storage::disk('public')->exists($behaviorReport->reports_evidence_path)) {
                Storage::disk('public')->delete($behaviorReport->reports_evidence_path);
            }

            // ลบรายงาน
            $behaviorReport->delete();

            // คำนวณคะแนนสะสมใหม่จาก snapshot ทั้งหมดของนักเรียน
            $student = Student::find($behaviorReport->student_id);
            if ($student) {
                $totalDeducted = BehaviorReport::where('student_id', $student->students_id)
                    ->sum('reports_points_deducted');
                $student->students_current_score = max(0, 100 - (int)$totalDeducted);
                $student->save();
            }

            // บันทึก Behavior Log
            BehaviorLog::create([
                'behavior_report_id' => $behaviorReport->reports_id,
                'action_type' => 'delete',
                'performed_by' => $user->users_id,
                'before_change' => $before,
                'after_change' => null,
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ลบรายงานพฤติกรรมเรียบร้อยแล้ว'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting behavior report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบรายงาน',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}