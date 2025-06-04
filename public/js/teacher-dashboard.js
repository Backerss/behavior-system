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
    const searchInput = document.querySelector('#students .form-control');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value;
            const url = new URL(window.location.href);
            
            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }
            
            // โหลดหน้าใหม่พร้อมพารามิเตอร์ค้นหา
            window.location.href = url.toString();
        });
    }
}

// เรียกใช้เมื่อหน้าโหลดเสร็จ
document.addEventListener('DOMContentLoaded', function() {
    searchStudents();
});

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

// เพิ่มตัวแปรสำหรับจัดการ violation
const violationManager = {
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    currentPage: 1,
    searchTerm: ''
};

// เพิ่มฟังก์ชันสำหรับ loading states
function showLoading(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        const loadingHTML = `
            <div class="text-center py-4" id="loading-${containerId}">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">กำลังโหลด...</span>
                </div>
                <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', loadingHTML);
    }
}

function hideLoading(containerId) {
    const loadingElement = document.getElementById(`loading-${containerId}`);
    if (loadingElement) {
        loadingElement.remove();
    }
}

// เพิ่มฟังก์ชันสำหรับแสดงข้อความ
function showSuccess(message) {
    // ใช้ SweetAlert2 หรือ Bootstrap Toast หรือ alert ธรรมดา
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ!',
            text: message,
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#1020AD'
        });
    } else {
        // ใช้ Bootstrap Toast หรือ alert ธรรมดา
        showToast(message, 'success');
    }
}

function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด!',
            text: message,
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#dc3545'
        });
    } else {
        showToast(message, 'error');
    }
}

// ฟังก์ชัน Toast สำรอง (ถ้าไม่มี SweetAlert2)
function showToast(message, type = 'info') {
    // สร้าง toast element
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    toast.show();
    
    // ลบ toast หลังจากซ่อน
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// เพิ่มฟังก์ชันสำหรับ violation management
function fetchViolations(page = 1, search = '') {
    const loadingContainer = document.querySelector('#violationTypesList .table tbody');
    if (loadingContainer) {
        loadingContainer.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
                </td>
            </tr>
        `;
    }
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    params.append('page', page);
    
    fetch(`/api/violations?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': violationManager.csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // ตรวจสอบโครงสร้างข้อมูลที่ได้รับ
        if (data && data.data) {
            // ถ้าข้อมูลมาในรูปแบบ Laravel pagination
            if (data.data.data && Array.isArray(data.data.data)) {
                renderViolationsList(data.data.data);
                renderPagination(data.data);
            } 
            // ถ้าข้อมูลมาในรูปแบบ array ธรรมดา
            else if (Array.isArray(data.data)) {
                renderViolationsList(data.data);
                // ไม่มี pagination สำหรับข้อมูลแบบ array ธรรมดา
                const paginationContainer = document.querySelector('#violationTypesList .pagination');
                if (paginationContainer) paginationContainer.innerHTML = '';
            }
            else {
                // ถ้าโครงสร้างข้อมูลไม่ตรงกับที่คาดหวัง
                // console.error('Unexpected data structure:', data); // ลบ debug log
                renderViolationsList([]);
            }
            
            violationManager.currentPage = page;
            violationManager.searchTerm = search;
        } else {
            showError(data?.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล');
            renderViolationsList([]);
        }
    })
    .catch(error => {
        // console.error('Fetch Error:', error); // ลบ debug log
        showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
        if (loadingContainer) {
            loadingContainer.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                        <p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
                        <button class="btn btn-sm btn-outline-primary" onclick="fetchViolations(${page}, '${search}')">
                            <i class="fas fa-redo me-1"></i> ลองใหม่
                        </button>
                    </td>
                </tr>
            `;
        }
    });
}

// แก้ไขฟังก์ชัน renderPagination ให้ปลอดภัยยิ่งขึ้น
function renderPagination(data) {
    const paginationContainer = document.querySelector('#violationTypesList .pagination');
    
    // ตรวจสอบว่ามี container และข้อมูล pagination
    if (!paginationContainer) {
        console.warn('Pagination container not found');
        return;
    }
    
    // ตรวจสอบว่าข้อมูลมี pagination properties หรือไม่
    if (!data || typeof data !== 'object' || !data.last_page || data.last_page <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }
    
    const currentPage = data.current_page || 1;
    const lastPage = data.last_page || 1;
    const paginationHTML = [];
    
    // Previous button
    if (currentPage > 1) {
        paginationHTML.push(`
            <li class="page-item">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `);
    }
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(lastPage, currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML.push(`
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `);
    }
    
    // Next button
    if (currentPage < lastPage) {
        paginationHTML.push(`
            <li class="page-item">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `);
    }
    
    paginationContainer.innerHTML = paginationHTML.join('');
    
    // Add click events (ลบ event listener เก่าก่อน)
    const newPaginationContainer = paginationContainer.cloneNode(true);
    paginationContainer.parentNode.replaceChild(newPaginationContainer, paginationContainer);
    
    newPaginationContainer.addEventListener('click', function(e) {
        e.preventDefault();
        const pageLink = e.target.closest('[data-page]');
        if (pageLink) {
            const page = parseInt(pageLink.dataset.page);
            if (page && page > 0 && page <= lastPage) {
                fetchViolations(page, violationManager.searchTerm);
            }
        }
    });
}

// เพิ่มฟังก์ชัน getCategoryBadge ก่อนฟังก์ชัน renderViolationsList
function getCategoryBadge(category) {
    const badges = {
        'light': '<span class="badge bg-success">เบา</span>',
        'medium': '<span class="badge bg-warning text-dark">ปานกลาง</span>',
        'severe': '<span class="badge bg-danger">หนัก</span>'
    };
    return badges[category] || '<span class="badge bg-secondary">ไม่ระบุ</span>';
}

// แก้ไขฟังก์ชัน renderViolationsList ให้ปลอดภัยยิ่งขึ้น
function renderViolationsList(violations) {
    const tbody = document.querySelector('#violationTypesList .table tbody');
    if (!tbody) {
        console.error('Table tbody not found');
        return;
    }
    
    // ตรวจสอบว่า violations เป็น array หรือไม่
    if (!Array.isArray(violations)) {
        console.error('Violations data is not an array:', violations);
        violations = [];
    }
    
    if (violations.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <p>ไม่พบข้อมูลประเภทพฤติกรรม</p>
                    ${violationManager.searchTerm ? `<p class="small">คำค้นหา: "${violationManager.searchTerm}"</p>` : ''}
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = violations.map(violation => {
        // ตรวจสอบความถูกต้องของข้อมูล violation
        const id = violation.violations_id || violation.id || '';
        const name = violation.violations_name || violation.name || 'ไม่ระบุ';
        const category = violation.violations_category || violation.category || '';
        const points = violation.violations_points_deducted || violation.points_deducted || 0;
        const description = violation.violations_description || violation.description || '';
        
        const categoryBadge = getCategoryBadge(category);
        
        return `
            <tr>
                <td>${escapeHtml(name)}</td>
                <td>${categoryBadge}</td>
                <td>${points} คะแนน</td>
                <td>${escapeHtml(description) || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary edit-violation-btn me-1" 
                            data-id="${id}" title="แก้ไข">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-violation-btn" 
                            data-id="${id}" title="ลบ">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    // เพิ่ม event listeners ใหม่
    attachEditButtonListeners();
    attachDeleteButtonListeners();
}

// เพิ่มฟังก์ชัน escapeHtml เพื่อป้องกัน XSS
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

// แก้ไขฟังก์ชัน updateViolationSelects ให้จัดการข้อมูลได้ดีขึ้น
function updateViolationSelects() {
    fetch('/api/violations/all', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': violationManager.csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        const violationSelect = document.getElementById('violationType');
        if (!violationSelect) {
            console.warn('Violation select element not found');
            return;
        }
        
        // เคลียร์ options เก่า (ยกเว้น option แรก)
        while (violationSelect.children.length > 1) {
            violationSelect.removeChild(violationSelect.lastChild);
        }
        
        // ตรวจสอบโครงสร้างข้อมูล
        let violations = [];
        if (data && data.data) {
            if (Array.isArray(data.data)) {
                violations = data.data;
            } else if (data.data.data && Array.isArray(data.data.data)) {
                violations = data.data.data;
            }
        }
        
        // เพิ่ม options ใหม่
        violations.forEach(violation => {
            const option = document.createElement('option');
            option.value = violation.violations_id || violation.id;
            option.textContent = violation.violations_name || violation.name;
            option.dataset.points = violation.violations_points_deducted || violation.points_deducted || 0;
            violationSelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error updating violation selects:', error);
    });
}

// เพิ่มฟังก์ชันสำหรับการ attach event listeners ของปุ่มแก้ไขและลบ
function attachEditButtonListeners() {
    const editButtons = document.querySelectorAll('.edit-violation-btn');
    editButtons.forEach(button => {
        // ลบ event listener เดิม (ถ้ามี)
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        // เพิ่ม event listener ใหม่
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const violationId = this.getAttribute('data-id');
            if (violationId && !this.disabled) {
                this.disabled = true;
                editViolationType(violationId);
                setTimeout(() => {
                    this.disabled = false;
                }, 1000);
            }
        });
    });
}

function attachDeleteButtonListeners() {
    const deleteButtons = document.querySelectorAll('.delete-violation-btn');
    deleteButtons.forEach(button => {
        // ลบ event listener เดิม (ถ้ามี)
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        // เพิ่ม event listener ใหม่
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
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

// แก้ไขฟังก์ชัน fetchViolations - ลบ debug logs
function fetchViolations(page = 1, search = '') {
    const loadingContainer = document.querySelector('#violationTypesList .table tbody');
    if (loadingContainer) {
        loadingContainer.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
                </td>
            </tr>
        `;
    }
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    params.append('page', page);
    
    fetch(`/api/violations?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': violationManager.csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // ตรวจสอบโครงสร้างข้อมูลที่ได้รับ
        if (data && data.data) {
            // ถ้าข้อมูลมาในรูปแบบ Laravel pagination
            if (data.data.data && Array.isArray(data.data.data)) {
                renderViolationsList(data.data.data);
                renderPagination(data.data);
            } 
            // ถ้าข้อมูลมาในรูปแบบ array ธรรมดา
            else if (Array.isArray(data.data)) {
                renderViolationsList(data.data);
                // ไม่มี pagination สำหรับข้อมูลแบบ array ธรรมดา
                const paginationContainer = document.querySelector('#violationTypesList .pagination');
                if (paginationContainer) paginationContainer.innerHTML = '';
            }
            else {
                // ถ้าโครงสร้างข้อมูลไม่ตรงกับที่คาดหวัง
                console.error('Unexpected data structure:', data);
                renderViolationsList([]);
            }
            
            violationManager.currentPage = page;
            violationManager.searchTerm = search;
        } else {
            showError(data?.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล');
            renderViolationsList([]);
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
        if (loadingContainer) {
            loadingContainer.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                        <p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
                        <button class="btn btn-sm btn-outline-primary" onclick="fetchViolations(${page}, '${search}')">
                            <i class="fas fa-redo me-1"></i> ลองใหม่
                        </button>
                    </td>
                </tr>
            `;
        }
    });
}

// แก้ไขฟังก์ชัน updateViolationSelects - ลบ debug logs
function updateViolationSelects() {
    fetch('/api/violations/all', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': violationManager.csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        const violationSelect = document.getElementById('violationType');
        if (!violationSelect) {
            console.warn('Violation select element not found');
            return;
        }
        
        // เคลียร์ options เก่า (ยกเว้น option แรก)
        while (violationSelect.children.length > 1) {
            violationSelect.removeChild(violationSelect.lastChild);
        }
        
        // ตรวจสอบโครงสร้างข้อมูล
        let violations = [];
        if (data && data.data) {
            if (Array.isArray(data.data)) {
                violations = data.data;
            } else if (data.data.data && Array.isArray(data.data.data)) {
                violations = data.data.data;
            }
        }
        
        // เพิ่ม options ใหม่
        violations.forEach(violation => {
            const option = document.createElement('option');
            option.value = violation.violations_id || violation.id;
            option.textContent = violation.violations_name || violation.name;
            option.dataset.points = violation.violations_points_deducted || violation.points_deducted || 0;
            violationSelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error updating violation selects:', error);
    });
}

// แก้ไขฟังก์ชัน editViolationType - ลบ debug logs และป้องกันการทำงานซ้ำ
function editViolationType(violationId) {
    if (!violationId) {
        showError('ไม่พบรหัสประเภทพฤติกรรม');
        return;
    }
    
    // ป้องกันการเรียกใช้ซ้ำ
    if (editViolationType.isProcessing) {
        return;
    }
    editViolationType.isProcessing = true;
    
    // แสดง loading state
    const loadingContainer = document.querySelector('#violationTypesList .table tbody');
    if (loadingContainer) {
        loadingContainer.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลดข้อมูลสำหรับแก้ไข...</span>
                    </div>
                    <p class="mt-2 text-muted">กำลังโหลดข้อมูลสำหรับแก้ไข...</p>
                </td>
            </tr>
        `;
    }
    
    // ดึงข้อมูลประเภทพฤติกรรมที่ต้องการแก้ไข
    fetch(`/api/violations/${violationId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': violationManager.csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data && (data.success !== false)) {
            // แสดงฟอร์มแก้ไข
            const violationData = data.data || data;
            showEditForm(violationData);
        } else {
            showError(data?.message || 'ไม่สามารถดึงข้อมูลประเภทพฤติกรรมได้');
            // กลับไปแสดงรายการ
            fetchViolations(violationManager.currentPage, violationManager.searchTerm);
        }
    })
    .catch(error => {
        console.error('Error fetching violation data:', error);
        showError('เกิดข้อผิดพลาดในการดึงข้อมูลประเภทพฤติกรรม');
        // กลับไปแสดงรายการ
        fetchViolations(violationManager.currentPage, violationManager.searchTerm);
    })
    .finally(() => {
        // รีเซ็ตสถานะ
        editViolationType.isProcessing = false;
    });
}

// ปรับปรุงฟังก์ชัน showEditForm ให้สร้างฟอร์มได้อย่างสมบูรณ์
function showEditForm(violation) {
    const violationTypesList = document.getElementById('violationTypesList');
    const violationTypeForm = document.getElementById('violationTypeForm');
    const formViolationTitle = document.getElementById('formViolationTitle');
    
    // ซ่อนรายการและแสดงฟอร์ม
    if (violationTypesList) violationTypesList.classList.add('d-none');
    if (violationTypeForm) violationTypeForm.classList.remove('d-none');
    if (formViolationTitle) formViolationTitle.textContent = 'แก้ไขประเภทพฤติกรรม';
    
    // สร้างฟอร์มแก้ไข
    const formContainer = violationTypeForm.querySelector('.card-body');
    if (formContainer) {
        formContainer.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0" id="formViolationTitle">แก้ไขประเภทพฤติกรรม</h6>
                <button type="button" class="btn-close" id="btnCloseViolationForm" aria-label="Close"></button>
            </div>
            
            <form id="formViolationType">
                <input type="hidden" id="editViolationId" value="${violation.violations_id}">
                
                <div class="mb-3">
                    <label for="violationName" class="form-label">ชื่อประเภทพฤติกรรม <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="violationName" name="violations_name" 
                           value="${violation.violations_name}" required maxlength="150"
                           placeholder="เช่น มาสาย, ไม่ส่งการบ้าน">
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3">
                    <label for="violationCategory" class="form-label">ระดับความรุนแรง <span class="text-danger">*</span></label>
                    <select class="form-select" id="violationCategory" name="violations_category" required>
                        <option value="">เลือกระดับความรุนแรง</option>
                        <option value="light" ${violation.violations_category === 'light' ? 'selected' : ''}>เบา</option>
                        <option value="medium" ${violation.violations_category === 'medium' ? 'selected' : ''}>ปานกลาง</option>
                        <option value="severe" ${violation.violations_category === 'severe' ? 'selected' : ''}>หนัก</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-3">
                    <label for="pointsDeducted" class="form-label">คะแนนที่หัก <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="pointsDeducted" name="violations_points_deducted"
                           value="${violation.violations_points_deducted}" required min="1" max="100"
                           placeholder="จำนวนคะแนนที่จะหัก">
                    <div class="form-text">คะแนนที่หักต้องอยู่ระหว่าง 1-100 คะแนน</div>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="mb-4">
                    <label for="violationDescription" class="form-label">รายละเอียด</label>
                    <textarea class="form-control" id="violationDescription" name="violations_description" 
                              rows="3" placeholder="อธิบายรายละเอียดของประเภทพฤติกรรมนี้">${violation.violations_description || ''}</textarea>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-app">
                        <i class="fas fa-save me-1"></i> บันทึกการเปลี่ยนแปลง
                    </button>
                    <button type="button" class="btn btn-secondary" id="btnCancelViolationType">
                        <i class="fas fa-times me-1"></i> ยกเลิก
                    </button>
                </div>
            </form>
        `;
        
        // เพิ่ม event listeners สำหรับฟอร์ม
        setupEditFormListeners();
    }
}

// ฟังก์ชันตั้งค่า event listeners สำหรับฟอร์มแก้ไข
function setupEditFormListeners() {
    // ปุ่มปิดฟอร์ม
    const btnCloseForm = document.getElementById('btnCloseViolationForm');
    if (btnCloseForm) {
        btnCloseForm.addEventListener('click', function() {
            hideEditForm();
        });
    }
    
    // ปุ่มยกเลิก
    const btnCancel = document.getElementById('btnCancelViolationType');
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            hideEditForm();
        });
    }
    
    // ฟอร์มส่งข้อมูล
    const form = document.getElementById('formViolationType');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            updateViolationType();
        });
    }
}

// ฟังก์ชันซ่อนฟอร์มแก้ไข
function hideEditForm() {
    const violationTypesList = document.getElementById('violationTypesList');
    const violationTypeForm = document.getElementById('violationTypeForm');
    
    if (violationTypeForm) violationTypeForm.classList.add('d-none');
    if (violationTypesList) violationTypesList.classList.remove('d-none');
    
    // รีเฟรชรายการ
    fetchViolations(violationManager.currentPage, violationManager.searchTerm);
}

// ฟังก์ชันอัปเดตประเภทพฤติกรรม
function updateViolationType() {
    const form = document.getElementById('formViolationType');
    const violationId = document.getElementById('editViolationId').value;
    
    if (!form || !violationId) return;
    
    // แสดง loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> กำลังบันทึก...';
    
    // เตรียมข้อมูล
    const formData = new FormData(form);
    const data = {
        violations_name: formData.get('violations_name'),
        violations_category: formData.get('violations_category'),
        violations_points_deducted: parseInt(formData.get('violations_points_deducted')),
        violations_description: formData.get('violations_description')
    };
    
    // ส่งข้อมูล
    fetch(`/api/violations/${violationId}`, {
        method: 'PUT',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': violationManager.csrfToken
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSuccess('อัปเดตประเภทพฤติกรรมเรียบร้อยแล้ว');
            // ปิดฟอร์มแก้ไขและรีเฟรชรายการ
            hideEditForm();
            fetchViolations(violationManager.currentPage, violationManager.searchTerm);
            // อัปเดต select options ในฟอร์มบันทึกพฤติกรรม
            updateViolationSelects();
        } else {
            showError(data.message || 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล');
        }
    })
    .catch(error => {
        console.error('Error updating violation type:', error);
        showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
    })
    .finally(() => {
        // รีเซ็ตปุ่มส่งข้อมูล
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}