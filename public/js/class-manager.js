/**
 * JavaScript สำหรับจัดการห้องเรียน
 * ใช้งานกับ Modal: classManagementModal
 */

document.addEventListener('DOMContentLoaded', function() {
    // แก้ปัญหา ARIA accessibility ใน Bootstrap Modals
    document.querySelectorAll('.modal').forEach(modalElement => {
        modalElement.addEventListener('show.bs.modal', function() {
            this.removeAttribute('aria-hidden');
            this.setAttribute('aria-modal', 'true');
            this.setAttribute('role', 'dialog');
        });
        
        modalElement.addEventListener('hidden.bs.modal', function() {
            this.setAttribute('aria-hidden', 'true');
            this.removeAttribute('aria-modal');
        });
    });
    
    // ตัวแปรสำหรับเก็บข้อมูลและสถานะต่าง ๆ
    const classManager = {
        currentPage: 1,
        totalPages: 1,
        searchTerm: '',
        classes: [],
        teachers: [],
        isLoading: false,
        filters: {
            academicYear: '',
            level: ''
        },
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    // เชื่อมต่อกับ Elements ต่าง ๆ
    const classroomList = document.getElementById('classroomList');
    const classroomForm = document.getElementById('classroomForm');
    const btnShowAddClass = document.getElementById('btnShowAddClass');
    const btnCloseClassForm = document.getElementById('btnCloseClassForm');
    const btnCancelClass = document.getElementById('btnCancelClass');
    const formClassroom = document.getElementById('formClassroom');
    const classroomSearch = document.getElementById('classroomSearch');
    const btnSearchClass = document.getElementById('btnSearchClass');
    const confirmDeleteClass = document.getElementById('confirmDeleteClass');
    const filterAcademicYear = document.getElementById('filterAcademicYear');
    const filterLevel = document.getElementById('filterLevel');
    const btnApplyFilter = document.getElementById('btnApplyFilter');

    // ฟังก์ชันแสดงรายการห้องเรียน
    function fetchClassrooms(page = 1, search = '', filters = {}) {
        classManager.isLoading = true;
        showLoading('classroomList');

        // สร้าง URL พร้อม query parameters
        let url = `/api/classes?page=${page}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (filters.academicYear) url += `&academicYear=${encodeURIComponent(filters.academicYear)}`;
        if (filters.level) url += `&level=${encodeURIComponent(filters.level)}`;
        
        // เรียก API จริง
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                classManager.classes = data.data.data;
                classManager.currentPage = data.data.current_page;
                classManager.totalPages = data.data.last_page;
                
                renderClassroomList();
                renderPagination();
            } else {
                showError(data.message || 'ไม่สามารถดึงข้อมูลห้องเรียนได้');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
        })
        .finally(() => {
            classManager.isLoading = false;
            hideLoading('classroomList');
        });
    }

    // ฟังก์ชันแสดงรายการห้องเรียนในตาราง
    function renderClassroomList() {
        const tableBody = document.querySelector('#classroomList table tbody');
        
        if (!tableBody) return;
        
        if (classManager.classes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>ไม่พบข้อมูลห้องเรียน</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        tableBody.innerHTML = '';
        
        classManager.classes.forEach(classroom => {
            // ตรวจสอบข้อมูลครูอย่างละเอียด
            let teacherName = 'ไม่ระบุ';
            let teacherAvatar = `https://ui-avatars.com/api/?name=Teacher&background=95A4D8&color=fff`;
            
            // เพิ่ม console.log เพื่อดูค่า
            console.log("Classroom data:", classroom);
            console.log("Teacher data:", classroom.teacher);
            console.log("Teacher user data:", classroom.teacher.users_id);
            if (classroom.teacher && classroom.teacher.user) {
                const teacher = classroom.teacher.user;
                // ตรวจสอบว่ามีข้อมูลครูครบหรือไม่
                if (teacher.users_first_name) {
                    teacherName = `${teacher.users_name_prefix || ''}${teacher.users_first_name} ${teacher.users_last_name || ''}`;
                    teacherAvatar = teacher.users_profile_image ?
                        `/storage/${teacher.users_profile_image}` :
                        `https://ui-avatars.com/api/?name=${encodeURIComponent(teacherName)}&background=95A4D8&color=fff`;
                }
            }
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${classroom.classes_level}/${classroom.classes_room_number}</td>
                <td>${classroom.classes_academic_year}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${teacherAvatar}" class="rounded-circle me-2" width="32" height="32" alt="${teacherName}">
                        <span>${teacherName}</span>
                    </div>
                </td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary view-class-btn" data-id="${classroom.classes_id}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary edit-class-btn" data-id="${classroom.classes_id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-class-btn" data-id="${classroom.classes_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            tableBody.appendChild(row);
        });
        
        // เพิ่ม event listeners สำหรับปุ่ม
        attachViewButtonListeners();
        attachEditButtonListeners();
        attachDeleteButtonListeners();
    }

    // ฟังก์ชันแสดง pagination
    function renderPagination() {
        const pagination = document.querySelector('#classroomList nav ul');
        
        if (!pagination) return;
        
        pagination.innerHTML = '';
        
        if (classManager.totalPages <= 1) return;
        
        // ปุ่ม Previous
        const prevLi = document.createElement('li');
        prevLi.classList.add('page-item');
        if (classManager.currentPage === 1) {
            prevLi.classList.add('disabled');
        }
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${classManager.currentPage - 1}">Previous</a>`;
        pagination.appendChild(prevLi);
        
        // หน้าต่าง ๆ
        for (let i = 1; i <= classManager.totalPages; i++) {
            const pageLi = document.createElement('li');
            pageLi.classList.add('page-item');
            if (i === classManager.currentPage) {
                pageLi.classList.add('active');
            }
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            pagination.appendChild(pageLi);
        }
        
        // ปุ่ม Next
        const nextLi = document.createElement('li');
        nextLi.classList.add('page-item');
        if (classManager.currentPage === classManager.totalPages) {
            nextLi.classList.add('disabled');
        }
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${classManager.currentPage + 1}">Next</a>`;
        pagination.appendChild(nextLi);
        
        // เพิ่ม event listeners สำหรับ pagination
        document.querySelectorAll('#classroomList nav ul li:not(.disabled) a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                fetchClassrooms(page, classManager.searchTerm, classManager.filters);
            });
        });
    }

    // ฟังก์ชันโหลดข้อมูลตัวกรอง (ปีการศึกษาและระดับชั้น)
    function fetchFilters() {
        fetch('/api/classes/filters/all', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // เติมข้อมูลปีการศึกษา
                if (data.data.academicYears && data.data.academicYears.length > 0) {
                    const academicYearSelect = document.getElementById('filterAcademicYear');
                    data.data.academicYears.forEach(year => {
                        const option = new Option(year, year);
                        academicYearSelect.add(option);
                    });
                    
                    // เติมข้อมูลในฟอร์มเพิ่มด้วย
                    const formAcademicYearSelect = document.getElementById('classes_academic_year');
                    if (formAcademicYearSelect) {
                        // ล้างตัวเลือกเดิมยกเว้นตัวแรก
                        while (formAcademicYearSelect.options.length > 1) {
                            formAcademicYearSelect.remove(1);
                        }
                        
                        data.data.academicYears.forEach(year => {
                            const option = new Option(year, year);
                            formAcademicYearSelect.add(option);
                        });
                    }
                }
                
                // เติมข้อมูลระดับชั้น
                if (data.data.levels && data.data.levels.length > 0) {
                    const levelSelect = document.getElementById('filterLevel');
                    data.data.levels.forEach(level => {
                        const option = new Option(level, level);
                        levelSelect.add(option);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error fetching filters:', error);
        });
    }

    // ฟังก์ชันโหลดข้อมูลครูทั้งหมด
    function fetchTeachers() {
        fetch('/api/classes/teachers/all', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                classManager.teachers = data.data;
                
                // เติมข้อมูลในตัวเลือกครูประจำชั้น
                const teacherSelect = document.getElementById('teacher_id');
                if (teacherSelect) {
                    // ล้างตัวเลือกเดิมยกเว้นตัวแรก
                    while (teacherSelect.options.length > 1) {
                        teacherSelect.remove(1);
                    }
                    
                    classManager.teachers.forEach(teacher => {
                        const teacherName = `${teacher.users_name_prefix}${teacher.users_first_name} ${teacher.users_last_name}`;
                        const option = new Option(teacherName, teacher.teachers_id);
                        teacherSelect.add(option);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error fetching teachers:', error);
        });
    }

    // ฟังก์ชันแสดงข้อมูลห้องเรียน
    function fetchClassroomById(classId) {
        return fetch(`/api/classes/${classId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'ไม่พบข้อมูลห้องเรียน');
            }
        });
    }

    // ฟังก์ชันบันทึกข้อมูลห้องเรียน
    function saveClassroom(formData) {
        const classId = formData.get('classes_id');
        const isUpdate = classId && classId !== '';
        
        // แสดง loading
        const saveBtn = document.getElementById('btnSaveClass');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังบันทึก...';
        
        // กำหนดวิธีการส่งข้อมูลและ URL
        const method = isUpdate ? 'PUT' : 'POST';
        const url = isUpdate ? `/api/classes/${classId}` : '/api/classes';
        
        // แปลง FormData เป็น Object
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        // ส่งข้อมูลไปยัง API
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': classManager.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showSuccess(result.message || (isUpdate ? 'แก้ไขข้อมูลห้องเรียนเรียบร้อยแล้ว' : 'เพิ่มห้องเรียนใหม่เรียบร้อยแล้ว'));
                
                // ซ่อนฟอร์ม และแสดงรายการ
                classroomForm.classList.add('d-none');
                classroomList.classList.remove('d-none');
                formClassroom.reset();
                
                // โหลดข้อมูลใหม่
                fetchClassrooms(classManager.currentPage, classManager.searchTerm, classManager.filters);
            } else {
                if (result.errors) {
                    // แสดง validation errors
                    Object.entries(result.errors).forEach(([field, messages]) => {
                        const input = document.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedbackElement = input.nextElementSibling;
                            if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                                feedbackElement.textContent = messages[0];
                            }
                        }
                    });
                } else {
                    showError(result.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                }
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

    // ฟังก์ชันลบห้องเรียน
    function deleteClassroom(classId) {
        // แสดง loading
        const deleteBtn = document.getElementById('confirmDeleteClass');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังลบ...';
        
        fetch(`/api/classes/${classId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': classManager.csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // ปิด modal ยืนยันการลบ
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteClassModal'));
                deleteModal.hide();
                
                showSuccess(result.message || 'ลบห้องเรียนเรียบร้อยแล้ว');
                
                // โหลดข้อมูลใหม่
                fetchClassrooms(classManager.currentPage, classManager.searchTerm, classManager.filters);
            } else {
                showError(result.message || 'เกิดข้อผิดพลาดในการลบข้อมูลห้องเรียน');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
        })
        .finally(() => {
            // คืนสถานะปุ่ม
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        });
    }

    // ฟังก์ชันเพิ่ม event listeners สำหรับปุ่มดูข้อมูล
    function attachViewButtonListeners() {
        document.querySelectorAll('.view-class-btn').forEach(button => {
            button.addEventListener('click', function() {
                const classId = this.getAttribute('data-id');
                
                // ดึงข้อมูลห้องเรียน
                fetchClassroomById(classId)
                    .then(classroom => {
                        // แสดงข้อมูลใน modal
                        const classTitle = `${classroom.classes_level}/${classroom.classes_room_number}`;
                        document.querySelector('.class-title').textContent = classTitle;
                        
                        // โหลดข้อมูลนักเรียนในห้องเรียน (ถ้าจำเป็น)
                        loadClassStudents(classId);
                        
                        // แสดง modal
                        const classDetailModal = new bootstrap.Modal(document.getElementById('classDetailModal'));
                        classDetailModal.show();
                        
                        // สร้างกราฟ
                        initClassViolationChart();
                    })
                    .catch(error => {
                        showError(error.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล');
                    });
            });
        });
    }

    // ฟังก์ชันโหลดข้อมูลนักเรียนในห้องเรียน
    function loadClassStudents(classId) {
        // ส่วนนี้สามารถปรับปรุงเพิ่มเติมได้ตามความต้องการ
        fetch(`/api/classes/${classId}/students`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // แสดงข้อมูลนักเรียน
                console.log("Students data loaded", data.data);
                // สามารถเพิ่มโค้ดแสดงผลข้อมูลนักเรียนได้ที่นี่
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
        });
    }

    // ฟังก์ชันเพิ่ม event listeners สำหรับปุ่มแก้ไข
    function attachEditButtonListeners() {
        document.querySelectorAll('.edit-class-btn').forEach(button => {
            button.addEventListener('click', function() {
                const classId = this.getAttribute('data-id');
                
                // แสดง loading ในฟอร์ม
                document.getElementById('classroomList').classList.add('d-none');
                document.getElementById('classroomForm').classList.remove('d-none');
                document.getElementById('formClassTitle').textContent = 'กำลังโหลดข้อมูล...';
                
                // ดึงข้อมูลห้องเรียน
                fetchClassroomById(classId)
                    .then(classroom => {
                        // ล้าง validation errors เดิม
                        formClassroom.querySelectorAll('.is-invalid').forEach(element => {
                            element.classList.remove('is-invalid');
                        });
                        
                        // เติมข้อมูลลงในฟอร์ม
                        document.getElementById('classId').value = classroom.classes_id;
                        document.getElementById('classes_level').value = classroom.classes_level;
                        document.getElementById('classes_room_number').value = classroom.classes_room_number;
                        document.getElementById('classes_academic_year').value = classroom.classes_academic_year;
                        
                        // เลือกครูประจำชั้น (ถ้ามีข้อมูล)
                        const teacherSelect = document.getElementById('teacher_id');
                        if (classroom.teachers_id && teacherSelect) {
                            teacherSelect.value = classroom.teachers_id;
                        }
                        
                        // เปลี่ยนชื่อหัวฟอร์ม
                        document.getElementById('formClassTitle').textContent = 'แก้ไขข้อมูลห้องเรียน';
                    })
                    .catch(error => {
                        showError(error.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล');
                        
                        // กลับไปหน้ารายการ
                        document.getElementById('classroomForm').classList.add('d-none');
                        document.getElementById('classroomList').classList.remove('d-none');
                    });
            });
        });
    }

    // ฟังก์ชันเพิ่ม event listeners สำหรับปุ่มลบ
    function attachDeleteButtonListeners() {
        document.querySelectorAll('.delete-class-btn').forEach(button => {
            button.addEventListener('click', function() {
                const classId = this.getAttribute('data-id');
                document.getElementById('deleteClassId').value = classId;
                
                // หาชื่อห้องเรียนจากข้อมูลที่มีอยู่
                const classroom = classManager.classes.find(c => c.classes_id == classId);
                if (classroom) {
                    document.querySelector('#deleteClassModal .modal-body h5').textContent = 
                        `ยืนยันการลบห้องเรียน "${classroom.classes_level}/${classroom.classes_room_number}"?`;
                }
                
                // แสดง modal ยืนยันการลบ
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteClassModal'));
                deleteModal.show();
            });
        });
    }

    // ฟังก์ชันสร้างกราฟสถิติการกระทำผิด
    function initClassViolationChart() {
        const ctx = document.getElementById('classViolationChart');
        if (!ctx) return;
        
        // ตรวจสอบว่ามีกราฟอยู่แล้วหรือไม่
        if (ctx.chart) {
            ctx.chart.destroy();
        }
        
        // สร้างกราฟใหม่
        ctx.chart = new Chart(ctx, {
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
                    data: [25, 20, 15, 30, 10],
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
                            padding: 10,
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
    
    // ฟังก์ชันแสดง loading
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

    // ฟังก์ชันซ่อน loading
    function hideLoading(containerId) {
        const container = document.getElementById(containerId);
        if (container) {
            const loadingEl = container.querySelector('.loading-container');
            if (loadingEl) {
                container.removeChild(loadingEl);
            }
        }
    }

    // ฟังก์ชันแสดงข้อความสำเร็จ
    function showSuccess(message) {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            // สร้าง toast container ถ้ายังไม่มี
            const newContainer = document.createElement('div');
            newContainer.classList.add('toast-container', 'position-fixed', 'top-0', 'end-0', 'p-3');
            document.body.appendChild(newContainer);
        }
        
        const existingContainer = document.querySelector('.toast-container');
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
        
        existingContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        
        // ลบ toast หลังจากแสดง
        toastEl.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }

    // ฟังก์ชันแสดงข้อความผิดพลาด
    function showError(message) {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            // สร้าง toast container ถ้ายังไม่มี
            const newContainer = document.createElement('div');
            newContainer.classList.add('toast-container', 'position-fixed', 'top-0', 'end-0', 'p-3');
            document.body.appendChild(newContainer);
        }
        
        const existingContainer = document.querySelector('.toast-container');
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
        
        existingContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
        
        // ลบ toast หลังจากแสดง
        toastEl.addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }

    // ล้าง validation errors เมื่อมีการแก้ไขข้อมูลในฟอร์ม
    document.querySelectorAll('#formClassroom input, #formClassroom select').forEach(element => {
        element.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });

    // โหลดข้อมูลห้องเรียนเมื่อ modal แสดง
    const classManagementModal = document.getElementById('classManagementModal');
    if (classManagementModal) {
        classManagementModal.addEventListener('shown.bs.modal', function() {
            fetchClassrooms();
            fetchFilters();
            fetchTeachers();
        });
    }

    // กำหนด event ให้กับปุ่มเพิ่มห้องเรียนใหม่
    if (btnShowAddClass) {
        btnShowAddClass.addEventListener('click', function() {
            // รีเซ็ตฟอร์ม
            formClassroom.reset();
            document.getElementById('classId').value = '';
            document.getElementById('formClassTitle').textContent = 'เพิ่มห้องเรียนใหม่';
            
            // ล้าง validation errors
            formClassroom.querySelectorAll('.is-invalid').forEach(element => {
                element.classList.remove('is-invalid');
            });
            
            // แสดงฟอร์ม ซ่อนรายการ
            classroomList.classList.add('d-none');
            classroomForm.classList.remove('d-none');
        });
    }

    // กำหนด event ให้กับปุ่มปิดฟอร์ม
    if (btnCloseClassForm) {
        btnCloseClassForm.addEventListener('click', function() {
            classroomForm.classList.add('d-none');
            classroomList.classList.remove('d-none');
        });
    }

    // กำหนด event ให้กับปุ่มยกเลิกในฟอร์ม
    if (btnCancelClass) {
        btnCancelClass.addEventListener('click', function() {
            classroomForm.classList.add('d-none');
            classroomList.classList.remove('d-none');
        });
    }

    // กำหนด event ให้กับฟอร์มบันทึกห้องเรียน
    if (formClassroom) {
        formClassroom.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // ล้าง validation errors
            this.querySelectorAll('.is-invalid').forEach(element => {
                element.classList.remove('is-invalid');
            });
            
            // ตรวจสอบความถูกต้องของฟอร์ม
            if (this.checkValidity()) {
                // สร้าง FormData
                const formData = new FormData(this);
                
                // บันทึกข้อมูล
                saveClassroom(formData);
            } else {
                // แสดงข้อความที่ browser validate
                this.classList.add('was-validated');
            }
        });
    }

    // กำหนด event ให้กับช่องค้นหา
    if (classroomSearch) {
        classroomSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                classManager.searchTerm = this.value;
                fetchClassrooms(1, this.value, classManager.filters);
            }
        });
    }

    // กำหนด event ให้กับปุ่มค้นหา
    if (btnSearchClass) {
        btnSearchClass.addEventListener('click', function() {
            classManager.searchTerm = classroomSearch.value;
            fetchClassrooms(1, classroomSearch.value, classManager.filters);
        });
    }

    // กำหนด event ให้กับปุ่มกรองข้อมูล
    if (btnApplyFilter) {
        btnApplyFilter.addEventListener('click', function() {
            classManager.filters.academicYear = filterAcademicYear.value;
            classManager.filters.level = filterLevel.value;
            fetchClassrooms(1, classManager.searchTerm, classManager.filters);
        });
    }

    // กำหนด event ให้กับปุ่มยืนยันการลบ
    if (confirmDeleteClass) {
        confirmDeleteClass.addEventListener('click', function() {
            const classId = document.getElementById('deleteClassId').value;
            if (classId) {
                deleteClassroom(classId);
            }
        });
    }
});