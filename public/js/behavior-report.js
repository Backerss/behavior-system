/**
 * JavaScript สำหรับจัดการการบันทึกพฤติกรรม
 */

// ตัวแปรสำหรับเก็บข้อมูล
const behaviorReport = {
    selectedStudent: null,
    violations: [],
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
};

// เริ่มต้นระบบเมื่อ DOM โหลดเสร็จ
document.addEventListener('DOMContentLoaded', function() {
    initializeBehaviorReport();
});

/**
 * เริ่มต้นระบบบันทึกพฤติกรรม
 */
function initializeBehaviorReport() {
    setupEventListeners();
    loadViolationTypes();
    setDefaultDateTime();
    loadRecentReports();
}

/**
 * ตั้งค่า Event Listeners
 */
function setupEventListeners() {
    const studentSearch = document.getElementById('behaviorStudentSearch');
    const classFilter = document.getElementById('classFilter');
    const violationType = document.getElementById('violationType');
    const saveBtn = document.getElementById('saveViolationBtn');
    const violationForm = document.getElementById('violationForm');
    
    // ค้นหานักเรียน
    if (studentSearch) {
        let searchTimeout;
        studentSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2) {
                    searchStudents(this.value);
                } else {
                    hideStudentResults();
                }
            }, 300);
        });
    }
    
    // กรองตามห้อง
    if (classFilter) {
        classFilter.addEventListener('change', function() {
            const searchInput = document.getElementById('behaviorStudentSearch');
            if (searchInput && searchInput.value.length >= 2) {
                searchStudents(searchInput.value);
            }
        });
    }
    
    // เปลี่ยนประเภทพฤติกรรม
    if (violationType) {
        violationType.addEventListener('change', function() {
            updatePointsDeducted(this.value);
        });
    }
    
    // บันทึกข้อมูล
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            saveBehaviorReport();
        });
    }
    
    // รีเซ็ตฟอร์มเมื่อปิด modal
    const modal = document.getElementById('newViolationModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            resetForm();
        });
    }
}

/**
 * ซ่อนผลการค้นหานักเรียน
 */
function hideStudentResults() {
    const resultsContainer = document.getElementById('studentResults');
    if (resultsContainer) {
        resultsContainer.style.display = 'none';
    }
}

/**
 * ค้นหานักเรียน
 */
function searchStudents(searchTerm) {
    const classFilter = document.getElementById('classFilter');
    const classId = classFilter ? classFilter.value : '';
    const resultsContainer = document.getElementById('studentResults');
    
    if (!resultsContainer) return;
    
    if (!searchTerm || searchTerm.length < 2) {
        resultsContainer.style.display = 'none';
        return;
    }
    
    // แสดง loading
    resultsContainer.innerHTML = '<div class="list-group-item">กำลังค้นหา...</div>';
    resultsContainer.style.display = 'block';
    
    fetch(`/api/behavior-reports/students/search?term=${encodeURIComponent(searchTerm)}&class_id=${classId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayStudentResults(data.data);
            } else {
                resultsContainer.innerHTML = '<div class="list-group-item text-danger">เกิดข้อผิดพลาดในการค้นหา</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultsContainer.innerHTML = '<div class="list-group-item text-danger">เกิดข้อผิดพลาดในการเชื่อมต่อ</div>';
        });
}

/**
 * แสดงผลการค้นหานักเรียน
 */
function displayStudentResults(students) {
    const resultsContainer = document.getElementById('studentResults');
    
    if (!resultsContainer) return;
    
    if (students.length === 0) {
        resultsContainer.innerHTML = '<div class="list-group-item text-muted">ไม่พบนักเรียนที่ตรงกับคำค้นหา</div>';
        return;
    }
    
    resultsContainer.innerHTML = '';
    
    students.forEach(student => {
        const item = document.createElement('div');
        item.className = 'list-group-item list-group-item-action';
        item.style.cursor = 'pointer';
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">${student.name}</h6>
                    <small class="text-muted">รหัส: ${student.student_id} | ห้อง: ${student.class}</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary">${student.current_score} คะแนน</span>
                </div>
            </div>
        `;
        
        item.addEventListener('click', function() {
            selectStudent(student);
        });
        
        resultsContainer.appendChild(item);
    });
}

/**
 * เลือกนักเรียน
 */
function selectStudent(student) {
    behaviorReport.selectedStudent = student;
    
    // ซ่อนผลการค้นหา
    hideStudentResults();
    
    // แสดงข้อมูลนักเรียนที่เลือก
    const selectedInfo = document.getElementById('selectedStudentInfo');
    const infoDisplay = document.getElementById('studentInfoDisplay');
    
    if (selectedInfo && infoDisplay) {
        infoDisplay.innerHTML = `
            <strong>${student.name}</strong> 
            (รหัส: ${student.student_id}) 
            ห้อง: ${student.class} 
            คะแนนปัจจุบัน: <span class="badge bg-primary">${student.current_score}</span>
        `;
        
        selectedInfo.style.display = 'block';
    }
    
    // ตั้งค่า hidden input
    const selectedStudentId = document.getElementById('selectedStudentId');
    if (selectedStudentId) {
        selectedStudentId.value = student.id;
    }
    
    // เคลียร์ช่องค้นหา
    const searchInput = document.getElementById('behaviorStudentSearch');
    if (searchInput) {
        searchInput.value = student.name;
    }
}

/**
 * โหลดประเภทพฤติกรรม
 */
function loadViolationTypes() {
    fetch('/api/violations/all')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                behaviorReport.violations = data.data;
                updateViolationSelect(data.data);
            }
        })
        .catch(error => {
            console.error('Error loading violations:', error);
        });
}

/**
 * อัปเดต select ประเภทพฤติกรรม
 */
function updateViolationSelect(violations) {
    const select = document.getElementById('violationType');
    
    if (!select) return;
    
    // เคลียร์ตัวเลือกเดิม
    while (select.options.length > 1) {
        select.remove(1);
    }
    
    violations.forEach(violation => {
        const option = new Option(violation.violations_name, violation.violations_id);
        option.dataset.points = violation.violations_points_deducted;
        select.add(option);
    });
}

/**
 * อัปเดตคะแนนที่หัก
 */
function updatePointsDeducted(violationId) {
    const pointsInput = document.getElementById('pointsDeducted');
    const select = document.getElementById('violationType');
    
    if (!pointsInput || !select) return;
    
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption && selectedOption.dataset.points) {
        pointsInput.value = selectedOption.dataset.points;
    } else {
        pointsInput.value = 0;
    }
}

/**
 * ตั้งค่าวันที่และเวลาเริ่มต้น
 */
function setDefaultDateTime() {
    const now = new Date();
    const dateInput = document.getElementById('violationDate');
    const timeInput = document.getElementById('violationTime');
    
    if (dateInput) {
        dateInput.value = now.toISOString().split('T')[0];
        dateInput.max = now.toISOString().split('T')[0];
    }
    
    if (timeInput) {
        timeInput.value = now.toTimeString().slice(0, 5);
    }
}

/**
 * บันทึกรายงานพฤติกรรม
 */
function saveBehaviorReport() {
    if (!validateForm()) {
        return;
    }
    
    const saveBtn = document.getElementById('saveViolationBtn');
    if (!saveBtn) return;
    
    const originalText = saveBtn.innerHTML;
    
    // แสดง loading
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> กำลังบันทึก...';
    
    // เตรียมข้อมูล
    const formData = new FormData();
    formData.append('student_id', document.getElementById('selectedStudentId')?.value || '');
    formData.append('violation_id', document.getElementById('violationType')?.value || '');
    formData.append('violation_date', document.getElementById('violationDate')?.value || '');
    formData.append('violation_time', document.getElementById('violationTime')?.value || '');
    formData.append('description', document.getElementById('violationDescription')?.value || '');
    
    // รวมวันที่และเวลา
    const date = document.getElementById('violationDate')?.value;
    const time = document.getElementById('violationTime')?.value;
    if (date && time) {
        formData.append('violation_datetime', `${date} ${time}`);
    }
    
    // แนบไฟล์หลักฐาน
    const evidenceFile = document.getElementById('evidenceFile')?.files[0];
    if (evidenceFile) {
        formData.append('evidence', evidenceFile);
    }
    
    // ส่งข้อมูล
    fetch('/api/behavior-reports', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': behaviorReport.csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSuccess('บันทึกพฤติกรรมเรียบร้อยแล้ว');
            resetForm();
            loadRecentReports();
            // ปิด modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('newViolationModal'));
            if (modal) {
                modal.hide();
            }
        } else {
            showError(data.message || 'เกิดข้อผิดพลาดในการบันทึก');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
    })
    .finally(() => {
        // คืนสถานะปุ่ม
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

/**
 * ตรวจสอบความถูกต้องของฟอร์ม
 */
function validateForm() {
    let isValid = true;
    const errors = [];
    
    // ตรวจสอบการเลือกนักเรียน
    if (!document.getElementById('selectedStudentId')?.value) {
        errors.push('กรุณาเลือกนักเรียน');
        isValid = false;
    }
    
    // ตรวจสอบประเภทพฤติกรรม
    if (!document.getElementById('violationType')?.value) {
        errors.push('กรุณาเลือกประเภทพฤติกรรม');
        isValid = false;
    }
    
    // ตรวจสอบวันที่
    if (!document.getElementById('violationDate')?.value) {
        errors.push('กรุณาระบุวันที่เกิดเหตุการณ์');
        isValid = false;
    }
    
    // ตรวจสอบเวลา
    if (!document.getElementById('violationTime')?.value) {
        errors.push('กรุณาระบุเวลาที่เกิดเหตุการณ์');
        isValid = false;
    }
    
    if (!isValid) {
        showError(errors.join('<br>'));
    }
    
    return isValid;
}

/**
 * รีเซ็ตฟอร์ม
 */
function resetForm() {
    const form = document.getElementById('violationForm');
    if (form) {
        form.reset();
    }
    
    const selectedStudentId = document.getElementById('selectedStudentId');
    if (selectedStudentId) {
        selectedStudentId.value = '';
    }
    
    const pointsDeducted = document.getElementById('pointsDeducted');
    if (pointsDeducted) {
        pointsDeducted.value = '0';
    }
    
    const selectedStudentInfo = document.getElementById('selectedStudentInfo');
    if (selectedStudentInfo) {
        selectedStudentInfo.style.display = 'none';
    }
    
    hideStudentResults();
    behaviorReport.selectedStudent = null;
    setDefaultDateTime();
}

/**
 * โหลดรายงานล่าสุด
 */
function loadRecentReports() {
    const tableBody = document.getElementById('recentViolationsTable');
    
    if (!tableBody) return;
    
    // แสดง loading
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                <p class="text-muted">กำลังโหลดข้อมูล...</p>
            </td>
        </tr>
    `;
    
    fetch('/api/behavior-reports/recent?limit=10')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayRecentReports(data.data);
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            เกิดข้อผิดพลาดในการโหลดข้อมูล
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        เกิดข้อผิดพลาดในการเชื่อมต่อ
                    </td>
                </tr>
            `;
        });
}

/**
 * แสดงรายงานล่าสุด
 */
function displayRecentReports(reports) {
    const tableBody = document.getElementById('recentViolationsTable');
    
    if (!tableBody) return;
    
    if (reports.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>ไม่มีข้อมูลการบันทึกพฤติกรรม</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tableBody.innerHTML = '';
    
    reports.forEach(report => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${report.student_name}</td>
            <td>
                <span class="badge bg-danger">${report.violation_name}</span>
            </td>
            <td>${report.points_deducted} คะแนน</td>
            <td>${report.created_at}</td>
            <td>${report.teacher_name}</td>
            <td>
                <button class="btn btn-sm btn-primary-app view-violation-btn" data-id="${report.id}" data-bs-toggle="modal" data-bs-target="#violationDetailModal">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary edit-violation-btn" data-id="${report.id}">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

/**
 * แสดงข้อความสำเร็จ
 */
function showSuccess(message) {
    createToast(message, 'success');
}

/**
 * แสดงข้อความผิดพลาด
 */
function showError(message) {
    createToast(message, 'error');
}

/**
 * สร้าง toast notification
 */
function createToast(message, type = 'success') {
    // สร้าง toast container ถ้ายังไม่มี
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.classList.add('toast-container', 'position-fixed', 'top-0', 'end-0', 'p-3');
        toastContainer.style.zIndex = '1070';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = `${type}-toast-${Date.now()}`;
    const isSuccess = type === 'success';
    const bgColor = isSuccess ? 'bg-success' : 'bg-danger';
    const icon = isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle';
    const title = isSuccess ? 'สำเร็จ' : 'ข้อผิดพลาด';
    
    const toastHTML = `
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
            <div class="toast-header ${bgColor} text-white">
                <i class="fas ${icon} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <small>เมื่อสักครู่</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastEl = document.getElementById(toastId);
    if (toastEl && window.bootstrap) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        
        // ลบ toast หลังจากแสดง
        toastEl.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }
}