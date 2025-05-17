// Set current date
document.addEventListener('DOMContentLoaded', function() {
    // Set current date in Thai format
    const today = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const thaiDate = today.toLocaleDateString('th-TH', options);
    document.querySelector('.current-date').textContent = thaiDate;
    
    // Initialize charts
    initViolationTrendChart();
    initViolationTypesChart();
    
    // Mobile navigation active state
    const navLinks = document.querySelectorAll('.bottom-navbar .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(item => {
                item.classList.remove('text-primary-app');
            });
            this.classList.add('text-primary-app');
        });
    });
    
    // Sidebar navigation active state
    const menuItems = document.querySelectorAll('.sidebar-menu .menu-item');
    menuItems.forEach(item => {
        if (!item.hasAttribute('data-bs-toggle')) {
            item.addEventListener('click', function(e) {
                menuItems.forEach(menuItem => {
                    menuItem.classList.remove('active');
                });
                this.classList.add('active');
            });
        }
    });
    
    // Student search in violation modal
    const studentSearchInput = document.querySelector('#newViolationModal input[placeholder="พิมพ์ชื่อหรือรหัสนักเรียน..."]');
    const selectedStudentContainer = document.querySelector('.selected-student');
    
    if (studentSearchInput) {
        studentSearchInput.addEventListener('keyup', function(e) {
            // Simulating search - in a real app, this would be an AJAX call
            if (e.key === 'Enter' && this.value.trim() !== '') {
                // Show selected student (this is just a mockup)
                selectedStudentContainer.classList.remove('d-none');
                this.value = '';
            }
        });
        
        // Remove selected student
        const removeStudentBtn = document.querySelector('.selected-student .btn-close');
        if (removeStudentBtn) {
            removeStudentBtn.addEventListener('click', function() {
                selectedStudentContainer.classList.add('d-none');
            });
        }
    }
    
    // Date restriction for violation date (max 3 days in the past)
    const dateInput = document.querySelector('#violationDate');
    if (dateInput) {
        const today = new Date();
        const threeDaysAgo = new Date();
        threeDaysAgo.setDate(today.getDate() - 3);
        
        dateInput.valueAsDate = today;
        dateInput.min = threeDaysAgo.toISOString().split('T')[0];
        dateInput.max = today.toISOString().split('T')[0];
    }
    
    // Initialize popovers and tooltips if using them
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // สำหรับแสดงฟอร์มเพิ่มประเภทพฤติกรรมใหม่
    const btnShowAddViolationType = document.getElementById('btnShowAddViolationType');
    const violationTypesList = document.getElementById('violationTypesList');
    const violationTypeForm = document.getElementById('violationTypeForm');
    const btnCloseViolationForm = document.getElementById('btnCloseViolationForm');
    const btnCancelViolationType = document.getElementById('btnCancelViolationType');
    const formViolationTitle = document.getElementById('formViolationTitle');
    const formViolationType = document.getElementById('formViolationType');
    
    // ปุ่มแสดงฟอร์มเพิ่มใหม่
    if (btnShowAddViolationType) {
        btnShowAddViolationType.addEventListener('click', function() {
            // รีเซ็ตฟอร์ม
            formViolationType.reset();
            document.getElementById('violationTypeId').value = '';
            formViolationTitle.textContent = 'เพิ่มประเภทพฤติกรรมใหม่';
            
            // แสดงฟอร์ม ซ่อนรายการ
            violationTypesList.classList.add('d-none');
            violationTypeForm.classList.remove('d-none');
        });
    }
    
    // ปุ่มปิดฟอร์ม
    if (btnCloseViolationForm) {
        btnCloseViolationForm.addEventListener('click', function() {
            violationTypeForm.classList.add('d-none');
            violationTypesList.classList.remove('d-none');
        });
    }
    
    // ปุ่มยกเลิกในฟอร์ม
    if (btnCancelViolationType) {
        btnCancelViolationType.addEventListener('click', function() {
            violationTypeForm.classList.add('d-none');
            violationTypesList.classList.remove('d-none');
        });
    }
    
    // จัดการปุ่มแก้ไข
    const editButtons = document.querySelectorAll('.edit-violation-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            
            // ในสถานการณ์จริงต้อง fetch ข้อมูลจาก API
            // นี่เป็นเพียงตัวอย่าง mockup:
            let mockData = {
                violations_id: violationId,
                violations_name: '',
                violations_category: '',
                violations_points_deducted: 0,
                violations_description: ''
            };
            
            if (violationId === '1') {
                mockData = {
                    violations_id: '1',
                    violations_name: 'ผิดระเบียบการแต่งกาย',
                    violations_category: 'medium',
                    violations_points_deducted: 5,
                    violations_description: 'นักเรียนแต่งกายไม่ถูกระเบียบตามข้อกำหนดของโรงเรียน'
                };
            } else if (violationId === '2') {
                mockData = {
                    violations_id: '2',
                    violations_name: 'มาสาย',
                    violations_category: 'light',
                    violations_points_deducted: 3,
                    violations_description: 'นักเรียนมาโรงเรียนหลังเวลา 08:00 น.'
                };
            } else if (violationId === '3') {
                mockData = {
                    violations_id: '3',
                    violations_name: 'ทะเลาะวิวาท',
                    violations_category: 'severe',
                    violations_points_deducted: 20,
                    violations_description: 'นักเรียนก่อเหตุทะเลาะวิวาท ทำร้ายร่างกายผู้อื่น'
                };
            }
            
            // เติมข้อมูลลงในฟอร์ม
            document.getElementById('violationTypeId').value = mockData.violations_id;
            document.getElementById('violations_name').value = mockData.violations_name;
            document.getElementById('violations_category').value = mockData.violations_category;
            document.getElementById('violations_points_deducted').value = mockData.violations_points_deducted;
            document.getElementById('violations_description').value = mockData.violations_description;
            
            // เปลี่ยนชื่อหัวฟอร์ม
            formViolationTitle.textContent = 'แก้ไขประเภทพฤติกรรม';
            
            // แสดงฟอร์ม ซ่อนรายการ
            violationTypesList.classList.add('d-none');
            violationTypeForm.classList.remove('d-none');
        });
    });
    
    // จัดการปุ่มลบ
    const deleteButtons = document.querySelectorAll('.delete-violation-btn');
    const deleteViolationModal = new bootstrap.Modal(document.getElementById('deleteViolationModal'));
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            document.getElementById('deleteViolationId').value = violationId;
            deleteViolationModal.show();
        });
    });
    
    // ปุ่มยืนยันการลบ
    const confirmDeleteBtn = document.getElementById('confirmDeleteViolation');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const violationId = document.getElementById('deleteViolationId').value;
            
            // ในสถานการณ์จริงต้องส่ง request ไปลบข้อมูลในฐานข้อมูล
            console.log('Deleting violation with ID: ' + violationId);
            
            // จำลองการลบสำเร็จ
            // show notification toast
            const toast = new bootstrap.Toast(document.createElement('div'));
            
            // ปิด modal ยืนยันการลบ
            deleteViolationModal.hide();
            
            // ในสถานการณ์จริง ควรมีการแสดงผลลัพธ์การลบและ refresh ข้อมูลใหม่
        });
    }
    
    // การบันทึกฟอร์ม
    if (formViolationType) {
        formViolationType.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // ตรวจสอบความถูกต้องของฟอร์ม
            if (this.checkValidity()) {
                // อ่านข้อมูลจากฟอร์ม
                const formData = {
                    violations_id: document.getElementById('violationTypeId').value,
                    violations_name: document.getElementById('violations_name').value,
                    violations_category: document.getElementById('violations_category').value,
                    violations_points_deducted: document.getElementById('violations_points_deducted').value,
                    violations_description: document.getElementById('violations_description').value
                };
                
                // ในสถานการณ์จริงต้องส่ง request ไปเพิ่ม/แก้ไขข้อมูลในฐานข้อมูล
                console.log('Saving violation data:', formData);
                
                // จำลองการบันทึกสำเร็จ
                // show success toast
                
                // กลับไปแสดงรายการ
                violationTypeForm.classList.add('d-none');
                violationTypesList.classList.remove('d-none');
                
                // ในสถานการณ์จริง ควรมีการ refresh ข้อมูลใหม่
            } else {
                // แสดงข้อความเตือนกรณีกรอกข้อมูลไม่ครบ/ไม่ถูกต้อง
                this.classList.add('was-validated');
            }
        });
    }
    
    // ค้นหาประเภทพฤติกรรม
    const violationTypeSearch = document.getElementById('violationTypeSearch');
    if (violationTypeSearch) {
        violationTypeSearch.addEventListener('keyup', function(e) {
            if (e.key === 'Enter' || this.value.length > 2) {
                // ในสถานการณ์จริงต้องค้นหาข้อมูลจากฐานข้อมูล
                console.log('Searching for: ' + this.value);
                
                // จำลองแสดงผลการค้นหา
                // ...
            }
        });
    }
    
    // แก้ไขการใช้ jQuery ด้วย Vanilla JavaScript
    const violationTypesModal = document.getElementById('violationTypesModal');
    if (violationTypesModal) {
        violationTypesModal.addEventListener('shown.bs.modal', function() {
            fetchViolations();
        });
    }
    
    const newViolationModal = document.getElementById('newViolationModal');
    if (newViolationModal) {
        newViolationModal.addEventListener('show.bs.modal', function() {
            // ตั้งค่าวันที่และเวลาเริ่มต้น
            const dateInput = document.getElementById('violationDate');
            const timeInput = document.getElementById('violationTime');
            
            if (dateInput) {
                dateInput.valueAsDate = new Date();
            }
            
            if (timeInput) {
                const now = new Date();
                now.setHours(8, 0, 0); // ตั้งเวลาเริ่มต้นเป็น 8:00 น.
                timeInput.value = now.toTimeString().substring(0, 5);
            }
            
            // เรียก function อื่นๆ ที่ต้องการ
            updateViolationSelects();
        });
    }
});

// Chart initialization functions
function initViolationTrendChart() {
    const ctx = document.getElementById('violationTrend');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['1', '5', '10', '15', '20', '25', '30'],
            datasets: [{
                label: 'พฤติกรรมที่ถูกบันทึก',
                data: [12, 19, 8, 15, 20, 27, 30],
                borderColor: 'rgb(16, 32, 173)',
                backgroundColor: 'rgba(16, 32, 173, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวนการบันทึก'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'วันที่'
                    }
                }
            }
        }
    });
}

function initViolationTypesChart() {
    const ctx = document.getElementById('violationTypes');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [
                'ผิดระเบียบการแต่งกาย',
                'มาสาย',
                'ใช้โทรศัพท์ในเวลาเรียน',
                'ไม่ส่งการบ้าน',
                'อื่นๆ'
            ],
            datasets: [{
                data: [30, 25, 15, 20, 10],
                backgroundColor: [
                    '#dc3545',
                    '#ffc107',
                    '#17a2b8',
                    '#fd7e14',
                    '#6c757d'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
}

/**
 * JavaScript สำหรับจัดการประเภทพฤติกรรม
 * ใช้งานกับ Modal: violationTypesModal
 */

// ตัวแปรสำหรับเก็บข้อมูลและสถานะต่าง ๆ
const violationManager = {
    currentPage: 1,
    totalPages: 1,
    searchTerm: '',
    violations: [],
    isLoading: false,
    csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
};

// ฟังก์ชันสำหรับดึงข้อมูลประเภทพฤติกรรมทั้งหมด
function fetchViolations(page = 1, search = '') {
    violationManager.isLoading = true;
    showLoading('violationTypesList');

    fetch(`/api/violations?page=${page}&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                violationManager.violations = data.data.data;
                violationManager.currentPage = data.data.current_page;
                violationManager.totalPages = data.data.last_page;
                
                renderViolationsList();
                renderPagination();
            } else {
                showError('ไม่สามารถดึงข้อมูลประเภทพฤติกรรมได้');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
        })
        .finally(() => {
            violationManager.isLoading = false;
            hideLoading('violationTypesList');
        });
}

// ฟังก์ชันสำหรับแสดงรายการประเภทพฤติกรรม
function renderViolationsList() {
    const tableBody = document.querySelector('#violationTypesList table tbody');
    
    if (violationManager.violations.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>ไม่พบข้อมูลประเภทพฤติกรรม</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tableBody.innerHTML = '';
    
    violationManager.violations.forEach(violation => {
        // สร้าง badge สำหรับระดับความรุนแรง
        let categoryBadge = '';
        let categoryText = '';
        
        switch (violation.violations_category) {
            case 'light':
                categoryBadge = '<span class="badge bg-info text-white">เบา</span>';
                categoryText = 'เบา';
                break;
            case 'medium':
                categoryBadge = '<span class="badge bg-warning text-dark">ปานกลาง</span>';
                categoryText = 'ปานกลาง';
                break;
            case 'severe':
                categoryBadge = '<span class="badge bg-danger text-white">รุนแรง</span>';
                categoryText = 'รุนแรง';
                break;
        }
        
        // สร้าง row
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${violation.violations_name}</td>
            <td>${categoryBadge}</td>
            <td>${violation.violations_points_deducted}</td>
            <td class="text-truncate" style="max-width: 200px;">${violation.violations_description || '-'}</td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary edit-violation-btn" data-id="${violation.violations_id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-violation-btn" data-id="${violation.violations_id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
    
    // เพิ่ม event listeners สำหรับปุ่มแก้ไขและลบ
    attachEditButtonListeners();
    attachDeleteButtonListeners();
}

// ฟังก์ชันสำหรับแสดง pagination
function renderPagination() {
    const pagination = document.querySelector('#violationTypesList nav ul');
    
    if (!pagination) return;
    
    pagination.innerHTML = '';
    
    // ปุ่ม Previous
    const prevLi = document.createElement('li');
    prevLi.classList.add('page-item');
    if (violationManager.currentPage === 1) {
        prevLi.classList.add('disabled');
    }
    prevLi.innerHTML = `<a class="page-link" href="#" data-page="${violationManager.currentPage - 1}">Previous</a>`;
    pagination.appendChild(prevLi);
    
    // หน้าต่าง ๆ
    for (let i = 1; i <= violationManager.totalPages; i++) {
        const pageLi = document.createElement('li');
        pageLi.classList.add('page-item');
        if (i === violationManager.currentPage) {
            pageLi.classList.add('active');
        }
        pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        pagination.appendChild(pageLi);
    }
    
    // ปุ่ม Next
    const nextLi = document.createElement('li');
    nextLi.classList.add('page-item');
    if (violationManager.currentPage === violationManager.totalPages) {
        nextLi.classList.add('disabled');
    }
    nextLi.innerHTML = `<a class="page-link" href="#" data-page="${violationManager.currentPage + 1}">Next</a>`;
    pagination.appendChild(nextLi);
    
    // เพิ่ม event listeners สำหรับ pagination
    document.querySelectorAll('#violationTypesList nav ul li:not(.disabled) a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            fetchViolations(page, violationManager.searchTerm);
        });
    });
}

// ฟังก์ชันสำหรับแสดง loading
function showLoading(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        const loadingHTML = `
            <div class="text-center py-5 loading-overlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
            </div>
        `;
        
        const loadingEl = document.createElement('div');
        loadingEl.innerHTML = loadingHTML;
        loadingEl.classList.add('loading-container');
        container.appendChild(loadingEl);
    }
}

// ฟังก์ชันสำหรับซ่อน loading
function hideLoading(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        const loadingEl = container.querySelector('.loading-container');
        if (loadingEl) {
            container.removeChild(loadingEl);
        }
    }
}

// ฟังก์ชันสำหรับแสดงข้อความผิดพลาด
function showError(message) {
    // สร้าง toast สำหรับแสดงข้อความผิดพลาด
    const toastContainer = document.querySelector('.toast-container');
    
    if (!toastContainer) return;
    
    const toastId = `error-toast-${Date.now()}`;
    const toastHTML = `
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">ข้อผิดพลาด</strong>
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
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
    
    // ลบ toast หลังจากแสดง
    toastEl.addEventListener('hidden.bs.toast', function () {
        this.remove();
    });
}

// ฟังก์ชันสำหรับแสดงข้อความสำเร็จ
function showSuccess(message) {
    // สร้าง toast สำหรับแสดงข้อความสำเร็จ
    const toastContainer = document.querySelector('.toast-container');
    
    if (!toastContainer) return;
    
    const toastId = `success-toast-${Date.now()}`;
    const toastHTML = `
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">สำเร็จ</strong>
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
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
    
    // ลบ toast หลังจากแสดง
    toastEl.addEventListener('hidden.bs.toast', function () {
        this.remove();
    });
}

// ฟังก์ชันสำหรับบันทึกประเภทพฤติกรรมใหม่หรืออัปเดต
function saveViolation(formData) {
    const violationId = formData.get('violations_id');
    const isUpdate = violationId && violationId !== '';
    
    const url = isUpdate ? `/api/violations/${violationId}` : '/api/violations';
    const method = isUpdate ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': violationManager.csrfToken
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            
            // รีเซ็ตฟอร์มและซ่อน
            document.getElementById('violationTypeForm').classList.add('d-none');
            document.getElementById('violationTypesList').classList.remove('d-none');
            document.getElementById('formViolationType').reset();
            
            // โหลดข้อมูลใหม่
            fetchViolations(violationManager.currentPage, violationManager.searchTerm);
        } else {
            if (data.errors) {
                // แสดงข้อความผิดพลาดสำหรับแต่ละฟิลด์
                Object.keys(data.errors).forEach(field => {
                    const inputEl = document.getElementById(field);
                    if (inputEl) {
                        inputEl.classList.add('is-invalid');
                        
                        // สร้างหรือแก้ไข div.invalid-feedback
                        let feedbackEl = inputEl.nextElementSibling;
                        if (!feedbackEl || !feedbackEl.classList.contains('invalid-feedback')) {
                            feedbackEl = document.createElement('div');
                            feedbackEl.classList.add('invalid-feedback');
                            inputEl.parentNode.insertBefore(feedbackEl, inputEl.nextSibling);
                        }
                        
                        feedbackEl.textContent = data.errors[field][0];
                    }
                });
            } else {
                showError(data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
    });
}

// ฟังก์ชันสำหรับลบประเภทพฤติกรรม
function deleteViolation(violationId) {
    fetch(`/api/violations/${violationId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': violationManager.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            
            // ปิด modal ยืนยันการลบ
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteViolationModal'));
            deleteModal.hide();
            
            // โหลดข้อมูลใหม่
            fetchViolations(violationManager.currentPage, violationManager.searchTerm);
        } else {
            showError(data.message || 'เกิดข้อผิดพลาดในการลบข้อมูล');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
    });
}

// ฟังก์ชันสำหรับดึงข้อมูลประเภทพฤติกรรมตาม ID
function fetchViolationById(violationId) {
    return fetch(`/api/violations/${violationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'ไม่พบข้อมูลประเภทพฤติกรรม');
            }
        });
}

// ฟังก์ชันสำหรับเพิ่ม event listeners ให้กับปุ่มแก้ไข
function attachEditButtonListeners() {
    document.querySelectorAll('.edit-violation-btn').forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            
            // แสดง loading ในฟอร์ม
            document.getElementById('violationTypesList').classList.add('d-none');
            document.getElementById('violationTypeForm').classList.remove('d-none');
            document.getElementById('formViolationTitle').textContent = 'กำลังโหลดข้อมูล...';
            
            // ดึงข้อมูลประเภทพฤติกรรม
            fetchViolationById(violationId)
                .then(violation => {
                    // เติมข้อมูลลงในฟอร์ม
                    document.getElementById('violationTypeId').value = violation.violations_id;
                    document.getElementById('violations_name').value = violation.violations_name;
                    document.getElementById('violations_category').value = violation.violations_category;
                    document.getElementById('violations_points_deducted').value = violation.violations_points_deducted;
                    document.getElementById('violations_description').value = violation.violations_description || '';
                    
                    // เปลี่ยนชื่อหัวฟอร์ม
                    document.getElementById('formViolationTitle').textContent = 'แก้ไขประเภทพฤติกรรม';
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError(error.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล');
                    
                    // กลับไปหน้ารายการ
                    document.getElementById('violationTypeForm').classList.add('d-none');
                    document.getElementById('violationTypesList').classList.remove('d-none');
                });
        });
    });
}

// ฟังก์ชันสำหรับเพิ่ม event listeners ให้กับปุ่มลบ
function attachDeleteButtonListeners() {
    document.querySelectorAll('.delete-violation-btn').forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            document.getElementById('deleteViolationId').value = violationId;
            
            // หาชื่อประเภทพฤติกรรมจากข้อมูลที่มีอยู่
            const violation = violationManager.violations.find(v => v.violations_id == violationId);
            if (violation) {
                document.querySelector('#deleteViolationModal .modal-body h5').textContent = 
                    `ยืนยันการลบประเภทพฤติกรรม "${violation.violations_name}"?`;
            }
            
            // แสดง modal ยืนยันการลบ
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteViolationModal'));
            deleteModal.show();
        });
    });
}

// Initialize เมื่อ DOM โหลดเสร็จ
document.addEventListener('DOMContentLoaded', function() {
    // เชื่อมต่อกับ Elements ต่าง ๆ
    const violationTypesList = document.getElementById('violationTypesList');
    const violationTypeForm = document.getElementById('violationTypeForm');
    const btnShowAddViolationType = document.getElementById('btnShowAddViolationType');
    const btnCloseViolationForm = document.getElementById('btnCloseViolationForm');
    const btnCancelViolationType = document.getElementById('btnCancelViolationType');
    const formViolationType = document.getElementById('formViolationType');
    const violationTypeSearch = document.getElementById('violationTypeSearch');
    const confirmDeleteViolation = document.getElementById('confirmDeleteViolation');
    
    // ตรวจสอบว่า Elements ที่จำเป็นมีอยู่หรือไม่
    if (!violationTypesList || !violationTypeForm || !formViolationType) return;
    
    // โหลดข้อมูลประเภทพฤติกรรมเมื่อ modal แสดง
    const violationTypesModal = document.getElementById('violationTypesModal');
    if (violationTypesModal) {
        violationTypesModal.addEventListener('shown.bs.modal', function() {
            fetchViolations();
        });
    }
    
    // กำหนด event ให้กับปุ่มเพิ่มประเภทพฤติกรรมใหม่
    if (btnShowAddViolationType) {
        btnShowAddViolationType.addEventListener('click', function() {
            // รีเซ็ตฟอร์ม
            formViolationType.reset();
            document.getElementById('violationTypeId').value = '';
            document.getElementById('formViolationTitle').textContent = 'เพิ่มประเภทพฤติกรรมใหม่';
            
            // แสดงฟอร์ม ซ่อนรายการ
            violationTypesList.classList.add('d-none');
            violationTypeForm.classList.remove('d-none');
            
            // ล้างข้อความผิดพลาด
            formViolationType.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            formViolationType.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        });
    }
    
    // กำหนด event ให้กับปุ่มปิดฟอร์ม
    if (btnCloseViolationForm) {
        btnCloseViolationForm.addEventListener('click', function() {
            violationTypeForm.classList.add('d-none');
            violationTypesList.classList.remove('d-none');
        });
    }
    
    // กำหนด event ให้กับปุ่มยกเลิกในฟอร์ม
    if (btnCancelViolationType) {
        btnCancelViolationType.addEventListener('click', function() {
            violationTypeForm.classList.add('d-none');
            violationTypesList.classList.remove('d-none');
        });
    }
    
    // กำหนด event ให้กับฟอร์มบันทึกประเภทพฤติกรรม
    formViolationType.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // ล้างข้อความผิดพลาด
        this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        this.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        
        // ตรวจสอบความถูกต้องของฟอร์ม
        if (this.checkValidity()) {
            // สร้าง FormData และแปลงเป็น object
            const formData = new FormData(this);
            
            // เรียกฟังก์ชันบันทึกข้อมูล
            saveViolation(formData);
        } else {
            // แสดงข้อความที่ browser validate
            this.classList.add('was-validated');
        }
    });
    
    // กำหนด event ให้กับช่องค้นหา
    if (violationTypeSearch) {
        violationTypeSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                violationManager.searchTerm = this.value;
                fetchViolations(1, this.value);
            }
        });
        
        // ปุ่มค้นหา (ถ้ามี)
        const searchButton = violationTypeSearch.nextElementSibling;
        if (searchButton && searchButton.tagName === 'BUTTON') {
            searchButton.addEventListener('click', function() {
                violationManager.searchTerm = violationTypeSearch.value;
                fetchViolations(1, violationTypeSearch.value);
            });
        }
    }
    
    // กำหนด event ให้กับปุ่มยืนยันการลบ
    if (confirmDeleteViolation) {
        confirmDeleteViolation.addEventListener('click', function() {
            const violationId = document.getElementById('deleteViolationId').value;
            if (violationId) {
                deleteViolation(violationId);
            }
        });
    }
});

// อัปเดตการเลือกประเภทพฤติกรรมในฟอร์มบันทึกพฤติกรรม
function updateViolationSelects() {
    fetch('/api/violations/all', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error(`Response is not JSON (${response.status})`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateSelectOptions(data.data);
        } else {
            console.error('API Error:', data);
            showError('ไม่สามารถดึงข้อมูลประเภทพฤติกรรมได้');
        }
    })
    .catch(error => {
        console.error('Error fetching violations for select:', error);
        showError('เกิดข้อผิดพลาดในการดึงข้อมูลประเภทพฤติกรรม กรุณาลองใหม่อีกครั้ง');
    });
}

// เพิ่มฟังก์ชันที่ใช้งานจริงในการอัพเดตตัวเลือก
function updateSelectOptions(violations) {
    const selects = document.querySelectorAll('select[data-violation-select]');
    
    selects.forEach(select => {
        // เก็บค่าที่เลือกไว้ (ถ้ามี)
        const selectedValue = select.value;
        
        // ล้างตัวเลือกเดิม (ยกเว้นตัวเลือกแรก)
        while (select.options.length > 1) {
            select.remove(1);
        }
        
        // เพิ่มตัวเลือกใหม่
        violations.forEach(violation => {
            const option = new Option(violation.violations_name, violation.violations_id);
            
            // เพิ่ม data-category และ data-points
            option.dataset.category = violation.violations_category;
            option.dataset.points = violation.violations_points_deducted;
            
            select.add(option);
        });
        
        // เลือกค่าเดิม (ถ้ามี)
        if (selectedValue) {
            select.value = selectedValue;
        }
        
        // ทริกเกอร์ event change
        select.dispatchEvent(new Event('change'));
    });
}

// อัปเดต select box เมื่อมีการเปิด modal
document.addEventListener('DOMContentLoaded', function() {
    // เมื่อเปิด modal บันทึกพฤติกรรม
    const newViolationModal = document.getElementById('newViolationModal');
    if (newViolationModal) {
        newViolationModal.addEventListener('show.bs.modal', function() {
            updateViolationSelects();
        });
    }
});


document.addEventListener('DOMContentLoaded', function() {
    const profileInput = document.getElementById('profile_image');
    const profilePreview = document.getElementById('profile-preview');
    
    if (profileInput && profilePreview) {
        profileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});