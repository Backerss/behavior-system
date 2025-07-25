# ขั้นตอนการทดสอบระบบ Google Sheets Import

## ขั้นตอนการทดสอบ

### 1. ทดสอบการเข้าถึงหน้า Dashboard
- เข้าสู่ระบบด้วยบัญชี Admin
- ตรวจสอบว่าเห็นปุ่ม "นำเข้าจาก Google Sheets" ใน Sidebar
- คลิกปุ่มเพื่อเปิด Modal

### 2. ทดสอบการดึงข้อมูล
- คลิกปุ่ม "ดูตัวอย่างข้อมูล"
- ตรวจสอบ Loading Spinner
- ตรวจสอบการแสดงผลสถิติ (ข้อมูลถูกต้อง, ซ้ำ, ผิดพลาด)

### 3. ทดสอบการแสดงผลข้อมูล
- ตรวจสอบ Tab "ข้อมูลถูกต้อง" (สีเขียว)
- ตรวจสอบ Tab "ข้อมูลซ้ำ" (สีเหลือง)
- ตรวจสอบ Tab "ข้อมูลผิดพลาด" (สีแดง)

### 4. ทดสอบการเลือกข้อมูล
- ทดสอบ Checkbox เลือกข้อมูลทีละรายการ
- ทดสอบปุ่ม "เลือกทั้งหมด"
- ทดสอบปุ่ม "ยกเลิกทั้งหมด"

### 5. ทดสอบการนำเข้าข้อมูล
- เลือกข้อมูลที่ต้องการ
- คลิกปุ่ม "นำเข้าข้อมูลที่เลือก"
- ตรวจสอบ Confirmation Dialog
- ตรวจสอบ Loading Spinner
- ตรวจสอบข้อความสำเร็จ

### 6. ทดสอบ Error Handling
- ทดสอบเมื่อไม่มีข้อมูลใน Google Sheets
- ทดสอบเมื่อ Google Sheets ไม่สามารถเข้าถึงได้
- ทดสอบเมื่อข้อมูลมีรูปแบบผิด

## ตัวอย่างข้อมูลสำหรับทดสอบ

### ข้อมูลถูกต้อง
```
prefix,first_name,last_name,email,phone,role,student_id,classroom,gender
นาย,ทดสอบ,ระบบ,test@example.com,0812345678,student,S999,ป.1/1,male
```

### ข้อมูลซ้ำ (ใช้อีเมลที่มีในระบบแล้ว)
```
prefix,first_name,last_name,email,phone,role,student_id,classroom,gender
นาย,ซ้ำ,อีเมล,existing@example.com,0812345678,student,S998,ป.1/1,male
```

### ข้อมูลผิดพลาด
```
prefix,first_name,last_name,email,phone,role,student_id,classroom,gender
นาย,,ไม่มีชื่อ,invalid-email,0812345678,student,S997,ป.1/1,male
```

## การแก้ปัญหาที่พบบ่อย

### 1. 403 Forbidden Error
- ตรวจสอบว่า Google Sheets ตั้งค่าเป็น Public
- ตรวจสอบ URL ว่าถูกต้อง

### 2. ไม่เห็นปุ่ม Import
- ตรวจสอบว่าเข้าสู่ระบบด้วยบัญชี Admin
- ตรวจสอบ role ในฐานข้อมูล

### 3. Modal ไม่เปิด
- ตรวจสอบ JavaScript Console สำหรับ Error
- ตรวจสอบ Bootstrap JS ที่โหลดถูกต้อง

### 4. ข้อมูลไม่แสดง
- ตรวจสอบ Network Tab ใน Developer Tools
- ตรวจสอบ Response จาก API

## Files ที่เกี่ยวข้อง

1. **Controller**: `app/Http/Controllers/GoogleSheetsImportController.php`
2. **Routes**: `routes/web.php`
3. **View**: `resources/views/teacher/dashboard.blade.php` (Modal Section)
4. **Documentation**: `GOOGLE_SHEETS_EXAMPLE.md`

## หมายเหตุ

- ระบบใช้ Transaction เพื่อความปลอดภัยของข้อมูล
- มีการ Log ข้อผิดพลาดใน Laravel Log
- รองรับ Mobile Responsive
- มี Loading States และ Error Handling ครบถ้วน
