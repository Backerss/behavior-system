// Set current date
document.addEventListener('DOMContentLoaded', function() {
    // Set current date in Thai format
    const today = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const thaiDate = today.toLocaleDateString('th-TH', options);
    const currentDateElement = document.querySelector('.current-date');
    if (currentDateElement) {
        currentDateElement.textContent = thaiDate;
    }
    
    // Initialize charts
    initViolationTrendChart();
    initViolationTypesChart();
    
    // Mobile navigation active state
    const navLinks = document.querySelectorAll('.bottom-navbar .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active', 'text-primary-app'));
            // Add active class to clicked link
            this.classList.add('active', 'text-primary-app');
        });
    });
    
    // Sidebar navigation active state
    const menuItems = document.querySelectorAll('.sidebar-menu .menu-item');
    menuItems.forEach(item => {
        if (!item.hasAttribute('data-bs-toggle')) {
            item.addEventListener('click', function() {
                // Remove active class from all items
                menuItems.forEach(i => i.classList.remove('active'));
                // Add active class to clicked item
                this.classList.add('active');
            });
        }
    });
    
    // Student search in violation modal
    const studentSearchInput = document.querySelector('#newViolationModal input[placeholder="พิมพ์ชื่อหรือรหัสนักเรียน..."]');
    const selectedStudentContainer = document.querySelector('.selected-student');
    
    if (studentSearchInput) {
        studentSearchInput.addEventListener('keyup', function(e) {
            const searchTerm = this.value.trim();
            
            if (searchTerm.length >= 2) {
                // Simulate student search
                setTimeout(() => {
                    showStudentSearchResults(searchTerm);
                }, 300);
            } else {
                hideStudentSearchResults();
            }
        });
        
        // Remove selected student
        const removeStudentBtn = document.querySelector('.selected-student .btn-close');
        if (removeStudentBtn) {
            removeStudentBtn.addEventListener('click', function() {
                if (selectedStudentContainer) {
                    selectedStudentContainer.style.display = 'none';
                }
                studentSearchInput.value = '';
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
    if (popoverTriggerList.length > 0) {
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }
    
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length > 0) {
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }
    
    // สำหรับแสดงฟอร์มเพิ่มประเภทพฤติกรรมใหม่
    const btnShowAddViolationType = document.getElementById('btnShowAddViolationType');
    const violationTypesList = document.getElementById('violationTypesList');
    const violationTypeForm = document.getElementById('violationTypeForm');
    const btnCloseViolationForm = document.getElementById('btnCloseViolationForm');
    const btnCancelViolationType = document.getElementById('btnCancelViolationType');
    const formViolationTitle = document.getElementById('formViolationTitle');
    const studentSearch = document.getElementById('studentSearch');
    const btnSearchStudent = document.getElementById('btnSearchStudent');
    const classFilter = document.getElementById('classFilter');
    
    // ปุ่มแสดงฟอร์มเพิ่มใหม่
    if (btnShowAddViolationType) {
        btnShowAddViolationType.addEventListener('click', function() {
            if (violationTypesList) violationTypesList.classList.add('d-none');
            if (violationTypeForm) violationTypeForm.classList.remove('d-none');
            if (formViolationTitle) formViolationTitle.textContent = 'เพิ่มประเภทพฤติกรรมใหม่';
        });
    }
    
    // ปุ่มปิดฟอร์ม
    if (btnCloseViolationForm) {
        btnCloseViolationForm.addEventListener('click', function() {
            if (violationTypeForm) violationTypeForm.classList.add('d-none');
            if (violationTypesList) violationTypesList.classList.remove('d-none');
        });
    }
    
    // ปุ่มยกเลิกในฟอร์ม
    if (btnCancelViolationType) {
        btnCancelViolationType.addEventListener('click', function() {
            if (violationTypeForm) violationTypeForm.classList.add('d-none');
            if (violationTypesList) violationTypesList.classList.remove('d-none');
        });
    }
    
    // จัดการปุ่มแก้ไข
    const editButtons = document.querySelectorAll('.edit-violation-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            editViolationType(violationId);
        });
    });
    
    // จัดการปุ่มลบ
    const deleteButtons = document.querySelectorAll('.delete-violation-btn');
    const deleteViolationModal = document.getElementById('deleteViolationModal');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            const deleteViolationId = document.getElementById('deleteViolationId');
            if (deleteViolationId) {
                deleteViolationId.value = violationId;
            }
            if (deleteViolationModal && bootstrap.Modal) {
                const modal = new bootstrap.Modal(deleteViolationModal);
                modal.show();
            }
        });
    });
    
    // ปุ่มยืนยันการลบ
    const confirmDeleteBtn = document.getElementById('confirmDeleteViolation');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const deleteViolationId = document.getElementById('deleteViolationId');
            if (deleteViolationId) {
                const violationId = deleteViolationId.value;
                deleteViolationType(violationId);
            }
        });
    }
    
    // การบันทึกฟอร์ม
    const formViolationType = document.getElementById('formViolationType');
    if (formViolationType) {
        formViolationType.addEventListener('submit', function(e) {
            e.preventDefault();
            saveViolationType();
        });
    }
    
    // ค้นหาประเภทพฤติกรรม
    const violationTypeSearch = document.getElementById('violationTypeSearch');
    if (violationTypeSearch) {
        violationTypeSearch.addEventListener('keyup', function(e) {
            const searchTerm = this.value.trim();
            filterViolationTypes(searchTerm);
        });
    }
    
    // แก้ไขการใช้ jQuery ด้วย Vanilla JavaScript
    const violationTypesModal = document.getElementById('violationTypesModal');
    if (violationTypesModal) {
        violationTypesModal.addEventListener('shown.bs.modal', function() {
            loadViolationTypes();
        });
    }
    
    const newViolationModal = document.getElementById('newViolationModal');
    if (newViolationModal) {
        newViolationModal.addEventListener('show.bs.modal', function() {
            updateViolationSelects();
        });
    }

    if (studentSearch && btnSearchStudent) {
        btnSearchStudent.addEventListener('click', function() {
            searchStudents();
        });
        
        studentSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchStudents();
            }
        });
    }
    
    if (classFilter) {
        classFilter.addEventListener('change', function() {
            searchStudents();
        });
    }

    // Profile image preview
    const profileInput = document.getElementById('profile_image');
    const profilePreview = document.getElementById('profile-preview');
    
    if (profileInput && profilePreview) {
        profileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
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
    
    // ฟังก์ชันกรองข้อมูลตามประเภทพฤติกรรม
    const trendFilterItems = document.querySelectorAll('#trendFilterDropdown + .dropdown-menu .dropdown-item');
    trendFilterItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filterType = this.getAttribute('data-filter');
            filterChartData(filterType);
        });
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

// ฟังก์ชันค้นหานักเรียน
function searchStudents() {
    const studentSearchInput = document.getElementById('studentSearch');
    const classFilterInput = document.getElementById('classFilter');
    
    const searchTerm = studentSearchInput ? studentSearchInput.value : '';
    const classId = classFilterInput ? classFilterInput.value : '';
    
    let url = '/students?';
    if (searchTerm) {
        url += `search=${encodeURIComponent(searchTerm)}&`;
    }
    if (classId) {
        url += `class=${encodeURIComponent(classId)}&`;
    }
    
    window.location.href = url;
}

// ฟังก์ชันแสดงผลการค้นหานักเรียน
function showStudentSearchResults(searchTerm) {
    // สำหรับการจำลองผลการค้นหา
    const resultsContainer = document.querySelector('.student-search-results');
    if (resultsContainer) {
        resultsContainer.innerHTML = `<div class="list-group-item">ผลการค้นหา: ${searchTerm}</div>`;
        resultsContainer.style.display = 'block';
    }
}

// ฟังก์ชันซ่อนผลการค้นหานักเรียน
function hideStudentSearchResults() {
    const resultsContainer = document.querySelector('.student-search-results');
    if (resultsContainer) {
        resultsContainer.style.display = 'none';
    }
}

// ฟังก์ชันกรองข้อมูลกราฟ
function filterChartData(filterType) {
    console.log('กรองข้อมูลตาม:', filterType);
    // ใส่โค้ดสำหรับการกรองข้อมูลกราฟที่นี่
}

// ฟังก์ชันแก้ไขประเภทพฤติกรรม
function editViolationType(violationId) {
    console.log('แก้ไขประเภทพฤติกรรม ID:', violationId);
    // ใส่โค้ดสำหรับการแก้ไขที่นี่
}

// ฟังก์ชันลบประเภทพฤติกรรม
function deleteViolationType(violationId) {
    console.log('ลบประเภทพฤติกรรม ID:', violationId);
    // ใส่โค้ดสำหรับการลบที่นี่
}

// ฟังก์ชันบันทึกประเภทพฤติกรรม
function saveViolationType() {
    console.log('บันทึกประเภทพฤติกรรม');
    // ใส่โค้ดสำหรับการบันทึกที่นี่
}

// ฟังก์ชันโหลดประเภทพฤติกรรม
function loadViolationTypes() {
    console.log('โหลดประเภทพฤติกรรม');
    // ใส่โค้ดสำหรับการโหลดข้อมูลที่นี่
}

// ฟังก์ชันกรองประเภทพฤติกรรม
function filterViolationTypes(searchTerm) {
    console.log('กรองประเภทพฤติกรรม:', searchTerm);
    // ใส่โค้ดสำหรับการกรองที่นี่
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
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
};

// ฟังก์ชันสำหรับดึงข้อมูลประเภทพฤติกรรมทั้งหมด
function fetchViolations(page = 1, search = '') {
    violationManager.isLoading = true;
    showLoading('violationTypesList');

    fetch(`/api/violations?page=${page}&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                violationManager.violations = data.data.data || [];
                violationManager.currentPage = data.data.current_page || 1;
                violationManager.totalPages = data.data.last_page || 1;
                
                renderViolationsList();
                renderPagination();
            } else {
                showError('เกิดข้อผิดพลาดในการดึงข้อมูลประเภทพฤติกรรม');
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
    
    if (!tableBody) return;
    
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
                categoryBadge = '<span class="badge bg-success">เบา</span>';
                categoryText = 'เบา';
                break;
            case 'medium':
                categoryBadge = '<span class="badge bg-warning">ปานกลาง</span>';
                categoryText = 'ปานกลาง';
                break;
            case 'severe':
                categoryBadge = '<span class="badge bg-danger">หนัก</span>';
                categoryText = 'หนัก';
                break;
            default:
                categoryBadge = '<span class="badge bg-secondary">ไม่ระบุ</span>';
                categoryText = 'ไม่ระบุ';
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
            if (page > 0 && page <= violationManager.totalPages) {
                fetchViolations(page, violationManager.searchTerm);
            }
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
            loadingEl.remove();
        }
    }
}

// ฟังก์ชันสำหรับแสดงข้อความผิดพลาด
function showError(message) {
    // สร้าง toast สำหรับแสดงข้อความผิดพลาด
    let toastContainer = document.querySelector('.toast-container');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.classList.add('toast-container', 'position-fixed', 'top-0', 'end-0', 'p-3');
        toastContainer.style.zIndex = '1070';
        document.body.appendChild(toastContainer);
    }
    
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
    if (toastEl && window.bootstrap) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        
        // ลบ toast หลังจากแสดง
        toastEl.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }
}

// ฟังก์ชันสำหรับแสดงข้อความสำเร็จ
function showSuccess(message) {
    // สร้าง toast สำหรับแสดงข้อความสำเร็จ
    let toastContainer = document.querySelector('.toast-container');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.classList.add('toast-container', 'position-fixed', 'top-0', 'end-0', 'p-3');
        toastContainer.style.zIndex = '1070';
        document.body.appendChild(toastContainer);
    }
    
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
    if (toastEl && window.bootstrap) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        
        // ลบ toast หลังจากแสดง
        toastEl.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }
}

// เพิ่มฟังก์ชันที่หายไป
function attachEditButtonListeners() {
    document.querySelectorAll('.edit-violation-btn').forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            editViolationType(violationId);
        });
    });
}

function attachDeleteButtonListeners() {
    document.querySelectorAll('.delete-violation-btn').forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            const deleteViolationId = document.getElementById('deleteViolationId');
            if (deleteViolationId) {
                deleteViolationId.value = violationId;
            }
            const deleteViolationModal = document.getElementById('deleteViolationModal');
            if (deleteViolationModal && bootstrap.Modal) {
                const modal = new bootstrap.Modal(deleteViolationModal);
                modal.show();
            }
        });
    });
}

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
            throw new Error('Response is not JSON');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateSelectOptions(data.data);
        } else {
            console.error('Failed to fetch violations:', data.message);
            showError('เกิดข้อผิดพลาดในการดึงข้อมูลประเภทพฤติกรรม');
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
            const option = document.createElement('option');
            option.value = violation.violations_id;
            option.textContent = violation.violations_name;
            option.setAttribute('data-points', violation.violations_points_deducted);
            select.appendChild(option);
        });
        
        // เลือกค่าเดิม (ถ้ามี)
        if (selectedValue) {
            select.value = selectedValue;
        }
        
        // ทริกเกอร์ event change
        select.dispatchEvent(new Event('change'));
    });
}