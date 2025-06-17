/**
 * สร้างรายงานพฤติกรรมประจำเดือน
 */
function generateMonthlyReport() {
    // แสดง Modal สำหรับเลือกเดือนและปี
    const modal = new bootstrap.Modal(document.getElementById('monthlyReportModal'));
    modal.show();
}

/**
 * ดาวน์โหลดรายงานพฤติกรรมประจำเดือน
 */
function downloadMonthlyReport() {
    const month = document.getElementById('report_month').value;
    const year = document.getElementById('report_year').value;
    const classId = document.getElementById('report_class_id').value;
    
    // ตรวจสอบความถูกต้องของข้อมูล
    if (!month || !year) {
        alert('กรุณาเลือกเดือนและปีที่ต้องการสร้างรายงาน');
        return;
    }
    
    // สร้าง URL พร้อมพารามิเตอร์
    let url = `/reports/monthly?month=${month}&year=${year}`;
    if (classId) {
        url += `&class_id=${classId}`;
    }
    
    // เปิด URL ใหม่เพื่อดาวน์โหลด (หรือเปิดในแท็บใหม่)
    window.open(url, '_blank');
    
    // ปิด Modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('monthlyReportModal'));
    modal.hide();
}

// เตรียม Modal เมื่อ Document โหลดเสร็จ
document.addEventListener('DOMContentLoaded', function() {
    // ตั้งค่าเริ่มต้นสำหรับเดือนและปี
    const now = new Date();
    if (document.getElementById('report_month')) {
        document.getElementById('report_month').value = now.getMonth() + 1; // JavaScript เดือน 0-11
    }
    if (document.getElementById('report_year')) {
        document.getElementById('report_year').value = now.getFullYear();
    }
});