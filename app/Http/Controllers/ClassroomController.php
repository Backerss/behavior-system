<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom; // ตรวจสอบว่ามีบรรทัดนี้
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClassroomController extends Controller
{
    /**
     * แสดงรายการห้องเรียนทั้งหมด
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */    public function index(Request $request)
    {
        try {
            // ระบุให้โหลด teacher และ teacher.user มาด้วย
            $query = ClassRoom::with(['teacher', 'teacher.user']);
            
            $searchTerm = $request->get('search', '');
            $academicYear = $request->get('academicYear', '');
            $level = $request->get('level', '');
            $perPage = $request->get('perPage', 10);
            
            // ค้นหาตาม search term
            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('classes_level', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('classes_room_number', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('teacher.user', function($subQ) use ($searchTerm) {
                          $subQ->where(DB::raw("CONCAT(users_first_name, ' ', users_last_name)"), 'LIKE', "%{$searchTerm}%");
                      });
                });
            }
            
            // กรองตามระดับชั้น
            if (!empty($level)) {
                $query->where('classes_level', $level);
            }
            
            $classes = $query->orderBy('classes_level')
                               ->orderBy('classes_room_number')
                               ->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $classes,
                'message' => 'ดึงข้อมูลสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลห้องเรียน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * บันทึกข้อมูลห้องเรียนใหม่
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // บันทึก log ข้อมูลที่ส่งเข้ามา
            \Log::info('Request data:', $request->all());
            
            $validator = Validator::make($request->all(), [
                'classes_level' => 'required|string|max:10',
                'classes_room_number' => 'required|string|max:5',
                'teacher_id' => 'required|exists:tb_teachers,teachers_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ข้อมูลไม่ถูกต้อง',
                    'errors' => $validator->errors()
                ], 422);
            }
              // สร้างห้องเรียนใหม่ - ต้องตรงกับชื่อคอลัมน์ในฐานข้อมูล
            $classroom = new ClassRoom();
            $classroom->classes_level = $request->classes_level;
            $classroom->classes_room_number = $request->classes_room_number;
            
            // สำคัญ! ต้องใช้ชื่อฟิลด์ให้ตรงกับในฐานข้อมูล
            // ถ้าคอลัมน์ในฐานข้อมูลชื่อ "teacher_id"
            // $classroom->teacher_id = $request->teacher_id;
            // ถ้าคอลัมน์ในฐานข้อมูลชื่อ "teachers_id"
            $classroom->teachers_id = $request->teacher_id;
            
            $classroom->save();
            
            return response()->json([
                'success' => true,
                'data' => $classroom,
                'message' => 'สร้างห้องเรียนสำเร็จ'
            ], 201);
            
        } catch (\Exception $e) {
            // บันทึก log ข้อผิดพลาดอย่างละเอียด
            \Log::error('Error saving classroom: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            // ส่งข้อความผิดพลาดกลับไป
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการสร้างห้องเรียน',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * แสดงข้อมูลห้องเรียนตาม ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // แก้จาก Classroom เป็น ClassRoom
            $classroom = ClassRoom::with(['teacher', 'teacher.user'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $classroom,
                'message' => 'ดึงข้อมูลสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลห้องเรียน',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * อัพเดทข้อมูลห้องเรียน
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'classes_level' => 'required|string|max:10',
                'classes_room_number' => 'required|string|max:5',
                'teacher_id' => 'required|exists:tb_teachers,teachers_id',
            ], [
                'classes_level.required' => 'กรุณาระบุระดับชั้น',
                'classes_room_number.required' => 'กรุณาระบุหมายเลขห้องเรียน',
                'teacher_id.required' => 'กรุณาเลือกครูประจำชั้น',
                'teacher_id.exists' => 'ไม่พบข้อมูลครูที่เลือก',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ข้อมูลไม่ถูกต้อง',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // แก้จาก Classroom เป็น ClassRoom
            $classroom = ClassRoom::findOrFail($id);
            
            // ตรวจสอบว่ามีห้องเรียนซ้ำหรือไม่ ยกเว้นห้องเรียนปัจจุบัน
            $existingClassroom = ClassRoom::where('classes_level', $request->classes_level)
                                    ->where('classes_room_number', $request->classes_room_number)
                                    ->where('classes_id', '!=', $id)
                                    ->first();
                                    
            if ($existingClassroom) {
                return response()->json([
                    'success' => false,
                    'message' => 'ห้องเรียนนี้มีอยู่ในระบบแล้ว',
                ], 422);
            }
            
            // อัพเดทข้อมูล
            $classroom->classes_level = $request->classes_level;
            $classroom->classes_room_number = $request->classes_room_number;
            $classroom->teachers_id = $request->teacher_id;
            $classroom->save();
            
            return response()->json([
                'success' => true,
                'data' => $classroom,
                'message' => 'อัพเดทข้อมูลห้องเรียนสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัพเดทข้อมูลห้องเรียน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ลบห้องเรียน
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // แก้จาก Classroom เป็น ClassRoom
            $classroom = ClassRoom::findOrFail($id);
            
            // ตรวจสอบว่ามีนักเรียนในห้องหรือไม่
            $studentsCount = $classroom->students()->count();
            if ($studentsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "ไม่สามารถลบห้องเรียนได้ เนื่องจากยังมีนักเรียนในห้องเรียนจำนวน {$studentsCount} คน",
                    'data' => [
                        'students_count' => $studentsCount
                    ]
                ], 422);
            }
            
            $classroom->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลห้องเรียนสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูลห้องเรียน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ดึงข้อมูลนักเรียนในห้องเรียน
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudents($id, Request $request)
    {
        try {
            $classroom = ClassRoom::findOrFail($id); // Ensure ClassRoom model is used
            $searchTerm = $request->get('search', '');
            $perPage = $request->get('perPage', 10); // Default to 10 students per page
            
            // Use eager loading for the 'user' relationship
            $query = Student::with('user')->where('class_id', $id);
        
            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('students_student_code', 'LIKE', "%{$searchTerm}%")
                      // Search within the related 'user' table
                      ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                          $userQuery->where('users_first_name', 'LIKE', "%{$searchTerm}%")
                                    ->orWhere('users_last_name', 'LIKE', "%{$searchTerm}%")
                                    ->orWhere(DB::raw("CONCAT(IFNULL(users_name_prefix, ''), users_first_name, ' ', users_last_name)"), 'LIKE', "%{$searchTerm}%");
                      });
                });
            }
            
            // Order by student's first name via the user relationship
            $query->join('tb_users', 'tb_students.user_id', '=', 'tb_users.users_id')
                  ->orderBy('tb_users.users_first_name')
                  ->orderBy('tb_users.users_last_name')
                  ->select('tb_students.*'); // Select only student columns after join for ordering

            $students = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $students, // This will now include the nested 'user' object for each student
                'message' => 'ดึงข้อมูลนักเรียนสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching students for class ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลนักเรียน',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ดึงข้อมูลครูทั้งหมดสำหรับ dropdown
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTeachers()
    {
        try {
            $teachers = Teacher::join('tb_users', 'tb_teachers.users_id', '=', 'tb_users.users_id')
                             ->select(
                                 'tb_teachers.teachers_id', 
                                 'tb_users.users_first_name',
                                 'tb_users.users_last_name',
                                 'tb_users.users_name_prefix',
                                 'tb_teachers.teachers_position',
                                 'tb_users.users_profile_image'
                             )
                             ->orderBy('tb_users.users_first_name')
                             ->get();
                             
            return response()->json([
                'success' => true,
                'data' => $teachers,
                'message' => 'ดึงข้อมูลครูทั้งหมดสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลครู',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ดึงข้อมูลระดับชั้นทั้งหมดสำหรับตัวกรอง
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilters()
    {
        try {
            $levels = ClassRoom::select('classes_level')
                             ->distinct()
                             ->orderBy('classes_level')
                             ->pluck('classes_level');
                             
            return response()->json([
                'success' => true,
                'data' => [
                    'levels' => $levels
                ],
                'message' => 'ดึงข้อมูลตัวกรองสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลตัวกรอง',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ดึงสถิติการกระทำผิดของห้องเรียน
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationStatistics($id)
    {
        try {
            // แก้จาก Classroom เป็น ClassRoom
            $classroom = ClassRoom::findOrFail($id);
            
            $stats = DB::table('tb_behavior_reports as br')
                ->join('tb_students as s', 'br.student_id', '=', 's.students_id')
                ->join('tb_violations as v', 'br.violation_id', '=', 'v.violations_id')
                ->where('s.class_id', $id)
                ->select('v.violations_id', 'v.violations_name as name', 
                         DB::raw('count(*) as count'),
                         DB::raw('avg(s.students_current_score) as avg_score'))
                ->groupBy('v.violations_id', 'v.violations_name')
                ->orderByDesc('count')
                ->limit(5)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'ดึงข้อมูลสถิติสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลสถิติ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ส่งออกรายงานห้องเรียนเป็น PDF
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function exportClassReport($id)
    {
        try {
            // แก้จาก Classroom เป็น ClassRoom
            $classroom = ClassRoom::with(['teacher.user', 'students.user'])
                            ->withCount('students')
                            ->findOrFail($id);
            
            // ดึงสถิติการกระทำผิด
            $violations = DB::table('tb_behavior_reports as br')
                ->join('tb_students as s', 'br.student_id', '=', 's.students_id')
                ->join('tb_violations as v', 'br.violation_id', '=', 'v.violations_id')
                ->where('s.class_id', $id)
                ->select('v.violations_name', DB::raw('count(*) as count'))
                ->groupBy('v.violations_name')
                ->orderByDesc('count')
                ->get();
            
            // ส่งกลับข้อมูล JSON แทน PDF ก่อน
            return response()->json([
                'success' => true,
                'data' => [
                    'classroom' => $classroom,
                    'violations' => $violations,
                    'date' => now()->format('d/m/Y')
                ],
                'message' => 'ดึงข้อมูลรายงานสำเร็จ'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการสร้างรายงาน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ดึงข้อมูลห้องเรียนสำหรับการลงทะเบียน
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClassesForRegistration()
    {
        try {
            $classes = ClassRoom::select('classes_id', 'classes_level', 'classes_room_number')
                ->orderBy('classes_level')
                ->orderBy('classes_room_number')
                ->get()
                ->map(function ($class) {
                    return [
                        'id' => $class->classes_id,
                        'label' => $class->classes_level . '/' . $class->classes_room_number,
                    ];
                });

            // เพิ่ม log สำหรับ debugging
            \Log::info('Classes retrieved successfully', ['count' => $classes->count()]);
            
            return response()->json($classes);
        
        } catch (\Exception $e) {
            \Log::error('Error in getClassesForRegistration: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        
            return response()->json([
                'error' => 'ไม่สามารถโหลดข้อมูลชั้นเรียนได้',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}