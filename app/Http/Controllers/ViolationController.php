<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\BehaviorReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ViolationController extends Controller
{
    /**
     * แสดงรายการประเภทพฤติกรรมทั้งหมด
     */
    public function index(Request $request)
    {
        try {
            $query = Violation::query();
            
            // ค้นหาตามชื่อ
            if ($request->has('search') && !empty($request->search)) {
                $query->where('violations_name', 'LIKE', '%' . $request->search . '%');
            }
            
            // เรียงลำดับ
            $query->orderBy('violations_name', 'asc');
            
            // แบ่งหน้า
            $violations = $query->paginate(10);
            
            return response()->json([
                'success' => true,
                'data' => $violations
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching violations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลประเภทพฤติกรรม',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * บันทึกประเภทพฤติกรรมใหม่
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'violations_name' => 'required|string|max:150|unique:tb_violations',
                'violations_category' => 'required|in:light,medium,severe',
                'violations_points_deducted' => 'required|integer|min:1|max:100',
                'violations_description' => 'nullable|string'
            ], [
                'violations_name.required' => 'กรุณาระบุชื่อประเภทพฤติกรรม',
                'violations_name.unique' => 'ชื่อประเภทพฤติกรรมนี้มีอยู่ในระบบแล้ว',
                'violations_category.required' => 'กรุณาเลือกระดับความรุนแรง',
                'violations_points_deducted.required' => 'กรุณาระบุคะแนนที่หัก',
                'violations_points_deducted.min' => 'คะแนนที่หักต้องไม่น้อยกว่า 1 คะแนน',
                'violations_points_deducted.max' => 'คะแนนที่หักต้องไม่เกิน 100 คะแนน'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ข้อมูลไม่ถูกต้อง',
                    'errors' => $validator->errors()
                ], 422);
            }

            $violation = Violation::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มประเภทพฤติกรรมเรียบร้อยแล้ว',
                'data' => $violation
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating violation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลประเภทพฤติกรรม',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * แสดงข้อมูลประเภทพฤติกรรมตาม ID
     */
    public function show($id)
    {
        try {
            $violation = Violation::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $violation
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching violation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลประเภทพฤติกรรม',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * อัปเดตข้อมูลประเภทพฤติกรรม
     */
    public function update(Request $request, $id)
    {
        try {
            $violation = Violation::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'violations_name' => 'required|string|max:150|unique:tb_violations,violations_name,' . $id . ',violations_id',
                'violations_category' => 'required|in:light,medium,severe',
                'violations_points_deducted' => 'required|integer|min:1|max:100',
                'violations_description' => 'nullable|string'
            ], [
                'violations_name.required' => 'กรุณาระบุชื่อประเภทพฤติกรรม',
                'violations_name.unique' => 'ชื่อประเภทพฤติกรรมนี้มีอยู่ในระบบแล้ว',
                'violations_category.required' => 'กรุณาเลือกระดับความรุนแรง',
                'violations_points_deducted.required' => 'กรุณาระบุคะแนนที่หัก',
                'violations_points_deducted.min' => 'คะแนนที่หักต้องไม่น้อยกว่า 1 คะแนน',
                'violations_points_deducted.max' => 'คะแนนที่หักต้องไม่เกิน 100 คะแนน'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ข้อมูลไม่ถูกต้อง',
                    'errors' => $validator->errors()
                ], 422);
            }

            $violation->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'อัปเดตประเภทพฤติกรรมเรียบร้อยแล้ว',
                'data' => $violation
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating violation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูลประเภทพฤติกรรม',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ลบประเภทพฤติกรรม
     */
    public function destroy($id)
    {
        try {
            $violation = Violation::findOrFail($id);
            
            // ตรวจสอบก่อนว่ามีการใช้งานประเภทนี้ในการบันทึกพฤติกรรมหรือไม่
            $reportsCount = BehaviorReport::where('violation_id', $id)->count();
            
            if ($reportsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ เนื่องจากมีการใช้งานประเภทพฤติกรรมนี้ในการบันทึกพฤติกรรมแล้ว',
                    'reports_count' => $reportsCount
                ], 400);
            }
            
            $violation->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'ลบประเภทพฤติกรรมเรียบร้อยแล้ว'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting violation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูลประเภทพฤติกรรม',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ดึงข้อมูลประเภทพฤติกรรมทั้งหมดแบบไม่แบ่งหน้า (สำหรับ dropdown)
     */
    public function getAll()
    {
        try {
            $violations = Violation::orderBy('violations_name', 'asc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $violations
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching all violations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลประเภทพฤติกรรม',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}