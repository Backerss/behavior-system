# 🎯 ระบบสารสนเทศจัดการคะแนนวินัยนักเรียน
## โรงเรียนนวมินทราชูทิศ มัชฌิม

<div align="center">

![School Badge](https://img.shields.io/badge/โรงเรียน-นวมินทราชูทิศ%20มัชฌิม-blue?style=for-the-badge)
![Version](https://img.shields.io/badge/เวอร์ชัน-2.0.0-success?style=for-the-badge)
![Status](https://img.shields.io/badge/สถานะ-พร้อมใช้งาน-green?style=for-the-badge)

</div>

---

## 📋 คำนำ

🏫 **ระบบสารสนเทศจัดการคะแนนวินัยนักเรียน** เป็นระบบที่พัฒนาขึ้นเพื่อช่วยในการจัดการและติดตามพฤติกรรมของนักเรียนในโรงเรียนนวมินทราชูทิศ มัชฌิม พร้อมด้วยระบบจัดการปีการศึกษาอัตโนมัติและการนำเข้าข้อมูลจาก Google Sheets

### 🎯 วัตถุประสงค์หลัก

- 📊 **เพิ่มประสิทธิภาพ** ในการจัดการข้อมูลพฤติกรรมนักเรียน
- 🔄 **ลดความซับซ้อน** ในกระบวนการบันทึกและติดตามพฤติกรรม
- 📱 **เพิ่มความสะดวก** ในการเข้าถึงข้อมูลสำหรับทุกกลุ่มผู้ใช้งาน
- 📈 **ส่งเสริมการวิเคราะห์** ข้อมูลเพื่อปรับปรุงพฤติกรรมนักเรียน
- 🤖 **อัตโนมัติการเลื่อนชั้น** และจัดการปีการศึกษา
- 📥 **นำเข้าข้อมูลจาก Google Sheets** อย่างมีประสิทธิภาพ

---

## 🚀 คุณสมบัติหลัก

### 👨‍🏫 สำหรับครู
- ✅ บันทึกพฤติกรรมนักเรียนแบบเรียลไทม์
- 📊 ดูสถิติและรายงานพฤติกรรมของนักเรียน
- 🏫 จัดการข้อมูลห้องเรียนและนักเรียน
- 📝 สร้างและแก้ไขประเภทพฤติกรรม
- 📋 ส่งออกรายงานเป็น PDF
- 📅 ติดตามปีการศึกษาและภาคเรียนปัจจุบัน
- 🔔 รับการแจ้งเตือนการเลื่อนชั้นและสิ้นสุดภาคเรียน

### 👨‍👩‍👧‍👦 สำหรับผู้ปกครอง
- 📱 ติดตามพฤติกรรมบุตรหลานผ่านระบบออนไลน์
- 📊 ดูคะแนนพฤติกรรมและแนวโน้มการเปลี่ยนแปลง
- 📬 รับการแจ้งเตือนเมื่อมีการบันทึกพฤติกรรม
- 💬 ติดต่อสื่อสารกับครูประจำชั้น

### 🎓 สำหรับนักเรียน
- 📈 ตรวจสอบคะแนนพฤติกรรมส่วนตัว
- 📊 ดูสถิติและประวัติพฤติกรรม
- 🏆 ติดตามอันดับในชั้นเรียน
- 📋 ดูกิจกรรมและข้อมูลส่วนตัว

### 👨‍💼 สำหรับผู้ดูแลระบบ (Admin)
- 📥 นำเข้าข้อมูลจาก Google Sheets (นักเรียน, ครู, ผู้ปกครอง)
- 🤖 จัดการการเลื่อนชั้นอัตโนมัติ
- ⚙️ ตั้งค่าปีการศึกษาและภาคเรียน
- 📊 ดูสถิติและรายงานภาพรวมของระบบ
- 🔧 จัดการผู้ใช้และสิทธิ์การเข้าถึง

---

## 🆕 ฟีเจอร์ใหม่

### 🤖 ระบบจัดการปีการศึกษาอัตโนมัติ
- **การเลื่อนชั้นอัตโนมัติ** ตามรหัสนักเรียนและปีการศึกษา
- **การแจ้งเตือนล่วงหน้า** ก่อนสิ้นสุดภาคเรียนและเริ่มภาคเรียนใหม่
- **Dashboard แบบ Real-time** แสดงข้อมูลปีการศึกษาปัจจุบัน

### 📥 ระบบนำเข้าข้อมูลจาก Google Sheets
- **Multi-Sheet Support** รองรับการเลือก tab ต่างๆ ใน Google Sheets
- **Preview และตรวจสอบข้อมูล** ก่อนนำเข้าจริง
- **ระบบการแจ้งเตือนข้อผิดพลาด** และข้อมูลซ้ำ
- **UI แบบ Modal** ใช้งานง่ายและสะดวก

### 📊 คำสั่งที่เพิ่มใหม่
```bash
# ตรวจสอบและจัดการปีการศึกษา
php artisan academic:check

# เลื่อนชั้นนักเรียน
php artisan students:promote --year=2568 --dry-run
```

---

## 🛠️ เทคโนโลยีที่ใช้งาน

### 🖥️ Backend (ฝั่งเซิร์ฟเวอร์)
- ![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white) **PHP 8.2+** - ภาษาโปรแกรมหลัก
- ![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel&logoColor=white) **Laravel 10.x** - Framework หลัก
- ![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white) **MySQL 8.0** - ฐานข้อมูล

### 🎨 Frontend (ฝั่งผู้ใช้)
- ![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white) **Bootstrap 5.3** - CSS Framework
- ![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat-square&logo=javascript&logoColor=black) **JavaScript ES6** - การโต้ตอบกับผู้ใช้
- ![jQuery](https://img.shields.io/badge/jQuery-3.6-0769AD?style=flat-square&logo=jquery&logoColor=white) **jQuery 3.6** - การจัดการ DOM

### 📊 การรายงานและการส่งออก
- ![PDF](https://img.shields.io/badge/mPDF-8.x-FF6B6B?style=flat-square) **mPDF** - สร้างไฟล์ PDF
- ![Charts](https://img.shields.io/badge/Chart.js-4.x-FF6384?style=flat-square&logo=chart.js&logoColor=white) **Chart.js** - กราฟและแผนภูมิ

### 🔗 การเชื่อมต่อภายนอก
- ![Google Sheets](https://img.shields.io/badge/Google%20Sheets-API-34A853?style=flat-square&logo=google-sheets&logoColor=white) **Google Sheets API** - นำเข้าข้อมูล
- ![cURL](https://img.shields.io/badge/cURL-HTTP-073551?style=flat-square) **cURL** - การติดต่อ HTTP

---

## 📦 การติดตั้งและการใช้งาน

### ✅ ความต้องการของระบบ
- PHP 8.2 หรือสูงกว่า
- MySQL 8.0 หรือสูงกว่า
- Composer 2.x
- Node.js 18+ (สำหรับการพัฒนา Frontend)

### 🚀 ขั้นตอนการติดตั้ง

1. **Clone โปรเจค**
```bash
git clone https://github.com/Backerss/behavior-system.git
cd behavior-system
```

2. **ติดตั้ง Dependencies**
```bash
composer install
npm install && npm run build
```

3. **ตั้งค่า Environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **ตั้งค่าฐานข้อมูล**
```bash
# แก้ไขไฟล์ .env ให้ตรงกับฐานข้อมูลของคุณ
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=behavior_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Migrate ฐานข้อมูล**
```bash
php artisan migrate
php artisan db:seed
```

6. **เริ่มใช้งาน**
```bash
php artisan serve
```

### ⚙️ การตั้งค่าเพิ่มเติม

#### การตั้งค่าปีการศึกษา
แก้ไขไฟล์ `.env`:
```bash
# ปีการศึกษาปัจจุบัน
ACADEMIC_YEAR=2568
CURRENT_SEMESTER=1

# การเลื่อนชั้นอัตโนมัติ
AUTO_PROMOTION_ENABLED=true
PROMOTION_MONTH=5
PROMOTION_DAY=16
```

#### การตั้งค่า Cron Job (ทางเลือก)
เพิ่มในไฟล์ crontab:
```bash
# ตรวจสอบการเลื่อนชั้นอัตโนมัติทุกวัน
0 6 * * * cd /path/to/project && php artisan academic:check
```

---

## 📚 เอกสารเพิ่มเติม

- 📖 **[ประวัติการพัฒนา](DEVELOPMENT_HISTORY.md)** - รายละเอียดการอัปเดตและแก้ไขระบบ
- 🎯 **[การใช้งาน Google Sheets](DEVELOPMENT_HISTORY.md#ระบบนำเข้าข้อมูลจาก-google-sheets)** - วิธีการเตรียมและนำเข้าข้อมูล
- 🤖 **[ระบบเลื่อนชั้นอัตโนมัติ](DEVELOPMENT_HISTORY.md#ระบบการจัดการปีการศึกษาและการเลื่อนชั้นอัตโนมัติ)** - คำสั่งและการตั้งค่า

---

## 🚀 การพัฒนาและการมีส่วนร่วม

หากต้องการพัฒนาหรือปรับปรุงระบบ:

1. **Fork โปรเจค**
2. **สร้าง Branch ใหม่** (`git checkout -b feature/new-feature`)
3. **Commit การเปลี่ยนแปลง** (`git commit -am 'Add new feature'`)
4. **Push ไปยัง Branch** (`git push origin feature/new-feature`)
5. **สร้าง Pull Request**

---

## 📞 การติดต่อและสนับสนุน

🏫 **โรงเรียนนวมินทราชูทิศ มัชฌิม**  
📧 **อีเมล**: support@school.ac.th  
📱 **โทรศัพท์**: 02-XXX-XXXX  

---

## 📄 ใบอนุญาต

โปรเจคนี้ได้รับการพัฒนาเพื่อใช้งานภายในโรงเรียนนวมินทราชูทิศ มัชฌิม

---

<div align="center">

**💙 พัฒนาด้วยความใส่ใจสำหรับการศึกษาไทย 💙**

![Built with Love](https://img.shields.io/badge/Built%20with-❤️-red?style=for-the-badge)

</div>
| ![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel&logoColor=white) | 10.x | Framework สำหรับพัฒนาเว็บแอปพลิเคชัน |
| ![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white) | 8.0+ | ระบบจัดการฐานข้อมูล |

</div>

### 🎨 Frontend (ฝั่งผู้ใช้งาน)
<div align="center">

| เทคโนโลยี | เวอร์ชัน | วัตถุประสงค์ |
|-----------|---------|-------------|
| ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white) | 5 | โครงสร้างหน้าเว็บ |
| ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white) | 3 | จัดรูปแบบและการแสดงผล |
| ![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black) | ES6+ | การทำงานแบบโต้ตอบ |
| ![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white) | 5.3 | Framework CSS สำหรับ Responsive Design |
| ![Chart.js](https://img.shields.io/badge/Chart.js-4.0-FF6384?style=flat-square&logo=chart.js&logoColor=white) | 4.0+ | สร้างกราฟและแผนภูมิ |

</div>

### 🔧 เครื่องมือเสริม
<div align="center">

| เทคโนโลยี | วัตถุประสงค์ |
|-----------|-------------|
| ![Composer](https://img.shields.io/badge/Composer-885630?style=flat-square&logo=composer&logoColor=white) | จัดการ Dependencies ของ PHP |
| ![NPM](https://img.shields.io/badge/NPM-CB3837?style=flat-square&logo=npm&logoColor=white) | จัดการ Package ของ JavaScript |
| ![mPDF](https://img.shields.io/badge/mPDF-PDF%20Generator-blue?style=flat-square) | สร้างไฟล์ PDF |
| ![Blade](https://img.shields.io/badge/Blade-Template%20Engine-red?style=flat-square) | Template Engine ของ Laravel |

</div>

---

## 📁 โครงสร้างโปรเจค

