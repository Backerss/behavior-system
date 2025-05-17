/**
 * JavaScript สำหรับจัดการประเภทพฤติกรรม
 * ใช้งานกับ Modal: violationTypesModal
 */

document.addEventListener('DOMContentLoaded', function() {
    // ตัวแปรสำหรับเก็บข้อมูลและสถานะต่าง ๆ
    const violationManager = {
        currentPage: 1,
        totalPages: 1,
        searchTerm: '',
        violations: [],
        isLoading: false,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    };

    // ปรับปรุงฟังก์ชัน fetchViolations
    function fetchViolations(page = 1, search = '') {
        violationManager.isLoading = true;
        showLoading('violationTypesList');

        // เพิ่ม timestamp เพื่อป้องกัน caching
        const url = `/api/violations?page=${page}&search=${encodeURIComponent(search)}&_=${Date.now()}`;
        
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // ตรวจสอบ content type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(`Response is not JSON (${response.status}: ${response.statusText})`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                violationManager.violations = data.data.data || [];
                violationManager.currentPage = data.data.current_page || 1;
                violationManager.totalPages = data.data.last_page || 1;
                
                renderViolationsList();
                renderPagination();
            } else {
                showError(data.message || 'ไม่สามารถดึงข้อมูลประเภทพฤติกรรมได้');
                console.error('API Error:', data);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์ กรุณาลองใหม่อีกครั้ง');
        })
        .finally(() => {
            violationManager.isLoading = false;
            hideLoading('violationTypesList');
        });
    }

    // เพิ่มฟังก์ชันแสดงข้อมูลตัวอย่าง
    function useMockData() {
        // ข้อมูลตัวอย่าง
        violationManager.violations = [
            {
                violations_id: 1,
                violations_name: 'ผิดระเบียบการแต่งกาย',
                violations_category: 'medium',
                violations_points_deducted: 5,
                violations_description: 'นักเรียนแต่งกายไม่ถูกระเบียบตามข้อกำหนดของโรงเรียน'
            },
            {
                violations_id: 2,
                violations_name: 'มาสาย',
                violations_category: 'light',
                violations_points_deducted: 3,
                violations_description: 'นักเรียนมาโรงเรียนหลังเวลา 08:00 น.'
            },
            {
                violations_id: 3,
                violations_name: 'ทะเลาะวิวาท',
                violations_category: 'severe',
                violations_points_deducted: 20,
                violations_description: 'นักเรียนก่อเหตุทะเลาะวิวาท ทำร้ายร่างกายผู้อื่น'
            }
        ];
        
        violationManager.currentPage = 1;
        violationManager.totalPages = 1;
        
        renderViolationsList();
        renderPagination();
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

    // ฟังก์ชันสำหรับแสดง loading state
    function showLoading(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // ตรวจสอบว่ามี loading อยู่แล้วหรือไม่
        if (container.querySelector('.loading-container')) {
            return; // ถ้ามีอยู่แล้วให้ไม่ต้องสร้างใหม่
        }
        
        // ล้างเนื้อหาเดิมทั้งหมด (รวมถึง loading เก่า)
        const tableContent = container.querySelector('.table-responsive');
        const paginationContent = container.querySelector('nav');
        
        if (tableContent) {
            tableContent.style.display = 'none';
        }
        
        if (paginationContent) {
            paginationContent.style.display = 'none';
        }
        
        // เพิ่ม container ที่มี z-index สูง และตำแหน่งชัดเจน
        const loadingHTML = `
            <div class="loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-white bg-opacity-75">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">กำลังโหลดข้อมูล</p>
            </div>
        `;
        
        const loadingEl = document.createElement('div');
        loadingEl.classList.add('loading-container', 'position-relative');
        loadingEl.style.zIndex = '1050';
        loadingEl.innerHTML = loadingHTML;
        
        // เพิ่มเข้าไปที่ส่วนบนสุดของ container
        container.prepend(loadingEl);
        
        // แสดง progress bar ด้านบนของหน้า
        showProgressBar();
    }

    // ฟังก์ชันสำหรับซ่อน loading
    function hideLoading(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // แสดงเนื้อหาเดิมกลับมา
        const tableContent = container.querySelector('.table-responsive');
        const paginationContent = container.querySelector('nav');
        
        if (tableContent) {
            tableContent.style.display = '';
        }
        
        if (paginationContent) {
            paginationContent.style.display = '';
        }
        
        // ลบ loading ทั้งหมด
        const loadingElements = container.querySelectorAll('.loading-container');
        loadingElements.forEach(el => {
            container.removeChild(el);
        });
        
        // ซ่อน progress bar
        hideProgressBar();
    }

    // แสดง progress bar แบบเดียวกับที่ YouTube, GitHub หรือ LinkedIn ใช้
    function showProgressBar() {
        // ลบตัวเก่าถ้ามี
        const existingBar = document.getElementById('top-progress-bar');
        if (existingBar) existingBar.remove();
        
        const progressBar = document.createElement('div');
        progressBar.id = 'top-progress-bar';
        progressBar.classList.add('progress-bar-top', 'progress-bar-animated');
        document.body.appendChild(progressBar);
        
        // จำลองความคืบหน้า
        setTimeout(() => { progressBar.style.width = '30%'; }, 100);
        setTimeout(() => { progressBar.style.width = '50%'; }, 300);
        setTimeout(() => { progressBar.style.width = '70%'; }, 600);
        setTimeout(() => { progressBar.style.width = '90%'; }, 900);
    }

    // ซ่อน progress bar
    function hideProgressBar() {
        const progressBar = document.getElementById('top-progress-bar');
        if (!progressBar) return;
        
        // ทำให้ progress bar วิ่งถึง 100% ก่อนหายไป
        progressBar.style.width = '100%';
        
        setTimeout(() => {
            progressBar.style.opacity = '0';
            setTimeout(() => {
                if (progressBar.parentNode) {
                    progressBar.parentNode.removeChild(progressBar);
                }
            }, 300);
        }, 200);
    }

    // รวมฟังก์ชันอื่นๆที่เกี่ยวข้อง...

    // โหลดข้อมูลประเภทพฤติกรรมเมื่อ modal แสดง
    const violationTypesModal = document.getElementById('violationTypesModal');
    if (violationTypesModal) {
        violationTypesModal.addEventListener('shown.bs.modal', function() {
            fetchViolations();
        });
    }
    
    // สำหรับอัปเดต select box ประเภทพฤติกรรม
    const newViolationModal = document.getElementById('newViolationModal');
    if (newViolationModal) {
        newViolationModal.addEventListener('show.bs.modal', function() {
            updateViolationSelects();
        });
    }
    
    // เพิ่มฟังก์ชัน updateViolationSelects()
    function updateViolationSelects() {
        fetch('/api/violations/all')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const violations = data.data;
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
            })
            .catch(error => {
                console.error('Error fetching violations for select:', error);
            });
    }
});