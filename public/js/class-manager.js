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
                
                renderClassroomList(classManager.classes); // Pass classManager.classes here
                renderPagination();
            } else {
                showError(data.message || 'ไม่สามารถดึงข้อมูลห้องเรียนได้');
                renderClassroomList([]); // Pass empty array on error to clear table
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
            renderClassroomList([]); // Pass empty array on error to clear table
        })
        .finally(() => {
            classManager.isLoading = false;
            hideLoading('classroomList');
        });
    }

    // ฟังก์ชันแสดงรายการห้องเรียนในตาราง
    function renderClassroomList(classrooms) {
        const tbody = document.querySelector('#classManagementModal #classroomList .table tbody');
        if (!tbody) {
            console.error('Classroom list table body not found in classManagementModal.');
            return;
        }

        if (!classrooms || !Array.isArray(classrooms) || classrooms.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>ไม่พบข้อมูลห้องเรียน</p>
                        ${classManager.searchTerm ? `<p class="small">คำค้นหา: "${escapeHtml(classManager.searchTerm)}"</p>` : ''}
                        ${(classManager.filters.academicYear || classManager.filters.level) ? `<p class="small">ตัวกรอง: ${classManager.filters.academicYear || 'ทุกปีการศึกษา'}, ${classManager.filters.level || 'ทุกระดับชั้น'}</p>` : ''}

                    </td>
                </tr>
            `;
            return;
        }

        const rowsHtml = classrooms.map(classroom => {
            const classId = classroom.classes_id || '';
            const className = `${classroom.classes_level || 'N/A'}/${classroom.classes_room_number || 'N/A'}`;
            const academicYear = classroom.classes_academic_year || 'N/A';
            // const studentCount = classroom.students_count || 0; // student_count is available if needed in the future

            let teacherName = 'ยังไม่ได้กำหนด';
            if (classroom.teacher && classroom.teacher.user) {
                teacherName = `${classroom.teacher.user.users_name_prefix || ''}${classroom.teacher.user.users_first_name || ''} ${classroom.teacher.user.users_last_name || ''}`.trim();
                if (!teacherName) {
                    teacherName = 'ข้อมูลครูไม่สมบูรณ์';
                }
            } else if (classroom.teacher) {
                teacherName = 'ข้อมูลครูไม่สมบูรณ์ (ขาดข้อมูล user)';
            }

            return `
                <tr>
                    <td>${escapeHtml(className)}</td>
                    <td>${escapeHtml(academicYear)}</td>
                    <td>${escapeHtml(teacherName)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary view-class-btn me-1" data-id="${classId}" title="ดูรายละเอียด">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary edit-class-btn me-1" data-id="${classId}" title="แก้ไข">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-class-btn" data-id="${classId}" title="ลบ">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.innerHTML = rowsHtml;

        if (typeof attachViewButtonListeners === 'function') attachViewButtonListeners();
        if (typeof attachEditButtonListeners === 'function') attachEditButtonListeners();
        if (typeof attachDeleteButtonListeners === 'function') attachDeleteButtonListeners();
    }

    // Add this helper function if it's not already globally available or imported
    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') return '';
        return text.toString().replace(/[&<>"']/g, function (match) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[match];
        });
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
        const viewButtons = document.querySelectorAll('#classManagementModal .view-class-btn');
        viewButtons.forEach(button => {
            const newButton = button.cloneNode(true); // Clone to remove existing listeners
            button.parentNode.replaceChild(newButton, button); // Replace old button with new one

            newButton.addEventListener('click', function() {
                const classId = this.getAttribute('data-id');
                const classDetailModalElement = document.getElementById('classDetailModal');
                
                if (!classDetailModalElement) {
                    console.error('Class Detail Modal (#classDetailModal) not found.');
                    showError('ไม่สามารถเปิดรายละเอียดห้องเรียนได้: ไม่พบ Modal');
                    return;
                }
                const classDetailModal = bootstrap.Modal.getOrCreateInstance(classDetailModalElement);
                
                const classDetailContent = document.getElementById('classDetailContent');
                const classDetailLoading = document.getElementById('classDetailLoading');
                const classTitleSpan = classDetailModalElement.querySelector('.class-title');

                if (classDetailContent) classDetailContent.classList.add('d-none');
                if (classDetailLoading) classDetailLoading.classList.remove('d-none');
                if (classTitleSpan) classTitleSpan.textContent = '';
                
                classDetailModal.show();

                fetchClassroomById(classId)
                    .then(classroom => {
                        // ใช้ฟังก์ชันที่สร้างใน file นี้แทน
                        populateClassDetailModal(classroom);
                        if (classDetailContent) classDetailContent.classList.remove('d-none');
                        if (classDetailLoading) classDetailLoading.classList.add('d-none');
                    })
                    .catch(error => {
                        console.error('Error fetching classroom for detail view:', error);
                        showError('ไม่สามารถโหลดข้อมูลห้องเรียนได้: ' + error.message);
                        if (classDetailLoading) {
                            classDetailLoading.innerHTML = `<div class="text-center py-3"><p class="text-danger">เกิดข้อผิดพลาด: ${escapeHtml(error.message)}</p></div>`;
                        }
                        if (classDetailContent) classDetailContent.classList.add('d-none');
                    });
            });
        });
    }

    // เพิ่มฟังก์ชัน populateClassDetailModal ใน class-manager.js
    function populateClassDetailModal(classroom) {
        const classDetailContent = document.getElementById('classDetailContent');
        const classTitleSpan = document.querySelector('#classDetailModal .class-title');
        
        if (!classDetailContent) {
            console.error('Class detail content container not found');
            return;
        }

        // อัปเดต title
        if (classTitleSpan) {
            classTitleSpan.textContent = `${classroom.classes_level}/${classroom.classes_room_number}`;
        }

        // สร้างข้อมูลครู
        let teacherInfo = 'ยังไม่ได้กำหนด';
        if (classroom.teacher && classroom.teacher.user) {
            teacherInfo = `${classroom.teacher.user.users_name_prefix || ''}${classroom.teacher.user.users_first_name || ''} ${classroom.teacher.user.users_last_name || ''}`.trim();
        }

        // สร้างเนื้อหา modal
        classDetailContent.innerHTML = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title">ข้อมูลพื้นฐาน</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label class="text-muted small">ระดับชั้น</label>
                                    <p class="mb-2 fw-bold">${escapeHtml(classroom.classes_level || 'N/A')}</p>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small">ห้อง</label>
                                    <p class="mb-2 fw-bold">${escapeHtml(classroom.classes_room_number || 'N/A')}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label class="text-muted small">ปีการศึกษา</label>
                                    <p class="mb-2 fw-bold">${escapeHtml(classroom.classes_academic_year || 'N/A')}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label class="text-muted small">ครูประจำชั้น</label>
                                    <p class="mb-0 fw-bold">${escapeHtml(teacherInfo)}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title">สถิติ</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label class="text-muted small">จำนวนนักเรียน</label>
                                    <p class="mb-2 fw-bold text-primary">${classroom.students_count || 0} คน</p>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small">การกระทำผิดในเดือนนี้</label>
                                    <p class="mb-2 fw-bold text-danger">${classroom.violations_this_month || 0} ครั้ง</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="text-muted small">คะแนนเฉลี่ย</label>
                                    <p class="mb-2 fw-bold text-success">${classroom.average_score || 100} คะแนน</p>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small">สถานะ</label>
                                    <span class="badge bg-success">ปกติ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">รายชื่อนักเรียน</h6>
                        <div class="input-group" style="max-width: 200px;">
                            <input type="text" class="form-control form-control-sm" placeholder="ค้นหานักเรียน..." id="studentSearchInClass">
                            <button class="btn btn-outline-secondary btn-sm" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 35%">ชื่อ - นามสกุล</th>
                                    <th style="width: 15%">รหัสนักเรียน</th>
                                    <th style="width: 15%">คะแนนปัจจุบัน</th>
                                    <th style="width: 15%">การกระทำผิดล่าสุด</th>
                                    <th style="width: 15%">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                                <!-- จะถูกเติมด้วย JavaScript -->
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">กำลังโหลด...</span>
                                        </div>
                                        <p class="mt-2 text-muted mb-0">กำลังโหลดรายชื่อนักเรียน...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        // โหลดรายชื่อนักเรียนในห้องเรียน
        loadStudentsInClass(classroom.classes_id);
    }

    // ฟังก์ชันโหลดรายชื่อนักเรียนในห้องเรียน
    function loadStudentsInClass(classId) {
        const studentsTableBody = document.getElementById('studentsTableBody');
        
        if (!studentsTableBody) {
            console.error('studentsTableBody element not found in populateClassDetailModal');
            return;
        }

        // Display loading state within the student table body
        studentsTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-2 text-muted mb-0">กำลังโหลดรายชื่อนักเรียน...</p>
                </td>
            </tr>
        `;

        fetch(`/api/classes/${classId}/students`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                // Try to parse error response body if available
                return response.json().then(errData => {
                    throw new Error(errData.message || `HTTP error! Status: ${response.status}`);
                }).catch(() => {
                    // Fallback if error response is not JSON or empty
                    throw new Error(`HTTP error! Status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Correctly access the student array from data.data.data
                if (data.data && Array.isArray(data.data.data)) {
                    renderStudentsTable(data.data.data);
                } else {
                    // API reported success, but data.data.data is not an array or data.data is missing
                    console.error('Student API response format error: data.data.data is not an array or data.data is missing.', data);
                    throw new Error('รูปแบบข้อมูลนักเรียนที่ได้รับจากเซิร์ฟเวอร์ไม่ถูกต้อง (ไม่พบรายการนักเรียน)');
                }
            } else {
                // API reported failure (data.success is false)
                throw new Error(data.message || 'ไม่สามารถโหลดรายชื่อนักเรียนได้');
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            if (studentsTableBody) {
                studentsTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                            <p class="mb-0">เกิดข้อผิดพลาดในการโหลดรายชื่อนักเรียน</p>
                            <small>${escapeHtml(error.message)}</small>
                        </td>
                    </tr>
                `;
            }
        });
    }

    // ฟังก์ชันแสดงรายชื่อนักเรียนในตาราง
    function renderStudentsTable(students) {
        const studentsTableBody = document.getElementById('studentsTableBody');
        
        if (!studentsTableBody) return;

        if (students.length === 0) {
            studentsTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p class="mb-0">ไม่มีนักเรียนในห้องเรียนนี้</p>
                    </td>
                </tr>
            `;
            return;
        }

        const rowsHtml = students.map((student, index) => {
            const studentName = student.user ? 
                `${student.user.users_name_prefix || ''}${student.user.users_first_name || ''} ${student.user.users_last_name || ''}`.trim() : 
                'ไม่มีข้อมูล'; // If 'ไม่มีข้อมูล' is shown, student.user is missing from the API response
            
            const studentCode = student.students_student_code || 'N/A';
            const currentScore = student.students_current_score || 100;
            // last_violation_date might not be directly available on student objects from the class list API
            // It's handled by StudentApiController for individual student view.
            // The current fallback to '-' is appropriate if the data isn't provided.
            const lastViolation = student.last_violation_date || '-'; 
            
            // กำหนดสีของคะแนน
            let scoreClass = 'text-success';
            if (currentScore <= 50) {
                scoreClass = 'text-danger';
            } else if (currentScore <= 75) {
                scoreClass = 'text-warning';
            }

            return `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(studentName)}&background=95A4D8&color=fff" 
                                 class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                            <span>${escapeHtml(studentName)}</span>
                        </div>
                    </td>
                    <td><small class="text-muted">${escapeHtml(studentCode)}</small></td>
                    <td><span class="fw-bold ${scoreClass}">${currentScore}</span></td>
                    <td><small class="text-muted">${escapeHtml(lastViolation)}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary view-student-btn" 
                                data-student-id="${student.students_id}" 
                                title="ดูรายละเอียด">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning record-behavior-btn ms-1" 
                                data-student-id="${student.students_id}" 
                                data-student-name="${escapeHtml(studentName)}"
                                title="บันทึกพฤติกรรม">
                            <i class="fas fa-exclamation-triangle"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        studentsTableBody.innerHTML = rowsHtml;

        // เพิ่ม event listeners สำหรับปุ่มต่างๆ
        attachStudentActionListeners();
    }

    // ฟังก์ชันเพิ่ม event listeners สำหรับปุ่มการจัดการนักเรียน
    function attachStudentActionListeners() {
        // ปุ่มดูรายละเอียดนักเรียน
        document.querySelectorAll('.view-student-btn').forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-student-id');
                // เปิด Student Detail Modal
                const studentDetailModal = new bootstrap.Modal(document.getElementById('studentDetailModal'));
                studentDetailModal.show();
                
                // โหลดข้อมูลนักเรียน (ถ้ามีฟังก์ชันใน behavior-report.js)
                if (typeof loadStudentDetails === 'function') {
                    loadStudentDetails(studentId);
                } else {
                    console.warn('loadStudentDetails function not found');
                }
            });
        });

        // ปุ่มบันทึกพฤติกรรม
        document.querySelectorAll('.record-behavior-btn').forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-student-id');
                const studentName = this.getAttribute('data-student-name');
                
                // ปิด Class Detail Modal
                const classDetailModal = bootstrap.Modal.getInstance(document.getElementById('classDetailModal'));
                if (classDetailModal) {
                    classDetailModal.hide();
                }
                
                // เปิด New Violation Modal และเติมข้อมูลนักเรียนที่เลือก
                setTimeout(() => {
                    const newViolationModal = new bootstrap.Modal(document.getElementById('newViolationModal'));
                    newViolationModal.show();
                    
                    // เติมข้อมูลนักเรียนที่เลือก (ถ้ามีฟังก์ชันใน behavior-report.js)
                    if (typeof selectStudent === 'function') {
                        selectStudent({
                            id: studentId,
                            name: studentName,
                            student_id: '',
                            class: '',
                            current_score: 100
                        });
                    }
                }, 500);
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