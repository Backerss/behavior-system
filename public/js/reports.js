/**
 * สร้างรายงานพฤติกรรมประจำเดือน
 */
function generateMonthlyReport() {
    // แสดง Modal สำหรับเลือกเดือนและปี
    const modal = new bootstrap.Modal(document.getElementById('monthlyReportModal'));
    modal.show();
}

/**
 * สร้างรายงานสรุปนักเรียนที่มีความเสี่ยง
 */
function generateRiskStudentsReport() {
    // แสดง Modal สำหรับเลือกเดือนและปี
    const modal = new bootstrap.Modal(document.getElementById('riskStudentsReportModal'));
    modal.show();
}

/**
 * สร้างรายงานข้อมูลพฤติกรรมทั้งหมด
 */
function generateAllBehaviorDataReport() {
    // แสดง Modal สำหรับเลือกเดือนและปี
    const modal = new bootstrap.Modal(document.getElementById('allBehaviorDataReportModal'));
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

/**
 * ดาวน์โหลดรายงานสรุปนักเรียนที่มีความเสี่ยง
 */
function downloadRiskStudentsReport() {
    const month = document.getElementById('risk_report_month').value;
    const year = document.getElementById('risk_report_year').value;
    const classId = document.getElementById('risk_report_class_id').value;
    const riskLevel = document.getElementById('risk_report_level').value;
    
    // ตรวจสอบความถูกต้องของข้อมูล
    if (!month || !year) {
        alert('กรุณาเลือกเดือนและปีที่ต้องการสร้างรายงาน');
        return;
    }
    
    // สร้าง URL พร้อมพารามิเตอร์
    let url = `/reports/risk-students?month=${month}&year=${year}`;
    if (classId) {
        url += `&class_id=${classId}`;
    }
    if (riskLevel && riskLevel !== 'all') {
        url += `&risk_level=${riskLevel}`;
    }
    
    // เปิด URL ใหม่เพื่อดาวน์โหลด (หรือเปิดในแท็บใหม่)
    window.open(url, '_blank');
    
    // ปิด Modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('riskStudentsReportModal'));
    modal.hide();
}

/**
 * ดาวน์โหลดรายงานข้อมูลพฤติกรรมทั้งหมด
 */
function downloadAllBehaviorDataReport() {
    const month = document.getElementById('all_data_report_month').value;
    const year = document.getElementById('all_data_report_year').value;
    const classId = document.getElementById('all_data_report_class_id').value;
    
    // ตรวจสอบความถูกต้องของข้อมูล
    if (!month || !year) {
        alert('กรุณาเลือกเดือนและปีที่ต้องการสร้างรายงาน');
        return;
    }
    
    // สร้าง URL พร้อมพารามิเตอร์
    let url = `/reports/all-behavior-data?month=${month}&year=${year}`;
    if (classId) {
        url += `&class_id=${classId}`;
    }
    
    // เปิด URL ใหม่เพื่อดาวน์โหลด (หรือเปิดในแท็บใหม่)
    window.open(url, '_blank');
    
    // ปิด Modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('allBehaviorDataReportModal'));
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
    
    // ตั้งค่าเริ่มต้นสำหรับรายงานความเสี่ยง
    if (document.getElementById('risk_report_month')) {
        document.getElementById('risk_report_month').value = now.getMonth() + 1;
    }
    if (document.getElementById('risk_report_year')) {
        document.getElementById('risk_report_year').value = now.getFullYear();
    }
    
    // ตั้งค่าเริ่มต้นสำหรับรายงานข้อมูลพฤติกรรมทั้งหมด
    if (document.getElementById('all_data_report_month')) {
        document.getElementById('all_data_report_month').value = now.getMonth() + 1;
    }
    if (document.getElementById('all_data_report_year')) {
        document.getElementById('all_data_report_year').value = now.getFullYear();
    }
});