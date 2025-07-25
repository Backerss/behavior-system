# ตัวอย่างข้อมูลสำหรับ Google Sheets

## คอลัมน์ที่ต้องมีใน Google Sheets:

| prefix | first_name | last_name | email | phone | role | student_id | classroom | gender | date_of_birth | employee_code | position | department | major | relationship | line_id |
|--------|------------|-----------|-------|-------|------|------------|-----------|---------|---------------|---------------|----------|------------|-------|--------------|---------|
| นาย | สมชาย | ใจดี | somchai@example.com | 0812345678 | student | S001 | ป.1/1 | male | 2010-05-15 | | | | | | |
| นางสาว | สมหญิง | รักเรียน | somying@example.com | 0823456789 | student | S002 | ป.1/1 | female | 2010-08-20 | | | | | | |
| ครู | วิภาพร | สอนดี | wipaporn@example.com | 0834567890 | teacher | | | female | 1985-03-10 | T001 | ครูชำนาญการ | วิทยาศาสตร์ | คณิตศาสตร์ | | |
| นาย | สมพร | ดูแลดี | somporn@example.com | 0845678901 | guardian | | | male | 1975-12-25 | | | | | พ่อ | somporn_line |

## คำอธิบายคอลัมน์:

### คอลัมน์พื้นฐาน (ทุก role ต้องมี):
- **prefix**: คำนำหน้าชื่อ (นาย, นาง, นางสาว, เด็กชาย, เด็กหญิง, ครู, อาจารย์)
- **first_name**: ชื่อจริง (จำเป็น)
- **last_name**: นามสกุล (จำเป็น)
- **email**: อีเมล (จำเป็น และไม่ซ้ำ)
- **phone**: เบอร์โทรศัพท์
- **role**: บทบาท (admin, teacher, student, guardian) (จำเป็น)

### คอลัมน์สำหรับ student:
- **student_id**: รหัสนักเรียน (จำเป็นสำหรับ student และไม่ซ้ำ)
- **classroom**: ห้องเรียน (เช่น ป.1/1, ม.2/3)
- **gender**: เพศ (male, female, other)
- **date_of_birth**: วันเกิด (YYYY-MM-DD)

### คอลัมน์สำหรับ teacher:
- **employee_code**: รหัสพนักงาน
- **position**: ตำแหน่ง (เช่น ครู, ครูชำนาญการ, หัวหน้าฝ่าย)
- **department**: ฝ่าย/แผนก
- **major**: วิชาเอก

### คอลัมน์สำหรับ guardian:
- **relationship**: ความสัมพันธ์กับนักเรียน (พ่อ, แม่, ปู่, ย่า, ผู้ปกครอง)
- **line_id**: Line ID สำหรับการติดต่อ

## หมายเหตุ:
1. คอลัมน์ที่ไม่เกี่ยวข้องกับ role นั้น ๆ สามารถปล่อยว่างได้
2. รหัสผ่านจะถูกตั้งเป็น "123456789" สำหรับทุกบัญชีใหม่
3. ข้อมูลที่ซ้ำ (email, student_id) จะถูกแจ้งเตือนในหน้า Preview
4. ข้อมูลที่มีข้อผิดพลาดจะถูกแยกออกมาแสดงใน Tab "ข้อมูลผิดพลาด"

## ลิงก์ Google Sheets ตัวอย่าง:
https://docs.google.com/spreadsheets/d/1L3O0f5HdX_7cPw2jrQT4IaPsjw_jFD3O0aeH9ZQ499c/edit#gid=0
