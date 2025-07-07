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
        abortController: null,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    };

    // ปรับปรุงฟังก์ชัน fetchViolations
    function fetchViolations(page = 1, search = '') {
        // ยกเลิก request เก่าถ้ามี
        if (violationManager.abortController) {
            violationManager.abortController.abort();
        }
        
        violationManager.isLoading = true;
        violationManager.abortController = new AbortController();
        showLoading('violationTypesList');

        // เพิ่ม timestamp เพื่อป้องกัน caching
        const url = `/api/violations?page=${page}&search=${encodeURIComponent(search)}&_=${Date.now()}`;
        
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: violationManager.abortController.signal
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
            // ตรวจสอบว่าเป็นการยกเลิก request หรือไม่
            if (error.name === 'AbortError') {
                console.log('Request was cancelled');
                return;
            }
            
            console.error('Fetch Error:', error);
            console.warn('Using mock data due to API error');
            
            // ใช้ข้อมูลตัวอย่างแทน
            useMockData();
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
                    <td colspan="5" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-info-circle fa-3x mb-3 text-secondary"></i>
                            <h6 class="mb-2">ไม่พบข้อมูลประเภทพฤติกรรม</h6>
                            <p class="mb-0 small">ยังไม่มีการเพิ่มประเภทพฤติกรรม หรือลองค้นหาด้วยคำอื่น</p>
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
            
            switch (violation.violations_category) {
                case 'light':
                    categoryBadge = '<span class="badge bg-success">เบา</span>';
                    break;
                case 'medium':
                    categoryBadge = '<span class="badge bg-warning">ปานกลาง</span>';
                    break;
                case 'severe':
                    categoryBadge = '<span class="badge bg-danger">รุนแรง</span>';
                    break;
                default:
                    categoryBadge = '<span class="badge bg-secondary">ไม่ระบุ</span>';
            }
            
            // สร้าง row
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <strong>${violation.violations_name}</strong>
                </td>
                <td class="text-center">${categoryBadge}</td>
                <td class="text-center">
                    <span class="badge bg-primary rounded-pill">${violation.violations_points_deducted} คะแนน</span>
                </td>
                <td>
                    <small class="text-muted">${violation.violations_description || '-'}</small>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary edit-violation-btn" 
                                data-id="${violation.violations_id}" 
                                data-name="${violation.violations_name}"
                                title="แก้ไข">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger delete-violation-btn" 
                                data-id="${violation.violations_id}" 
                                data-name="${violation.violations_name}"
                                title="ลบ">
                            <i class="fas fa-trash-alt"></i>
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

    // ฟังก์ชันสำหรับเพิ่ม event listeners ให้ปุ่มแก้ไข
    function attachEditButtonListeners() {
        document.querySelectorAll('.edit-violation-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const violationId = this.getAttribute('data-id');
                editViolation(violationId);
            });
        });
    }

    // ฟังก์ชันสำหรับเพิ่ม event listeners ให้ปุ่มลบ
    function attachDeleteButtonListeners() {
        document.querySelectorAll('.delete-violation-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const violationId = this.getAttribute('data-id');
                const violationName = this.getAttribute('data-name');
                deleteViolation(violationId, violationName);
            });
        });
    }

    // ฟังก์ชันสำหรับแก้ไขประเภทพฤติกรรม
    function editViolation(violationId) {
        const violation = violationManager.violations.find(v => v.violations_id == violationId);
        if (!violation) {
            showError('ไม่พบข้อมูลประเภทพฤติกรรม');
            return;
        }

        // เติมข้อมูลลงใน modal (ใช้ modal เดียวกับการเพิ่มใหม่)
        const violationIdInput = document.getElementById('violation_id');
        const violationNameInput = document.getElementById('violation_name');
        const violationCategoryInput = document.getElementById('violation_category');
        const violationPointsInput = document.getElementById('violation_points');
        const violationDescriptionInput = document.getElementById('violation_description');

        if (violationIdInput) violationIdInput.value = violation.violations_id;
        if (violationNameInput) violationNameInput.value = violation.violations_name;
        if (violationCategoryInput) violationCategoryInput.value = violation.violations_category;
        if (violationPointsInput) violationPointsInput.value = violation.violations_points_deducted;
        if (violationDescriptionInput) violationDescriptionInput.value = violation.violations_description || '';

        // เปลี่ยน title modal
        const modalTitle = document.querySelector('#addViolationTypeModal .modal-title');
        if (modalTitle) modalTitle.textContent = 'แก้ไขประเภทพฤติกรรม';

        // แสดง modal
        const modal = new bootstrap.Modal(document.getElementById('addViolationTypeModal'));
        modal.show();
    }

    // ฟังก์ชันสำหรับลบประเภทพฤติกรรม
    function deleteViolation(violationId, violationName) {
        if (!confirm(`คุณต้องการลบประเภทพฤติกรรม "${violationName}" หรือไม่?`)) {
            return;
        }

        // เรียก API ลบข้อมูล
        fetch(`/api/violations/${violationId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('ลบประเภทพฤติกรรมสำเร็จ');
                fetchViolations(); // รีโหลดข้อมูล
            } else {
                showError(data.message || 'ไม่สามารถลบประเภทพฤติกรรมได้');
            }
        })
        .catch(error => {
            console.error('Delete Error:', error);
            showError('เกิดข้อผิดพลาดในการลบข้อมูล');
        });
    }

    // ฟังก์ชันสำหรับแสดง loading state
    function showLoading(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // ซ่อนตาราง
        const tableResponsive = container.querySelector('.table-responsive');
        const pagination = container.querySelector('nav');
        
        if (tableResponsive) tableResponsive.style.display = 'none';
        if (pagination) pagination.style.display = 'none';
        
        // ลบ loading เก่าถ้ามี
        const existingLoading = container.querySelector('.loading-state');
        if (existingLoading) existingLoading.remove();
        
        // สร้าง loading state ใหม่
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'loading-state text-center py-5';
        loadingDiv.innerHTML = `
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mb-0">กำลังโหลดข้อมูล...</p>
        `;
        
        container.appendChild(loadingDiv);
    }

    // ฟังก์ชันสำหรับซ่อน loading
    function hideLoading(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // ลบ loading state ทั้งหมด
        const loadingStates = container.querySelectorAll('.loading-state');
        loadingStates.forEach(loading => loading.remove());
        
        // ลบ loading container เก่าๆ ถ้ามี
        const loadingContainers = container.querySelectorAll('.loading-container');
        loadingContainers.forEach(loading => loading.remove());
        
        // แสดงตารางและ pagination
        const tableResponsive = container.querySelector('.table-responsive');
        const pagination = container.querySelector('nav');
        
        if (tableResponsive) tableResponsive.style.display = '';
        if (pagination) pagination.style.display = '';
    }

    // ฟังก์ชันสำหรับสร้างปุ่ม pagination
    function renderPagination() {
        const paginationContainer = document.querySelector('#violationTypesList nav ul.pagination');
        if (!paginationContainer) return;

        paginationContainer.innerHTML = '';

        if (violationManager.totalPages <= 1) {
            return; // ไม่ต้องแสดง pagination ถ้ามีหน้าเดียว
        }

        // ปุ่ม Previous
        const prevDisabled = violationManager.currentPage === 1;
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${prevDisabled ? 'disabled' : ''}`;
        prevLi.innerHTML = `
            <a class="page-link" href="#" data-page="${violationManager.currentPage - 1}">
                <i class="fas fa-chevron-left"></i>
            </a>
        `;
        if (!prevDisabled) {
            prevLi.addEventListener('click', function(e) {
                e.preventDefault();
                if (violationManager.currentPage > 1) {
                    fetchViolations(violationManager.currentPage - 1, violationManager.searchTerm);
                }
            });
        }
        paginationContainer.appendChild(prevLi);

        // ปุ่มหมายเลขหน้า
        const startPage = Math.max(1, violationManager.currentPage - 2);
        const endPage = Math.min(violationManager.totalPages, violationManager.currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${i === violationManager.currentPage ? 'active' : ''}`;
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            
            if (i !== violationManager.currentPage) {
                pageLi.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchViolations(i, violationManager.searchTerm);
                });
            }
            paginationContainer.appendChild(pageLi);
        }

        // ปุ่ม Next
        const nextDisabled = violationManager.currentPage === violationManager.totalPages;
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${nextDisabled ? 'disabled' : ''}`;
        nextLi.innerHTML = `
            <a class="page-link" href="#" data-page="${violationManager.currentPage + 1}">
                <i class="fas fa-chevron-right"></i>
            </a>
        `;
        if (!nextDisabled) {
            nextLi.addEventListener('click', function(e) {
                e.preventDefault();
                if (violationManager.currentPage < violationManager.totalPages) {
                    fetchViolations(violationManager.currentPage + 1, violationManager.searchTerm);
                }
            });
        }
        paginationContainer.appendChild(nextLi);
    }

    // รวมฟังก์ชันอื่นๆที่เกี่ยวข้อง...

    // โหลดข้อมูลประเภทพฤติกรรมเมื่อ modal แสดง
    const violationTypesModal = document.getElementById('violationTypesModal');
    if (violationTypesModal) {
        violationTypesModal.addEventListener('shown.bs.modal', function() {
            fetchViolations();
        });
        
        // เพิ่ม event listener สำหรับการปิด modal
        violationTypesModal.addEventListener('hidden.bs.modal', function() {
            // ยกเลิก request ที่กำลังทำงานอยู่
            if (violationManager.abortController) {
                violationManager.abortController.abort();
                violationManager.abortController = null;
            }
            
            // ล้างข้อมูลและ state
            violationManager.violations = [];
            violationManager.currentPage = 1;
            violationManager.totalPages = 1;
            violationManager.searchTerm = '';
            violationManager.isLoading = false;
            
            // ล้างช่องค้นหา
            const searchInput = document.getElementById('violationTypeSearch');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // ล้างตาราง
            const tableBody = document.querySelector('#violationTypesList table tbody');
            if (tableBody) {
                tableBody.innerHTML = '';
            }
            
            // ล้าง pagination
            const paginationContainer = document.querySelector('#violationTypesList nav ul.pagination');
            if (paginationContainer) {
                paginationContainer.innerHTML = '';
            }
            
            // ลบ loading state ถ้ามี
            hideLoading('violationTypesList');
            
            // Force cleanup สำหรับป้องกัน UI ค้าง
            setTimeout(() => {
                forceCleanupModal();
            }, 100);
        });
    }

    // เพิ่ม event listener สำหรับการค้นหา
    const searchInput = document.getElementById('violationTypeSearch');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            searchTimeout = setTimeout(() => {
                violationManager.searchTerm = searchTerm;
                violationManager.currentPage = 1; // รีเซ็ตกลับหน้าแรก
                fetchViolations(1, searchTerm);
            }, 300); // หน่วงเวลา 300ms
        });
    }

    // เพิ่ม event listener สำหรับปุ่มเพิ่มประเภทพฤติกรรม
    const btnShowAddViolationType = document.getElementById('btnShowAddViolationType');
    if (btnShowAddViolationType) {
        btnShowAddViolationType.addEventListener('click', function() {
            // เปิด modal เพิ่มประเภทพฤติกรรม
            const addModal = new bootstrap.Modal(document.getElementById('addViolationTypeModal'));
            addModal.show();
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

// สำหรับเปิด modal เพิ่มประเภทพฤติกรรมใหม่
document.addEventListener('DOMContentLoaded', function() {
    // ปุ่มเพิ่มประเภทพฤติกรรมใหม่
    const btnShowAddViolationType = document.getElementById('btnShowAddViolationType');
    
    if (btnShowAddViolationType) {
        btnShowAddViolationType.addEventListener('click', function() {
            // ปิด modal รายการพฤติกรรม
            const violationTypesModal = bootstrap.Modal.getInstance(document.getElementById('violationTypesModal'));
            if (violationTypesModal) {
                violationTypesModal.hide();
                
                // รอให้ modal เดิมปิดก่อน แล้วจึงเปิด modal ใหม่
                setTimeout(() => {
                    // รีเซ็ตฟอร์ม
                    const form = document.getElementById('addViolationTypeForm');
                    if (form) form.reset();
                    
                    // ซ่อนข้อความแจ้งเตือนต่างๆ
                    document.querySelector('#addViolationTypeModal .save-success')?.classList.add('d-none');
                    document.querySelector('#addViolationTypeModal .save-error')?.classList.add('d-none');
                    
                    // เปิด modal ใหม่
                    const addViolationTypeModal = new bootstrap.Modal(document.getElementById('addViolationTypeModal'));
                    addViolationTypeModal.show();
                }, 150);
            }
        });
    }
    
    // ปุ่มบันทึกข้อมูลประเภทพฤติกรรม
    const btnSaveViolationType = document.getElementById('btnSaveViolationType');
    if (btnSaveViolationType) {
        btnSaveViolationType.addEventListener('click', function() {
            saveNewViolationType();
        });
    }
    
    // ตรวจสอบเมื่อกดปิด modal เพิ่มพฤติกรรม ให้กลับไปเปิด modal รายการพฤติกรรม
    const addViolationTypeModal = document.getElementById('addViolationTypeModal');
    if (addViolationTypeModal) {
        addViolationTypeModal.addEventListener('hidden.bs.modal', function() {
            setTimeout(() => {
                const violationTypesModal = new bootstrap.Modal(document.getElementById('violationTypesModal'));
                violationTypesModal.show();
                
                // โหลดข้อมูลรายการประเภทพฤติกรรมใหม่
                fetchViolations();
            }, 150);
        });
    }
});

// ฟังก์ชันสำหรับบันทึกประเภทพฤติกรรมใหม่
function saveNewViolationType() {
    const form = document.getElementById('addViolationTypeForm');
    
    // ตรวจสอบความถูกต้องของฟอร์ม
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    // ปิดการแสดงข้อความแจ้งเตือนเก่า
    document.querySelector('#addViolationTypeModal .save-success').classList.add('d-none');
    document.querySelector('#addViolationTypeModal .save-error').classList.add('d-none');
    
    // แสดงสถานะกำลังบันทึก
    const btnSave = document.getElementById('btnSaveViolationType');
    const originalText = btnSave.innerHTML;
    btnSave.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังบันทึก...`;
    btnSave.disabled = true;
    
    // สร้าง FormData จากฟอร์ม
    const formData = new FormData(form);
    
    // ตรวจสอบว่าเป็นการแก้ไขหรือเพิ่มใหม่
    const violationId = formData.get('id');
    const isEditing = violationId && violationId.trim() !== '';
    
    // แปลงชื่อฟิลด์ให้ตรงกับที่ API คาดหวัง
    const data = {
        violations_name: formData.get('name'),
        violations_category: formData.get('category'),
        violations_points_deducted: parseInt(formData.get('points_deducted')) || 0,
        violations_description: formData.get('description') || ''
    };
    
    // กำหนด URL และ method ตามการใช้งาน
    const url = isEditing ? `/api/violations/${violationId}` : '/api/violations';
    const method = isEditing ? 'PUT' : 'POST';
    
    // ส่งข้อมูลไปบันทึก
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 422) {
                // กรณี validation error
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'ข้อมูลไม่ถูกต้องตามเงื่อนไข');
                });
            }
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // แสดงข้อความบันทึกสำเร็จ
            const successElement = document.querySelector('#addViolationTypeModal .save-success');
            if (successElement) {
                successElement.textContent = isEditing ? 'แก้ไขข้อมูลสำเร็จ' : 'บันทึกข้อมูลสำเร็จ';
                successElement.classList.remove('d-none');
            }
            
            // รีเฟรชรายการประเภทพฤติกรรม
            fetchViolations();
            
            // รีเซ็ตฟอร์ม
            form.reset();
            form.classList.remove('was-validated');
            
            // หน่วงเวลาสักครู่แล้วปิด modal
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('addViolationTypeModal')).hide();
            }, 1500);
        } else {
            throw new Error(data.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
        }
    })
    .catch(error => {
        // แสดงข้อความผิดพลาด
        const errorMessageElement = document.querySelector('#addViolationTypeModal .error-message');
        errorMessageElement.textContent = error.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
        document.querySelector('#addViolationTypeModal .save-error').classList.remove('d-none');
        console.error('Error saving violation type:', error);
    })
    .finally(() => {
        // คืนสถานะปุ่มบันทึก
        btnSave.innerHTML = originalText;
        btnSave.disabled = false;
    });
}

// ฟังก์ชันสำหรับแสดงข้อความผิดพลาด
function showError(message) {
    // สร้าง toast notification สำหรับข้อผิดพลาด
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toastHtml = `
        <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // ลบ toast หลังจากซ่อน
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// ฟังก์ชันสำหรับแสดงข้อความสำเร็จ
function showSuccess(message) {
    // สร้าง toast notification สำหรับความสำเร็จ
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toastHtml = `
        <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // ลบ toast หลังจากซ่อน
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// ฟังก์ชันสำหรับสร้าง toast container
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}

// เพิ่มฟังก์ชันสำหรับทำความสะอาด modal และ loading state ที่อาจค้างอยู่
function forceCleanupModal() {
    // ลบ modal backdrop ที่อาจค้างอยู่
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    
    // ลบ modal-open class จาก body
    document.body.classList.remove('modal-open');
    
    // รีเซ็ต body style
    document.body.style.paddingRight = '';
    document.body.style.overflow = '';
    
    // ลบ loading element ทั้งหมดที่อาจค้างอยู่
    const loadingElements = document.querySelectorAll('.loading-state, .loading-container, .loading-overlay');
    loadingElements.forEach(el => el.remove());
}

// เพิ่ม event listener สำหรับรีเซ็ต modal เมื่อปิด
document.addEventListener('DOMContentLoaded', function() {
    const addViolationModal = document.getElementById('addViolationTypeModal');
    if (addViolationModal) {
        addViolationModal.addEventListener('hidden.bs.modal', function () {
            // รีเซ็ต title กลับเป็นเดิม
            const modalTitle = this.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'เพิ่มประเภทพฤติกรรมใหม่';
            
            // รีเซ็ตฟอร์ม
            const form = this.querySelector('#addViolationTypeForm');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
                
                // ล้าง violation_id สำหรับการเพิ่มใหม่
                const violationIdInput = document.getElementById('violation_id');
                if (violationIdInput) violationIdInput.value = '';
            }
            
            // ซ่อนข้อความแจ้งเตือน
            const successAlert = this.querySelector('.save-success');
            const errorAlert = this.querySelector('.save-error');
            if (successAlert) successAlert.classList.add('d-none');
            if (errorAlert) errorAlert.classList.add('d-none');
        });
    }
});