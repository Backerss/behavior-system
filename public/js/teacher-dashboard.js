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
    
    // ตรวจสอบว่า DOM พร้อมแล้ว
    setTimeout(() => {
        // เช็คว่าองค์ประกอบสำคัญโหลดแล้วหรือยัง
        const overviewSection = document.getElementById('overview');
        const statCards = document.querySelectorAll('#overview .stat-card');
    
        
        if (statCards.length >= 4) {
            // โหลดข้อมูลสถิติประจำเดือน
            loadMonthlyStats();
        } else {
            console.warn('Stat cards not found, retrying in 1 second...');
            setTimeout(() => {
                loadMonthlyStats();
            }, 1000);
        }
        
        // Initialize charts
        loadViolationTrendChart();
        loadViolationTypesChart();
        
    }, 100); // รอ 100ms ให้ DOM โหลดเสร็จ
    
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
    const violationTypesList = document.getElementById('violationTypesList');
    const violationTypeForm = document.getElementById('violationTypeForm');
    const btnCloseViolationForm = document.getElementById('btnCloseViolationForm');
    const btnCancelViolationType = document.getElementById('btnCancelViolationType');
    const formViolationTitle = document.getElementById('formViolationTitle');
    const studentSearch = document.getElementById('studentSearch');
    const btnSearchStudent = document.getElementById('btnSearchStudent');
    const classFilter = document.getElementById('classFilter');
    
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
    
    // โหลดข้อมูลสถิติประจำเดือน
    loadMonthlyStats();
});

// ฟังก์ชันโหลดสถิติประจำเดือน
function loadMonthlyStats() {
    fetch('/api/dashboard/stats', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            updateStatsDisplay(result.data);
        } else {
            console.error('Error loading monthly stats:', result.message);
        }
    })
    .catch(error => {
        console.error('Error fetching monthly stats:', error);
    });
}

// ฟังก์ชันอัปเดตการแสดงข้อมูลสถิติ
function updateStatsDisplay(stats) {
    try {
        // อัปเดตจำนวนพฤติกรรม
        const violationValueEl = document.querySelector('#overview .stat-card:nth-child(2) .stat-value');
        if (violationValueEl) {
            violationValueEl.textContent = stats.violation_count || 0;
        }
        
        // อัปเดตแนวโน้มจำนวนพฤติกรรม
        const violationTrendEl = document.querySelector('#overview .stat-card:nth-child(2) .stat-change');
        if (violationTrendEl) {
            violationTrendEl.className = 'stat-change mb-0 ' + 
                (stats.violation_trend > 0 ? 'increase' : (stats.violation_trend < 0 ? 'decrease' : 'no-change'));
            violationTrendEl.innerHTML = `
                <i class="fas fa-${stats.violation_trend > 0 ? 'arrow-up' : (stats.violation_trend < 0 ? 'arrow-down' : 'equals')} me-1"></i>
                ${Math.abs(stats.violation_trend)}% จากเดือนก่อน
            `;
        }
        
        // อัปเดตจำนวนนักเรียนที่ถูกบันทึก
        const studentsValueEl = document.querySelector('#overview .stat-card:nth-child(3) .stat-value');
        if (studentsValueEl) {
            studentsValueEl.textContent = stats.students_count || 0;
        }
        
        // อัปเดตแนวโน้มจำนวนนักเรียน
        const studentsTrendEl = document.querySelector('#overview .stat-card:nth-child(3) .stat-change');
        if (studentsTrendEl) {
            studentsTrendEl.className = 'stat-change mb-0 ' + 
                (stats.students_trend > 0 ? 'increase' : (stats.students_trend < 0 ? 'decrease' : 'no-change'));
            studentsTrendEl.innerHTML = `
                <i class="fas fa-${stats.students_trend > 0 ? 'arrow-up' : (stats.students_trend < 0 ? 'arrow-down' : 'equals')} me-1"></i>
                ${Math.abs(stats.students_trend)}% จากเดือนก่อน
            `;
        }
        
        // อัปเดตจำนวนพฤติกรรมรุนแรง
        const severeValueEl = document.querySelector('#overview .stat-card:nth-child(4) .stat-value');
        if (severeValueEl) {
            severeValueEl.textContent = stats.severe_count || 0;
        }
        
        // อัปเดตแนวโน้มพฤติกรรมรุนแรง
        const severeTrendEl = document.querySelector('#overview .stat-card:nth-child(4) .stat-change');
        if (severeTrendEl) {
            severeTrendEl.className = 'stat-change mb-0 ' + 
                (stats.severe_trend > 0 ? 'increase' : (stats.severe_trend < 0 ? 'decrease' : 'no-change'));
            severeTrendEl.innerHTML = `
                <i class="fas fa-${stats.severe_trend > 0 ? 'arrow-up' : (stats.severe_trend < 0 ? 'arrow-down' : 'equals')} me-1"></i>
                ${Math.abs(stats.severe_trend)}% จากเดือนก่อน
            `;
        }
        
        // อัปเดตคะแนนเฉลี่ย
        const scoreValueEl = document.querySelector('#overview .stat-card:nth-child(5) .stat-value');
        if (scoreValueEl) {
            scoreValueEl.textContent = stats.avg_score.toFixed(1);
        }
        
        // อัปเดตแนวโน้มคะแนนเฉลี่ย
        const scoreTrendEl = document.querySelector('#overview .stat-card:nth-child(5) .stat-change');
        if (scoreTrendEl) {
            scoreTrendEl.className = 'stat-change mb-0 ' + 
                (stats.score_trend > 0 ? 'increase' : (stats.score_trend < 0 ? 'decrease' : 'no-change'));
            scoreTrendEl.innerHTML = `
                <i class="fas fa-${stats.score_trend > 0 ? 'arrow-up' : (stats.score_trend < 0 ? 'arrow-down' : 'equals')} me-1"></i>
                ${Math.abs(stats.score_trend)} คะแนนจากเดือนก่อน
            `;
        }
    
        
    } catch (error) {
        console.error('Error updating stats display:', error);
        console.log('Available elements:', {
            violationValue: !!document.querySelector('#overview .stat-card:nth-child(2) .stat-value'),
            studentsValue: !!document.querySelector('#overview .stat-card:nth-child(3) .stat-value'),
            severeValue: !!document.querySelector('#overview .stat-card:nth-child(4) .stat-value'),
            scoreValue: !!document.querySelector('#overview .stat-card:nth-child(5) .stat-value')
        });
    }
}

// ปรับปรุงฟังก์ชันโหลดกราฟแนวโน้ม
function loadViolationTrendChart() {
    const ctx = document.getElementById('violationTrend');
    if (!ctx) return;
    
    // แสดง Loading indicator
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'chart-loading';
    loadingDiv.innerHTML = `
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">กำลังโหลด...</span>
        </div>
        <span class="ms-2">กำลังโหลดข้อมูล...</span>
    `;
    ctx.parentNode.insertBefore(loadingDiv, ctx);
    
    fetch('/api/dashboard/trends', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        // ลบ Loading indicator
        if (loadingDiv) loadingDiv.remove();
        
        if (result.success) {
            initViolationTrendChart(result.data);
        } else {
            console.error('Error loading trend data:', result.message);
            initViolationTrendChart(null); // ใช้ข้อมูลเริ่มต้น
        }
    })
    .catch(error => {
        // ลบ Loading indicator
        if (loadingDiv) loadingDiv.remove();
        
        console.error('Error fetching trend data:', error);
        initViolationTrendChart(null); // ใช้ข้อมูลเริ่มต้น
    });
}

// ปรับปรุงฟังก์ชันโหลดกราฟประเภทพฤติกรรม
function loadViolationTypesChart() {
    const ctx = document.getElementById('violationTypes');
    if (!ctx) return;
    
    // แสดง Loading indicator
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'chart-loading';
    loadingDiv.innerHTML = `
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">กำลังโหลด...</span>
        </div>
        <span class="ms-2">กำลังโหลดข้อมูล...</span>
    `;
    ctx.parentNode.insertBefore(loadingDiv, ctx);
    
    fetch('/api/dashboard/violations', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(result => {
        // ลบ Loading indicator
        if (loadingDiv) loadingDiv.remove();
        
        if (result.success) {
            initViolationTypesChart(result.data);
        } else {
            console.error('Error loading violation types data:', result.message);
            initViolationTypesChart(null); // ใช้ข้อมูลเริ่มต้น
        }
    })
    .catch(error => {
        // ลบ Loading indicator
        if (loadingDiv) loadingDiv.remove();
        
        console.error('Error fetching violation types data:', error);
        initViolationTypesChart(null); // ใช้ข้อมูลเริ่มต้น
    });
}

// ปรับปรุงฟังก์ชันสร้างกราฟแนวโน้ม
function initViolationTrendChart(chartData) {
    const ctx = document.getElementById('violationTrend');
    if (!ctx) return;
    
    // ลบกราฟเดิมถ้ามี
    if (window.violationTrendChart instanceof Chart) {
        window.violationTrendChart.destroy();
    }
    
    // ถ้าไม่มีข้อมูล ใช้ข้อมูลเริ่มต้น
    if (!chartData) {
        chartData = {
            labels: ['1', '5', '10', '15', '20', '25', '30'],
            datasets: [{
                label: 'พฤติกรรมที่ถูกบันทึก',
                data: [0, 0, 0, 0, 0, 0, 0],
                borderColor: 'rgb(16, 32, 173)',
                backgroundColor: 'rgba(16, 32, 173, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };
    }
    
    // สร้างกราฟใหม่
    window.violationTrendChart = new Chart(ctx, {
        type: 'line',
        data: chartData,
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

// ปรับปรุงฟังก์ชันสร้างกราฟประเภทพฤติกรรม
function initViolationTypesChart(chartData) {
    const ctx = document.getElementById('violationTypes');
    if (!ctx) return;
    
    // ลบกราฟเดิมถ้ามี
    if (window.violationTypesChart instanceof Chart) {
        window.violationTypesChart.destroy();
    }
    
    // ถ้าไม่มีข้อมูล ใช้ข้อมูลเริ่มต้น
    if (!chartData) {
        chartData = {
            labels: [
                'ไม่มีข้อมูล'
            ],
            datasets: [{
                data: [1],
                backgroundColor: [
                    '#6c757d'
                ],
                borderWidth: 0
            }]
        };
    }
    
    // สร้างกราฟใหม่
    window.violationTypesChart = new Chart(ctx, {
        type: 'doughnut',
        data: chartData,
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

// เพิ่มฟังก์ชัน saveViolationType ก่อนการปิด document.addEventListener('DOMContentLoaded', function() { ... });

// ฟังก์ชันบันทึกประเภทพฤติกรรม
function saveViolationType() {
    const form = document.getElementById('formViolationType');
    if (!form) return;
    
    // ล้าง validation errors ถ้ามี
    form.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
    
    // แสดง loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> กำลังบันทึก...';
    
    // ดึงข้อมูลจากฟอร์ม
    const formData = new FormData(form);
    const violationId = document.getElementById('editViolationId')?.value;
    const isUpdate = violationId && violationId !== '';
    
    // แปลง FormData เป็น object
    const data = {
        violations_name: formData.get('violations_name'),
        violations_category: formData.get('violations_category'),
        violations_points_deducted: parseInt(formData.get('violations_points_deducted')),
        violations_description: formData.get('violations_description') || ''
    };
    
    // กำหนด HTTP method และ URL ตามการใช้งาน (สร้างใหม่/แก้ไข)
    const method = isUpdate ? 'PUT' : 'POST';
    const url = isUpdate ? `/api/violations/${violationId}` : '/api/violations';
    
    // ส่งข้อมูลไปยัง API
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            showSuccess(isUpdate ? 'อัปเดตประเภทพฤติกรรมเรียบร้อยแล้ว' : 'เพิ่มประเภทพฤติกรรมเรียบร้อยแล้ว');
            
            // ซ่อนฟอร์มและแสดงรายการ
            const violationTypeForm = document.getElementById('violationTypeForm');
            const violationTypesList = document.getElementById('violationTypesList');
            
            if (violationTypeForm) violationTypeForm.classList.add('d-none');
            if (violationTypesList) violationTypesList.classList.remove('d-none');
            
            // โหลดรายการประเภทพฤติกรรมใหม่อีกครั้ง
            if (typeof fetchViolations === 'function') {
                fetchViolations();
            }
        } else {
            showError(result.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
        }
    })
    .catch(error => {
        console.error('Error saving violation type:', error);
        
        // แสดงข้อความผิดพลาดจาก validation
        if (error.errors) {
            Object.keys(error.errors).forEach(key => {
                const element = form.querySelector(`[name="${key}"]`);
                if (element) {
                    element.classList.add('is-invalid');
                    const feedback = element.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = error.errors[key][0];
                    }
                }
            });
        } else {
            showError('เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + error.message);
        }
    })
    .finally(() => {
        // คืนสถานะปุ่ม
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// เพิ่มเข้าไปในส่วนท้ายของ document.addEventListener('DOMContentLoaded', function() { ... });

// ฟังก์ชันสำหรับการค้นหานักเรียนในตารางหน้าหลัก
function setupMainStudentSearch() {
    const searchInput = document.querySelector('#students .input-group input[placeholder="ค้นหานักเรียน..."]');
    const searchButton = document.querySelector('#students .input-group button');
    
    if (searchInput && searchButton) {
        // เมื่อกดปุ่มค้นหา
        searchButton.addEventListener('click', function() {
            filterStudentsInTable(searchInput.value);
        });
        
        // เมื่อกด Enter ในช่องค้นหา
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterStudentsInTable(this.value);
            }
        });
        
        // ตั้งค่าเริ่มต้นให้สามารถค้นหาทันทีเมื่อพิมพ์เสร็จ (debounce)
        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                filterStudentsInTable(this.value);
            }, 500);
        });
    }
}

// ฟังก์ชันกรองข้อมูลนักเรียนในตารางหน้าหลัก
function filterStudentsInTable(searchTerm) {
    // ล้างไฮไลท์เดิม (ถ้ามี)
    document.querySelectorAll('#students .table tbody tr .highlight').forEach(el => {
        el.outerHTML = el.innerHTML;
    });
    
    // ดึงตาราง
    const tableRows = document.querySelectorAll('#students .table tbody tr');
    if (!tableRows.length) return;
    
    // กรณีไม่มีข้อมูลค้นหา แสดงทุกแถว (ลบตัว s ที่ไม่เกี่ยวข้องออก)
    if (!searchTerm || searchTerm.trim() === '') {
        tableRows.forEach(row => {
            row.style.display = '';
        });
        return;
    }
    
    // ทำการค้นหาแบบไม่สนใจตัวพิมพ์เล็ก-ใหญ่
    searchTerm = searchTerm.toLowerCase();
    let matchCount = 0;
    
    tableRows.forEach(row => {
        // ดึงข้อมูลที่ต้องการค้นหา: รหัสนักเรียน, ชื่อ-นามสกุล, ชั้นเรียน
        const studentId = row.querySelector('td:nth-child(1)')?.textContent || '';
        const studentName = row.querySelector('td:nth-child(2) span')?.textContent || '';
        const className = row.querySelector('td:nth-child(3)')?.textContent || '';
        
        // รวมข้อความทั้งหมดที่ต้องการค้นหา
        const allText = `${studentId} ${studentName} ${className}`.toLowerCase();
        
        // ถ้าพบคำค้นหาในข้อความ
        if (allText.includes(searchTerm)) {
            row.style.display = '';
            matchCount++;
            
            // ไฮไลท์คำที่ค้นหา
            highlightSearchTerm(row, searchTerm);
        } else {
            row.style.display = 'none';
        }
    });
    
    // แสดงข้อความถ้าไม่พบข้อมูล
    if (matchCount === 0 && tableRows.length > 0) {
        const firstRow = tableRows[0].parentElement;
        
        // ตรวจสอบว่ามีแถว "ไม่พบข้อมูล" อยู่แล้วหรือไม่
        const noDataRow = document.getElementById('no-search-results');
        if (!noDataRow) {
            const newRow = document.createElement('tr');
            newRow.id = 'no-search-results';
            newRow.innerHTML = `<td colspan="6" class="text-center py-4">
                <div class="text-muted">
                    <i class="fas fa-search fa-2x mb-3"></i>
                    <p>ไม่พบนักเรียนที่ตรงกับคำค้นหา "${searchTerm}"</p>
                </div>
            </td>`;
            firstRow.appendChild(newRow);
        } else {
            noDataRow.style.display = '';
            noDataRow.querySelector('p').textContent = `ไม่พบนักเรียนที่ตรงกับคำค้นหา "${searchTerm}"`;
        }
    } else {
        // ซ่อนข้อความ "ไม่พบข้อมูล" ถ้ามี
        const noDataRow = document.getElementById('no-search-results');
        if (noDataRow) {
            noDataRow.style.display = 'none';
        }
    }
}

// ฟังก์ชันไฮไลท์คำที่ค้นหา
function highlightSearchTerm(row, searchTerm) {
    // ไฮไลท์ในคอลัมน์รหัสนักเรียน
    const idCell = row.querySelector('td:nth-child(1)');
    if (idCell) {
        idCell.innerHTML = idCell.textContent.replace(new RegExp(`(${searchTerm})`, 'gi'), 
            '<span class="highlight bg-warning bg-opacity-50">$1</span>');
    }
    
    // ไฮไลท์ในคอลัมน์ชื่อ-นามสกุล
    const nameCell = row.querySelector('td:nth-child(2) span');
    if (nameCell) {
        nameCell.innerHTML = nameCell.textContent.replace(new RegExp(`(${searchTerm})`, 'gi'), 
            '<span class="highlight bg-warning bg-opacity-50">$1</span>');
    }
    
    // ไฮไลท์ในคอลัมน์ชั้นเรียน
    const classCell = row.querySelector('td:nth-child(3)');
    if (classCell) {
        classCell.innerHTML = classCell.textContent.replace(new RegExp(`(${searchTerm})`, 'gi'), 
            '<span class="highlight bg-warning bg-opacity-50">$1</span>');
    }
}

// เรียกใช้ฟังก์ชันเมื่อโหลดหน้าเสร็จ
document.addEventListener('DOMContentLoaded', function() {
    setupMainStudentSearch();
    setupStudentDetailModal();
    setupNewViolationModal();
});

// ===== Student Detail Modal Functions =====

let currentStudentId = null;

/**
 * ตั้งค่า Student Detail Modal
 */
function setupStudentDetailModal() {
    // เพิ่ม event listener สำหรับปุ่มดูข้อมูลนักเรียน
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-bs-target="#studentDetailModal"]')) {
            const button = e.target.closest('[data-bs-target="#studentDetailModal"]');
            const studentId = button.getAttribute('data-student-id');
            if (studentId) {
                currentStudentId = studentId;
                loadStudentDetail(studentId);
            }
        }
    });

    // เพิ่ม event listener สำหรับการปิด modal
    const modal = document.getElementById('studentDetailModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            resetStudentDetailModal();
        });
    }
}

/**
 * โหลดข้อมูลนักเรียนจาก API
 */
async function loadStudentDetail(studentId) {
    showStudentDetailLoading();
    
    try {
        const response = await fetch(`/api/students/${studentId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            populateStudentDetail(data.student);
            showStudentDetailContent();
        } else {
            throw new Error(data.message || 'ไม่สามารถโหลดข้อมูลได้');
        }
    } catch (error) {
        console.error('Error loading student detail:', error);
        showStudentDetailError(error.message);
    }
}

/**
 * แสดงสถานะ Loading
 */
function showStudentDetailLoading() {
    document.getElementById('studentDetailLoading').style.display = 'block';
    document.getElementById('studentDetailContent').style.display = 'none';
    document.getElementById('studentDetailError').style.display = 'none';
}

/**
 * แสดงข้อมูลนักเรียน
 */
function showStudentDetailContent() {
    document.getElementById('studentDetailLoading').style.display = 'none';
    document.getElementById('studentDetailContent').style.display = 'block';
    document.getElementById('studentDetailError').style.display = 'none';
}

/**
 * แสดงข้อผิดพลาด
 */
function showStudentDetailError(message) {
    document.getElementById('studentDetailLoading').style.display = 'none';
    document.getElementById('studentDetailContent').style.display = 'none';
    const errorElement = document.getElementById('studentDetailError');
    errorElement.style.display = 'block';
    
    const errorText = errorElement.querySelector('p');
    if (errorText) {
        errorText.textContent = message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล';
    }
}

/**
 * เติมข้อมูลลงใน Modal
 */
function populateStudentDetail(student) {
    // รูปโปรไฟล์
    const profileImg = document.getElementById('studentProfileImage');
    if (profileImg) {
        const studentName = `${student.user.users_first_name} ${student.user.users_last_name}`;
        if (student.user.users_profile_image) {
            profileImg.src = `/storage/${student.user.users_profile_image}`;
        } else {
            profileImg.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(studentName)}&background=95A4D8&color=fff`;
        }
        profileImg.alt = `รูปโปรไฟล์ ${studentName}`;
    }

    // ชื่อเต็ม
    const fullNameElement = document.getElementById('studentFullName');
    if (fullNameElement) {
        fullNameElement.textContent = `${student.user.users_name_prefix}${student.user.users_first_name} ${student.user.users_last_name}`;
    }

    // ชั้นเรียน (Badge)
    const classBadge = document.getElementById('studentClassBadge');
    if (classBadge) {
        if (student.classroom) {
            classBadge.textContent = `${student.classroom.classes_level}/${student.classroom.classes_room_number}`;
        } else {
            classBadge.textContent = 'ไม่ระบุชั้นเรียน';
        }
    }

    // รหัสนักเรียน
    const studentCodeElement = document.getElementById('studentCode');
    if (studentCodeElement) {
        studentCodeElement.textContent = student.students_student_code || 'ไม่ระบุ';
    }

    // ชั้นเรียน
    const studentClassElement = document.getElementById('studentClass');
    if (studentClassElement) {
        if (student.classroom) {
            studentClassElement.textContent = `${student.classroom.classes_level}/${student.classroom.classes_room_number}`;
        } else {
            studentClassElement.textContent = 'ไม่ระบุชั้นเรียน';
        }
    }

    // เลขบัตรประชาชน
    const idNumberElement = document.getElementById('studentIdNumber');
    if (idNumberElement) {
        idNumberElement.textContent = student.id_number || 'ไม่ระบุ';
    }

    // วันเกิด
    const birthdateElement = document.getElementById('studentBirthdate');
    if (birthdateElement) {
        if (student.user.users_birthdate) {
            const birthDate = new Date(student.user.users_birthdate);
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            birthdateElement.textContent = birthDate.toLocaleDateString('th-TH', options);
        } else {
            birthdateElement.textContent = 'ไม่ระบุ';
        }
    }

    // ชื่อผู้ปกครอง
    const guardianNameElement = document.getElementById('guardianName');
    if (guardianNameElement) {
        if (student.guardian && student.guardian.user) {
            guardianNameElement.textContent = `${student.guardian.user.users_name_prefix}${student.guardian.user.users_first_name} ${student.guardian.user.users_last_name}`;
        } else {
            guardianNameElement.textContent = 'ไม่มีข้อมูล';
        }
    }

    // เบอร์โทรผู้ปกครอง
    const guardianPhoneElement = document.getElementById('guardianPhone');
    if (guardianPhoneElement) {
        if (student.guardian && student.guardian.guardians_phone) {
            guardianPhoneElement.textContent = student.guardian.guardians_phone;
        } else {
            guardianPhoneElement.textContent = 'ไม่มีข้อมูล';
        }
    }

    // คะแนนความประพฤติ
    updateScoreDisplay(student.students_current_score || 100);

    // ประวัติการกระทำผิด
    populateBehaviorHistory(student.behavior_reports || []);

    // ตั้งค่าวงเงินคะแนน
    const scoreLimitElement = document.getElementById('scoreLimit');
    if (scoreLimitElement) {
        scoreLimitElement.textContent = `วงเงินคะแนน: ${student.students_score_limit || 100} คะแนน`;
    }

    // ตั้งค่าปุ่มพิมพ์รายงาน
    const printBtn = document.getElementById('printReportBtn');
    if (printBtn) {
        printBtn.setAttribute('data-student-id', student.students_id);
    }
}

/**
 * อัปเดตการแสดงคะแนน
 */
function updateScoreDisplay(score) {
    const progressBar = document.getElementById('scoreProgressBar');
    const scoreIcon = document.getElementById('scoreIcon');
    
    if (progressBar) {
        progressBar.style.width = `${score}%`;
        progressBar.textContent = `${score}/100`;
        
        // เปลี่ยนสีตามคะแนน
        progressBar.className = 'progress-bar';
        if (score >= 80) {
            progressBar.classList.add('bg-success');
        } else if (score >= 60) {
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.add('bg-danger');
        }
    }

    if (scoreIcon) {
        // ปรับตำแหน่งไอคอนตามคะแนน
        scoreIcon.style.left = `calc(${score}% - 20px)`;
    }
}

/**
 * เติมประวัติการกระทำผิด
 */
function populateBehaviorHistory(behaviorReports) {
    const tableBody = document.getElementById('behaviorHistoryTable');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (behaviorReports.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted py-3">
                    <i class="fas fa-info-circle me-2"></i>ไม่มีประวัติการกระทำผิด
                </td>
            </tr>
        `;
        return;
    }

    behaviorReports.forEach(report => {
        const reportDate = new Date(report.reports_report_date);
        const formattedDate = reportDate.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        const row = `
            <tr>
                <td>${formattedDate}</td>
                <td>
                    <span class="badge bg-secondary">${escapeHtml(report.violation.violations_name)}</span>
                </td>
                <td class="text-danger fw-bold">-${report.violation.violations_points_deducted}</td>
                <td>${escapeHtml(report.teacher.user.users_first_name)} ${escapeHtml(report.teacher.user.users_last_name)}</td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', row);
    });
}

/**
 * รีเซ็ต Modal
 */
function resetStudentDetailModal() {
    currentStudentId = null;
    showStudentDetailLoading();
}

/**
 * ลองโหลดข้อมูลใหม่
 */
function retryLoadStudentDetail() {
    if (currentStudentId) {
        loadStudentDetail(currentStudentId);
    }
}

/**
 * เปิด New Violation Modal (สำหรับปุ่มบันทึกพฤติกรรม)
 */
function openNewViolationModal() {
    // ปิด Student Detail Modal ก่อน
    const studentModal = bootstrap.Modal.getInstance(document.getElementById('studentDetailModal'));
    if (studentModal) {
        studentModal.hide();
    }

    // เปิด New Violation Modal
    setTimeout(() => {
        const newViolationModal = new bootstrap.Modal(document.getElementById('newViolationModal'));
        newViolationModal.show();
        
        // ถ้ามีข้อมูลนักเรียนปัจจุบัน ให้เลือกนักเรียนคนนั้นใน modal
        if (currentStudentId) {
            selectStudentInViolationModal(currentStudentId);
        }
    }, 300);
}

// ===== New Violation Modal Functions =====

let violationStudents = [];
let violationTypes = [];
let selectedStudent = null;

/**
 * ตั้งค่า New Violation Modal
 */
function setupNewViolationModal() {
    // โหลดข้อมูลเริ่มต้น
    loadViolationTypes();
    loadClassroomsForFilter();
    
    // ตั้งค่าวันที่เริ่มต้น
    setupViolationDateRestriction();
    
    // ตั้งค่า Event Listeners
    setupViolationEventListeners();
}

/**
 * โหลดประเภทพฤติกรรมจาก API
 */
async function loadViolationTypes() {
    try {
        const response = await fetch('/api/violations/all', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        violationTypes = data;
        populateViolationTypeSelect();
    } catch (error) {
        console.error('Error loading violation types:', error);
    }
}

/**
 * เติมข้อมูลประเภทพฤติกรรมลงใน select
 */
function populateViolationTypeSelect() {
    const select = document.getElementById('violationType');
    if (!select) return;

    // Clear existing options except the first one
    select.innerHTML = '<option value="">เลือกประเภทพฤติกรรม</option>';

    violationTypes.forEach(violation => {
        const option = document.createElement('option');
        option.value = violation.violations_id;
        option.textContent = `${violation.violations_name} (-${violation.violations_points_deducted} คะแนน)`;
        option.dataset.points = violation.violations_points_deducted;
        select.appendChild(option);
    });
}

/**
 * โหลดห้องเรียนสำหรับตัวกรอง
 */
async function loadClassroomsForFilter() {
    try {
        // ใช้ข้อมูลจาก dashboard หรือดึงจาก API
        const classFilter = document.getElementById('classFilter');
        if (!classFilter) return;

        // เพิ่มตัวเลือกพื้นฐาน
        classFilter.innerHTML = '<option value="">ทุกห้อง</option>';
        
        // ในอนาคตสามารถดึงจาก API ได้
        // const response = await fetch('/api/classrooms');
    } catch (error) {
        console.error('Error loading classrooms:', error);
    }
}

/**
 * ตั้งค่าวันที่เริ่มต้นและจำกัดวันที่
 */
function setupViolationDateRestriction() {
    const dateInput = document.getElementById('violationDate');
    const timeInput = document.getElementById('violationTime');
    
    if (dateInput) {
        const today = new Date();
        const threeDaysAgo = new Date();
        threeDaysAgo.setDate(today.getDate() - 3);
        
        dateInput.valueAsDate = today;
        dateInput.min = threeDaysAgo.toISOString().split('T')[0];
        dateInput.max = today.toISOString().split('T')[0];
    }
    
    if (timeInput) {
        const now = new Date();
        timeInput.value = now.toTimeString().slice(0, 5);
    }
}

/**
 * ตั้งค่า Event Listeners สำหรับ New Violation Modal
 */
function setupViolationEventListeners() {
    // ค้นหานักเรียน
    const studentSearch = document.getElementById('behaviorStudentSearch');
    if (studentSearch) {
        let searchTimeout;
        studentSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    searchStudentsForViolation(query);
                }, 300);
            } else {
                hideStudentResults();
            }
        });
    }

    // เปลี่ยนประเภทพฤติกรรม
    const violationTypeSelect = document.getElementById('violationType');
    if (violationTypeSelect) {
        violationTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const pointsInput = document.getElementById('pointsDeducted');
            
            if (pointsInput && selectedOption.dataset.points) {
                pointsInput.value = selectedOption.dataset.points;
            }
        });
    }

    // บันทึกข้อมูล
    const saveBtn = document.getElementById('saveViolationBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            submitViolationForm();
        });
    }

    // รีเซ็ตเมื่อปิด modal
    const modal = document.getElementById('newViolationModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            resetViolationForm();
        });
    }
}

/**
 * ค้นหานักเรียนสำหรับ violation modal
 */
async function searchStudentsForViolation(query) {
    try {
        const response = await fetch(`/api/behavior-reports/students/search?term=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        displayStudentResults(data);
    } catch (error) {
        console.error('Error searching students:', error);
        // Fallback: ใช้ข้อมูลจากตารางหลัก
        searchStudentsFromTable(query);
    }
}

/**
 * แสดงผลการค้นหานักเรียน
 */
function displayStudentResults(students) {
    const resultsContainer = document.getElementById('studentResults');
    if (!resultsContainer) return;

    resultsContainer.innerHTML = '';
    
    if (students.length === 0) {
        resultsContainer.innerHTML = '<div class="list-group-item text-muted">ไม่พบนักเรียน</div>';
        resultsContainer.style.display = 'block';
        return;
    }

    students.forEach(student => {
        const item = document.createElement('div');
        item.className = 'list-group-item list-group-item-action cursor-pointer';
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>${student.users_name_prefix}${student.users_first_name} ${student.users_last_name}</strong>
                    <br>
                    <small class="text-muted">รหัส: ${student.students_student_code} | ชั้น: ${student.classroom_info || 'ไม่ระบุ'}</small>
                </div>
                <span class="badge bg-primary">${student.students_current_score || 100} คะแนน</span>
            </div>
        `;
        
        item.addEventListener('click', () => {
            selectStudentForViolation(student);
        });
        
        resultsContainer.appendChild(item);
    });
    
    resultsContainer.style.display = 'block';
}

/**
 * เลือกนักเรียนสำหรับบันทึกพฤติกรรม
 */
function selectStudentForViolation(student) {
    selectedStudent = student;
    
    // อัปเดต hidden input
    const hiddenInput = document.getElementById('selectedStudentId');
    if (hiddenInput) {
        hiddenInput.value = student.students_id;
    }
    
    // แสดงข้อมูลนักเรียนที่เลือก
    const infoDisplay = document.getElementById('studentInfoDisplay');
    const infoContainer = document.getElementById('selectedStudentInfo');
    
    if (infoDisplay && infoContainer) {
        infoDisplay.innerHTML = `
            <strong>${student.users_name_prefix}${student.users_first_name} ${student.users_last_name}</strong>
            (รหัส: ${student.students_student_code}) | ชั้น: ${student.classroom_info || 'ไม่ระบุ'} | 
            คะแนนปัจจุบัน: <span class="badge bg-primary">${student.students_current_score || 100}</span>
        `;
        infoContainer.style.display = 'block';
    }
    
    // ซ่อนผลการค้นหาและล้างช่องค้นหา
    hideStudentResults();
    const searchInput = document.getElementById('behaviorStudentSearch');
    if (searchInput) {
        searchInput.value = `${student.users_first_name} ${student.users_last_name}`;
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
 * เลือกนักเรียนจาก currentStudentId (จาก Student Detail Modal)
 */
function selectStudentInViolationModal(studentId) {
    // ค้นหานักเรียนจากตารางหลักหรือจาก API
    // ในอนาคตสามารถปรับปรุงให้ดึงจาก API ได้
    console.log('Selecting student ID:', studentId);
}

/**
 * ค้นหานักเรียนจากตารางหลัก (Fallback)
 */
function searchStudentsFromTable(query) {
    const tableRows = document.querySelectorAll('#students tbody tr');
    const results = [];
    
    tableRows.forEach(row => {
        const nameCell = row.querySelector('td:nth-child(2)');
        const codeCell = row.querySelector('td:nth-child(1)');
        
        if (nameCell && codeCell) {
            const name = nameCell.textContent.toLowerCase();
            const code = codeCell.textContent.toLowerCase();
            
            if (name.includes(query.toLowerCase()) || code.includes(query.toLowerCase())) {
                // Extract student data from table
                const studentData = {
                    students_id: row.querySelector('[data-student-id]')?.getAttribute('data-student-id') || '',
                    users_first_name: nameCell.textContent.split(' ')[0] || '',
                    users_last_name: nameCell.textContent.split(' ').slice(1).join(' ') || '',
                    users_name_prefix: '',
                    students_student_code: codeCell.textContent,
                    classroom_info: row.querySelector('td:nth-child(3)')?.textContent || '',
                    students_current_score: '100'
                };
                results.push(studentData);
            }
        }
    });
    
    displayStudentResults(results);
}

/**
 * ส่งข้อมูลฟอร์ม
 */
async function submitViolationForm() {
    if (!validateViolationForm()) {
        return;
    }
    
    const saveBtn = document.getElementById('saveViolationBtn');
    const originalText = saveBtn.innerHTML;
    
    try {
        // แสดง loading
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> กำลังบันทึก...';
        saveBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('student_id', document.getElementById('selectedStudentId').value);
        formData.append('violation_id', document.getElementById('violationType').value);
        formData.append('violation_date', document.getElementById('violationDate').value);
        formData.append('violation_time', document.getElementById('violationTime').value);
        formData.append('description', document.getElementById('violationDescription').value);
        
        const evidenceFile = document.getElementById('evidenceFile').files[0];
        if (evidenceFile) {
            formData.append('evidence', evidenceFile);
        }
        
        const response = await fetch('/api/behavior-reports', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // แสดงข้อความสำเร็จ
            alert('บันทึกพฤติกรรมเรียบร้อยแล้ว');
            
            // ปิด modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('newViolationModal'));
            if (modal) {
                modal.hide();
            }
            
            // รีเฟรชข้อมูลในหน้า
            location.reload();
        } else {
            throw new Error(data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
        }
        
    } catch (error) {
        console.error('Error submitting violation form:', error);
        alert('เกิดข้อผิดพลาด: ' + error.message);
    } finally {
        // คืนค่าปุ่ม
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    }
}

/**
 * ตรวจสอบความถูกต้องของฟอร์ม
 */
function validateViolationForm() {
    const studentId = document.getElementById('selectedStudentId').value;
    const violationType = document.getElementById('violationType').value;
    const violationDate = document.getElementById('violationDate').value;
    const violationTime = document.getElementById('violationTime').value;
    
    if (!studentId) {
        alert('กรุณาเลือกนักเรียน');
        return false;
    }
    
    if (!violationType) {
        alert('กรุณาเลือกประเภทพฤติกรรม');
        return false;
    }
    
    if (!violationDate) {
        alert('กรุณาเลือกวันที่');
        return false;
    }
    
    if (!violationTime) {
        alert('กรุณาเลือกเวลา');
        return false;
    }
    
    return true;
}

/**
 * รีเซ็ตฟอร์ม
 */
function resetViolationForm() {
    selectedStudent = null;
    
    // รีเซ็ตฟิลด์ต่างๆ
    document.getElementById('behaviorStudentSearch').value = '';
    document.getElementById('selectedStudentId').value = '';
    document.getElementById('violationType').value = '';
    document.getElementById('pointsDeducted').value = '0';
    document.getElementById('violationDescription').value = '';
    document.getElementById('evidenceFile').value = '';
    
    // ซ่อนข้อมูลนักเรียน
    document.getElementById('selectedStudentInfo').style.display = 'none';
    hideStudentResults();
    
    // รีเซ็ตวันที่และเวลา
    setupViolationDateRestriction();
}