# ระบบเลือก Google Sheets Tabs 📊

## การปรับปรุงที่เพิ่มขึ้น ✨

### 1. **Multi-Sheet Support**
ตอนนี้ระบบรองรับการเลือก Google Sheets tabs ต่างๆ:

```php
const AVAILABLE_SHEETS = [
    'students' => [
        'name' => 'ข้อมูลนักเรียน',
        'gid' => '0', // Sheet แรก
        'description' => 'ข้อมูลนักเรียนทั้งหมด รวมรหัส ชื่อ อีเมล เบอร์โทร',
        'expected_columns' => ['รหัสนักเรียน', 'ชื่อจริง', 'นามสกุล', 'อีเมลล์', 'เบอร์โทรศัพท์'],
        'role' => 'student'
    ],
    'teachers' => [
        'name' => 'ข้อมูลครู',
        'gid' => '1234567890',
        'description' => 'ข้อมูลครูและบุคลากร',
        'expected_columns' => ['รหัสครู', 'ชื่อ', 'นามสกุล', 'อีเมล', 'วิชาที่สอน'],
        'role' => 'teacher'
    ],
    'guardians' => [
        'name' => 'ข้อมูลผู้ปกครอง',
        'gid' => '987654321',
        'description' => 'ข้อมูลผู้ปกครองและความสัมพันธ์กับนักเรียน',
        'expected_columns' => ['รหัสผู้ปกครอง', 'ชื่อ', 'นามสกุล', 'ความสัมพันธ์', 'รหัสนักเรียน'],
        'role' => 'guardian'
    ]
];
```

### 2. **Dynamic URL Generation**
URL จะถูกสร้างแบบ dynamic ตาม sheet ที่เลือก:

```php
// จาก: https://docs.google.com/spreadsheets/d/{id}/export?format=csv
// เป็น: https://docs.google.com/spreadsheets/d/{id}/export?format=csv&gid={sheet_gid}

$url = self::GOOGLE_SHEETS_BASE_URL . '/export?format=csv&gid=' . $sheetConfig['gid'];
```

### 3. **Interactive Sheet Selection UI**

#### Card-based Selection:
```html
<div class="card sheet-card active" data-sheet="students">
    <div class="card-body text-center">
        <h5 class="card-title">
            <i class="fas fa-file-alt text-primary"></i>
            ข้อมูลนักเรียน
        </h5>
        <p class="card-text text-muted">ข้อมูลนักเรียนทั้งหมด รวมรหัส ชื่อ อีเมล เบอร์โทร</p>
        <div class="mt-2">
            <span class="badge bg-secondary">student</span>
        </div>
    </div>
</div>
```

#### Visual Feedback:
- **Hover Effects**: เปลี่ยนสีและยกขึ้น
- **Active State**: เน้นด้วยสีฟ้าและเงา
- **Role Badges**: แสดงประเภทข้อมูล

### 4. **New API Endpoints**

#### GET `/admin/google-sheets/sheets`
```json
{
    "success": true,
    "sheets": {
        "students": {
            "name": "ข้อมูลนักเรียน",
            "gid": "0",
            "description": "ข้อมูลนักเรียนทั้งหมด รวมรหัส ชื่อ อีเมล เบอร์โทร",
            "expected_columns": ["รหัสนักเรียน", "ชื่อจริง", "นามสกุล", "อีเมลล์", "เบอร์โทรศัพท์"],
            "role": "student"
        }
    }
}
```

#### GET `/admin/google-sheets/preview?sheet=students`
```json
{
    "success": true,
    "data": {
        "valid_data": [...],
        "duplicate_data": [...],
        "error_data": [...]
    },
    "total_rows": 71
}
```

### 5. **Enhanced JavaScript Functionality**

#### Auto-load Sheets:
```javascript
$('#googleSheetsImportModal').on('show.bs.modal', function() {
    loadAvailableSheets();
});
```

#### Sheet Selection:
```javascript
function selectSheet(sheetType, sheetInfo) {
    selectedSheetType = sheetType;
    
    // Update visual selection
    $('.sheet-card').removeClass('active');
    $(`.sheet-card[data-sheet="${sheetType}"]`).addClass('active');
    
    // Show sheet info
    $('#sheetDescription').text(sheetInfo.description);
    $('#expectedColumns').text(sheetInfo.expected_columns.join(', '));
    $('#selectedSheetInfo').removeClass('d-none');
}
```

#### AJAX with Sheet Parameter:
```javascript
$.ajax({
    url: '{{ route("admin.google-sheets.preview") }}',
    method: 'GET',
    data: {
        sheet: selectedSheetType
    },
    success: function(response) {
        // Handle response
    }
});
```

## การใช้งาน 🎯

### 1. **เปิด Modal**
- กดปุ่ม "นำเข้าข้อมูลจาก Google Sheets"
- ระบบจะโหลดรายการ sheets อัตโนมัติ

### 2. **เลือก Sheet**
- เลือก card ของ sheet ที่ต้องการ
- ดูข้อมูลคอลัมน์ที่คาดหวัง
- card จะเปลี่ยนสี เมื่อถูกเลือก

### 3. **Preview ข้อมูล**
- กดปุ่ม "ดูตัวอย่างข้อมูล"
- ระบบจะดึงข้อมูลจาก sheet ที่เลือก
- แสดงผลแยกตามประเภท: ถูกต้อง/ซ้ำ/ผิดพลาด

### 4. **Import ข้อมูล**
- เลือกรายการที่ต้องการนำเข้า
- กดปุ่ม "นำเข้าข้อมูล"
- ระบบจะบันทึกลงฐานข้อมูล

## การตั้งค่า Google Sheets 📝

### หาค่า GID:
1. เปิด Google Sheets
2. คลิกที่ tab ของ sheet ที่ต้องการ
3. ดู URL จะมี `#gid=1234567890`
4. นำเลข `1234567890` มาใส่ใน config

### ตัวอย่าง URL:
```
https://docs.google.com/spreadsheets/d/1L3O0f5HdX_7cPw2jrQT4IaPsjw_jFD3O0aeH9ZQ499c/edit#gid=0
                                                                                               ^^^^
                                                                                               นี่คือ GID
```

## ไฟล์ที่ถูกลบ 🗑️

- ✅ `test_google_sheets.php` - ไฟล์ทดสอบ
- ✅ `routes/test.php` - route ทดสอบ
- ✅ test route ใน `web.php` - route ทดสอบใน web.php

## ข้อดีของระบบใหม่ 🌟

1. **ยืดหยุ่น**: เลือกได้หลาย sheets
2. **ชัดเจน**: แสดงข้อมูลแต่ละ sheet
3. **ปลอดภัย**: ตรวจสอบข้อมูลก่อนนำเข้า
4. **สวยงาม**: UI ที่ใช้งานง่าย
5. **เสถียร**: ไม่มีไฟล์ test หลงเหลือ

## ตัวอย่างการใช้งาน 📸

```
┌─────────────────────────────────────────────┐
│          เลือกแผ่นข้อมูลที่ต้องการนำเข้า           │
├─────────────────────────────────────────────┤
│  ┌─────────┐  ┌─────────┐  ┌─────────┐     │
│  │ 📄 นักเรียน │  │ 👨‍🏫 ครู │  │ 👨‍👩‍👧‍👦 ผปค │     │
│  │ [ACTIVE] │  │         │  │         │     │
│  └─────────┘  └─────────┘  └─────────┘     │
├─────────────────────────────────────────────┤
│ ℹ️ ข้อมูลนักเรียนทั้งหมด รวมรหัส ชื่อ อีเมล   │
│ 📋 คอลัมน์: รหัสนักเรียน, ชื่อจริง, นามสกุล   │
└─────────────────────────────────────────────┘
```

ตอนนี้ระบบสามารถเลือก Google Sheets tabs ต่างๆ ได้แล้ว! 🎉
