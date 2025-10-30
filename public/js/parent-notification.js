/**
 * ระบบแจ้งเตือนผู้ปกครอง
 */

// ข้อความแนะนำสำหรับแต่ละประเภทการแจ้งเตือน
const notificationTemplates = {
    behavior: {
        normal: "เรียนท่านผู้ปกครอง นักเรียน [ชื่อนักเรียน] มีพฤติกรรมที่น่าเป็นห่วง คะแนนความประพฤติปัจจุบัน [คะแนน]/100 ขอความกรุณาช่วยกำกับดูแลและติดต่อกลับครูประจำชั้น",
        critical: "เรียนท่านผู้ปกครอง นักเรียน [ชื่อนักเรียน] มีพฤติกรรมที่น่ากังวลอย่างมาก คะแนนความประพฤติปัจจุบันต่ำมาก ([คะแนน]/100) ขอความกรุณาติดต่อครูประจำชั้นโดยด่วน"
    },
    attendance: {
        normal: "เรียนท่านผู้ปกครอง นักเรียน [ชื่อนักเรียน] ขาดเรียนบ่อยครั้งในช่วงที่ผ่านมา ขอความกรุณาติดต่อกลับครูประจำชั้น",
        critical: "เรียนท่านผู้ปกครอง นักเรียน [ชื่อนักเรียน] ขาดเรียนติดต่อกันหลายวันโดยไม่มีการแจ้ง ขอความกรุณาติดต่อครูประจำชั้นโดยด่วน"
    },
    meeting: {
        normal: "เรียนท่านผู้ปกครอง ทางโรงเรียนขอเชิญท่านเข้าพบครูประจำชั้นในวันที่ [วันที่] เวลา [เวลา] เพื่อปรึกษาเกี่ยวกับการเรียนของนักเรียน [ชื่อนักเรียน]",
        critical: "เรียนท่านผู้ปกครอง ทางโรงเรียนขอเชิญท่านเข้าพบครูประจำชั้นโดยด่วนในวันที่ [วันที่] เวลา [เวลา] เพื่อปรึกษาเกี่ยวกับพฤติกรรมที่น่ากังวลของนักเรียน [ชื่อนักเรียน]"
    },
    custom: {
        normal: "",
        critical: ""
    }
};

// ข้อมูลการแจ้งเตือนปัจจุบัน
let currentNotification = {
    studentId: null,
    studentName: '',
    classroom: '',
    score: 0,
    phone: '',
    isCritical: false
};

/**
 * เปิด Modal แจ้งเตือนผู้ปกครอง
 */
function openParentNotificationModal(studentId, studentName, classroom, score, phone) {
    // บันทึกข้อมูล
    currentNotification = {
        studentId,
        studentName,
        classroom,
        score,
        phone,
        isCritical: score < 40
    };
    
    // ตั้งค่า fields ใน form
    document.getElementById('notification-student-id').value = studentId;
    document.getElementById('notification-score').value = score;
    document.getElementById('notification-phone').value = phone;
    // UI สำหรับ SMS/LINE ถูกนำออก หาก element ไม่อยู่ ให้ข้ามได้อย่างปลอดภัย
    const phoneDisplay = document.getElementById('notification-phone-display');
    if (phoneDisplay) {
        phoneDisplay.textContent = formatPhoneNumber(phone);
    }
    
    // แสดงข้อมูลนักเรียน
    document.getElementById('notification-student-info').innerHTML = `
        <div class="d-flex align-items-center">
            <div>
                <strong>${studentName}</strong><br>
                <span class="text-muted">ชั้น ${classroom} | คะแนนความประพฤติ: 
                    <span class="${score < 60 ? 'text-danger' : score < 75 ? 'text-warning' : 'text-success'}">
                        ${score}/100
                    </span>
                </span>
            </div>
        </div>
    `;
    
    // แสดง/ซ่อนการแจ้งเตือนสำหรับนักเรียนที่คะแนนต่ำมาก
    if (score < 40) {
        document.getElementById('notification-warning').classList.remove('d-none');
    } else {
        document.getElementById('notification-warning').classList.add('d-none');
    }
    
    // ซ่อนข้อความสำเร็จ/ผิดพลาด
    document.getElementById('notification-success').classList.add('d-none');
    document.getElementById('notification-error').classList.add('d-none');
    
    // ตั้งค่าข้อความเริ่มต้น
    updateNotificationTemplate();
    
    // แสดง Modal
    const modal = new bootstrap.Modal(document.getElementById('parentNotificationModal'));
    modal.show();
}

/**
 * อัพเดตข้อความตามเทมเพลต
 */
function updateNotificationTemplate() {
    const type = document.getElementById('notification-type').value;
    const isCritical = currentNotification.isCritical;
    const templateKey = isCritical ? 'critical' : 'normal';
    
    let template = notificationTemplates[type][templateKey];
    
    // ถ้าเป็นประเภท custom ไม่ต้องใส่ข้อความอัตโนมัติ
    if (type === 'custom') {
        // แสดงปุ่มแนะนำข้อความ
        document.getElementById('message-suggestion').classList.remove('d-none');
        return;
    }
    
    // แทนที่ตัวแปรในเทมเพลต
    let message = template
        .replace('[ชื่อนักเรียน]', currentNotification.studentName)
        .replace('[คะแนน]', currentNotification.score)
        .replace('[วันที่]', getTomorrowDateString())
        .replace('[เวลา]', '15:30 น.');
    
    // ตั้งค่าข้อความ
    document.getElementById('notification-message').value = message;
    
    // ซ่อนปุ่มแนะนำข้อความ
    document.getElementById('message-suggestion').classList.add('d-none');
}

/**
 * ใช้ข้อความแนะนำ
 */
function applyMessageSuggestion() {
    const type = document.getElementById('notification-type').value;
    const isCritical = currentNotification.isCritical;
    const templateKey = isCritical ? 'critical' : 'normal';
    
    // ใช้เทมเพลตของประเภท behavior แทน
    let template = notificationTemplates['behavior'][templateKey];
    
    // แทนที่ตัวแปรในเทมเพลต
    let message = template
        .replace('[ชื่อนักเรียน]', currentNotification.studentName)
        .replace('[คะแนน]', currentNotification.score)
        .replace('[วันที่]', getTomorrowDateString())
        .replace('[เวลา]', '15:30 น.');
    
    // ตั้งค่าข้อความ
    document.getElementById('notification-message').value = message;
    
    // ซ่อนปุ่มแนะนำข้อความ
    document.getElementById('message-suggestion').classList.add('d-none');
}

/**
 * ส่งการแจ้งเตือนไปยังผู้ปกครอง
 */
function sendParentNotification() {
    // ตรวจสอบว่ามีข้อความ
    const message = document.getElementById('notification-message').value.trim();
    if (!message) {
        alert('กรุณากรอกข้อความที่ต้องการส่ง');
        return;
    }
    
    // ใช้เฉพาะการแจ้งเตือนภายในระบบเท่านั้น
    const systemChecked = document.getElementById('notification-system')?.checked ?? true;
    
    // แสดงสถานะกำลังส่ง
    const sendButton = document.getElementById('send-notification-btn');
    const originalText = sendButton.innerHTML;
    sendButton.disabled = true;
    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> กำลังส่ง...';
    
    // ซ่อนข้อความสำเร็จ/ผิดพลาด
    document.getElementById('notification-success').classList.add('d-none');
    document.getElementById('notification-error').classList.add('d-none');
    
    // ข้อมูลสำหรับส่งไป API
    const data = {
        student_id: parseInt(currentNotification.studentId),
        message: message,
        channels: {
            system: systemChecked
        },
        phone: currentNotification.phone,
        score: parseFloat(currentNotification.score),
        notification_type: document.getElementById('notification-type').value
    };
    
    // ดึง CSRF token จาก meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // ส่งข้อมูล
    fetch('/notifications/parent', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data),
        credentials: 'same-origin'
    })
    .then(async response => {
        // อ่าน response body
        const responseText = await response.text();
        
        if (!response.ok) {
            // พยายาม parse JSON ถ้าเป็น JSON
            let errorData;
            try {
                errorData = JSON.parse(responseText);
            } catch (e) {
                // ถ้าไม่ใช่ JSON ให้ใช้ text
                throw new Error(`Server Error (${response.status}): ${responseText}`);
            }
            
            if (response.status === 422) {
                throw new Error(errorData.message || 'ข้อมูลไม่ถูกต้อง');
            } else if (response.status === 500) {
                throw new Error(`เกิดข้อผิดพลาดในเซิร์ฟเวอร์: ${errorData.message || errorData.error || 'Unknown error'}`);
            } else {
                throw new Error(errorData.message || `HTTP Error ${response.status}`);
            }
        }
        
        // Parse JSON response
        let jsonData;
        try {
            jsonData = JSON.parse(responseText);
        } catch (e) {
            throw new Error('Server returned invalid JSON response');
        }
        
        return jsonData;
    })
    .then(data => {
        if (data.success) {
            // แสดงข้อความสำเร็จ
            document.getElementById('notification-success').classList.remove('d-none');
            
            // รีเซ็ตฟอร์ม
            document.getElementById('notification-form').reset();
            
            // ปิด Modal หลังจาก 2 วินาที
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('parentNotificationModal'));
                if (modal) {
                    modal.hide();
                }
            }, 2000);
        } else {
            throw new Error(data.message || 'เกิดข้อผิดพลาดในการส่งการแจ้งเตือน');
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        
        // แสดงข้อความผิดพลาด
        document.getElementById('notification-error').classList.remove('d-none');
        document.getElementById('notification-error-message').textContent = error.message;
    })
    .finally(() => {
        // คืนสถานะปุ่ม
        sendButton.disabled = false;
        sendButton.innerHTML = originalText;
    });
}

/**
 * ฟังก์ชันช่วยจัดรูปแบบเบอร์โทรศัพท์
 */
function formatPhoneNumber(phone) {
    if (!phone || phone === '-') return '-';
    
    // เฉพาะตัวเลข
    const digits = phone.replace(/\D/g, '');
    
    // ตรวจสอบความยาว
    if (digits.length === 10) {
        return digits.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
    }
    
    return phone;
}

/**
 * ฟังก์ชันช่วยรับวันพรุ่งนี้
 */
function getTomorrowDateString() {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    // รูปแบบไทย: วันที่/เดือน/ปี พ.ศ.
    const day = tomorrow.getDate();
    const month = tomorrow.getMonth() + 1;
    const year = tomorrow.getFullYear() + 543; // แปลงเป็น พ.ศ.
    
    return `${day}/${month}/${year}`;
}