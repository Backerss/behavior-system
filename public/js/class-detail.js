/**
 * JavaScript สำหรับจัดการการแสดงรายละเอียดห้องเรียน
 * ใช้งานกับ Modal: classDetailModal
 */

document.addEventListener('DOMContentLoaded', function() {
    let currentClassId = null;
    let studentsData = [];
    let studentCurrentPage = 1;
    let studentTotalPages = 1;
    let studentSearchTerm = '';
    
    // ตัวแปรสำหรับเก็บอ้างอิงถึงกราฟ
    let violationChart = null;
    
    // เชื่อมต่อ DOM elements
    const classDetailModal = document.getElementById('classDetailModal');
    const classTitle = document.querySelector('.class-title');
    const classLevelRoom = document.getElementById('class-level-room');
    const classAcademicYear = document.getElementById('class-academic-year');
    const classTeacherName = document.getElementById('class-teacher-name');
    const classStudentsCount = document.getElementById('class-students-count');
    const classAvgScore = document.getElementById('class-avg-score');
    const classAvgScoreBar = document.getElementById('class-avg-score-bar');
    const studentCountBadge = document.getElementById('student-count-badge');
    const studentsList = document.getElementById('students-list');
    const studentPagination = document.getElementById('student-pagination');
    const studentSearch = document.getElementById('studentSearch');
    const btnSearchStudent = document.getElementById('btnSearchStudent');
    const btnEditClassDetail = document.getElementById('btnEditClassDetail');
    const btnExportClassReport = document.getElementById('btnExportClassReport');
    const classDetailLoading = document.getElementById('classDetailLoading');
    const classDetailContent = document.getElementById('classDetailContent');
    const noViolationsMessage = document.getElementById('no-violations-message');
    
    // Event listener สำหรับ modal เมื่อถูกแสดง
    if (classDetailModal) {
        classDetailModal.addEventListener('show.bs.modal', function(event) {
            // ดึงข้อมูล class_id จากปุ่มที่ถูกคลิก
            const button = event.relatedTarget;
            if (button && button.hasAttribute('data-id')) {
                currentClassId = button.getAttribute('data-id');
                loadClassDetails(currentClassId);
            }
        });
        
        // เมื่อ modal ถูกซ่อน รีเซ็ตข้อมูล
        classDetailModal.addEventListener('hidden.bs.modal', function() {
            resetClassDetailModal();
        });
    }
    
    // Event listener สำหรับการค้นหานักเรียน
    if (studentSearch) {
        studentSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                studentSearchTerm = this.value;
                studentCurrentPage = 1;
                loadClassStudents(currentClassId, studentCurrentPage, studentSearchTerm);
            }
        });
    }
    
    if (btnSearchStudent) {
        btnSearchStudent.addEventListener('click', function() {
            studentSearchTerm = studentSearch.value;
            studentCurrentPage = 1;
            loadClassStudents(currentClassId, studentCurrentPage, studentSearchTerm);
        });
    }
    
    if (btnEditClassDetail) {
        btnEditClassDetail.addEventListener('click', function() {
            // ปิด modal รายละเอียด
            const classDetailModalInstance = bootstrap.Modal.getInstance(classDetailModal);
            classDetailModalInstance.hide();
            
            // เปิด modal จัดการห้องเรียนพร้อมตั้งค่าเพื่อแก้ไข
            setTimeout(() => {
                const classManagementModal = document.getElementById('classManagementModal');
                if (classManagementModal) {
                    const modal = new bootstrap.Modal(classManagementModal);
                    modal.show();
                    
                    // รอให้ modal เปิด แล้วค่อยเรียกฟังก์ชันแก้ไข
                    classManagementModal.addEventListener('shown.bs.modal', function handler() {
                        // ตรวจสอบว่ามีฟังก์ชันนี้ไหม
                        if (typeof editClassroom === 'function') {
                            editClassroom(currentClassId);
                        } else {
                            // ถ้าไม่มีฟังก์ชันโดยตรง ให้เรียกฝ่าน event ของปุ่มแก้ไข
                            const editButtons = document.querySelectorAll('.edit-class-btn');
                            editButtons.forEach(button => {
                                if (button.getAttribute('data-id') == currentClassId) {
                                    button.click();
                                }
                            });
                        }
                        // ลบ event listener นี้หลังจากใช้งานแล้ว
                        classManagementModal.removeEventListener('shown.bs.modal', handler);
                    });
                }
            }, 500); // รอ 500ms เพื่อให้แน่ใจว่า modal แรกปิดแล้ว
        });
    }
    
    if (btnExportClassReport) {
        btnExportClassReport.addEventListener('click', function() {
            if (!currentClassId) return;
            
            // แสดงการโหลด
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> กำลังเตรียมข้อมูล...';
            
            // เรียก API เพื่อสร้างรายงาน
            fetch(`/api/classes/${currentClassId}/export`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('ไม่สามารถสร้างรายงานได้');
                }
                return response.blob();
            })
            .then(blob => {
                // สร้าง URL สำหรับดาวน์โหลด
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `รายงานห้องเรียน-${classLevelRoom.textContent}-${new Date().toISOString().slice(0,10)}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Error exporting class report:', error);
                alert('ไม่สามารถสร้างรายงานได้ กรุณาลองใหม่อีกครั้ง');
            })
            .finally(() => {
                // คืนค่าปุ่ม
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    }
    
    // ฟังก์ชันโหลดข้อมูลห้องเรียน
    function loadClassDetails(classId) {
        if (!classId) return;
        
        // แสดง loading
        showLoading();
        
        // เรียก API เพื่อดึงข้อมูลห้องเรียน
        fetch(`/api/classes/${classId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('ไม่สามารถดึงข้อมูลห้องเรียนได้');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // แสดงข้อมูลห้องเรียน
                displayClassDetails(data.data);
                
                // โหลดข้อมูลนักเรียนในห้องเรียน
                loadClassStudents(classId, 1, '');
                
                // โหลดข้อมูลสถิติการกระทำผิด
                loadViolationStatistics(classId);
            } else {
                throw new Error(data.message || 'ไม่สามารถดึงข้อมูลห้องเรียนได้');
            }
        })
        .catch(error => {
            console.error('Error loading class details:', error);
            alert('เกิดข้อผิดพลาดในการโหลดข้อมูลห้องเรียน');
            hideLoading();
        });
    }
    
    // ฟังก์ชันแสดงข้อมูลห้องเรียน
    function displayClassDetails(classData) {
        if (!classData) return;
        
        // แสดงข้อมูลพื้นฐานของห้องเรียน
        const roomInfo = `${classData.classes_level}/${classData.classes_room_number}`;
        classTitle.textContent = roomInfo;
        classLevelRoom.textContent = roomInfo;
        classAcademicYear.textContent = classData.classes_academic_year;
        
        // แสดงข้อมูลครูประจำชั้น
        if (classData.teacher && classData.teacher.user) {
            const teacher = classData.teacher.user;
            classTeacherName.textContent = `${teacher.users_name_prefix}${teacher.users_first_name} ${teacher.users_last_name}`;
        } else {
            classTeacherName.textContent = 'ไม่ระบุ';
        }
        
        // แสดงจำนวนนักเรียน
        classStudentsCount.textContent = `${classData.students_count || 0} คน`;
        studentCountBadge.textContent = classData.students_count || 0;
        
        // ซ่อน loading
        hideLoading();
    }
    
    // ฟังก์ชันโหลดข้อมูลนักเรียนในห้องเรียน
    function loadClassStudents(classId, page = 1, search = '') {
        if (!classId) return;
        
        // แสดง loading ในตาราง
        studentsList.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-2 text-muted">กำลังโหลดข้อมูลนักเรียน...</p>
                </td>
            </tr>
        `;
        
        // สร้าง URL
        let url = `/api/classes/${classId}/students?page=${page}`;
        if (search) {
            url += `&search=${encodeURIComponent(search)}`;
        }
        
        // เรียก API
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('ไม่สามารถดึงข้อมูลนักเรียนได้');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // เก็บข้อมูล
                studentsData = data.data.data || [];
                studentCurrentPage = data.data.current_page || 1;
                studentTotalPages = data.data.last_page || 1;
                
                // แสดงข้อมูลนักเรียน
                displayStudents();
                
                // แสดงการแบ่งหน้า
                renderStudentPagination();
            } else {
                throw new Error(data.message || 'ไม่สามารถดึงข้อมูลนักเรียนได้');
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            studentsList.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="text-danger mb-2">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                        <p>เกิดข้อผิดพลาดในการโหลดข้อมูลนักเรียน</p>
                    </td>
                </tr>
            `;
        });
    }
    
    // ฟังก์ชันแสดงข้อมูลนักเรียน
    function displayStudents() {
        // เคลียร์ข้อมูลเดิม
        studentsList.innerHTML = '';
        
        if (!studentsData || studentsData.length === 0) {
            studentsList.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>ไม่พบข้อมูลนักเรียนในห้องเรียนนี้</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        // วนลูปแสดงข้อมูลนักเรียน
        studentsData.forEach((student, index) => {
            // คำนวณเลขที่นักเรียน
            const number = (studentCurrentPage - 1) * 10 + index + 1;
            
            // คำนวณคลาสสำหรับ progress bar
            let scoreClass = 'student-score-high';
            if (student.students_current_score <= 50) {
                scoreClass = 'student-score-low';
            } else if (student.students_current_score <= 75) {
                scoreClass = 'student-score-medium';
            }
            
            // สร้าง URL รูปโปรไฟล์
            const profileImage = student.users_profile_image
                ? `/storage/${student.users_profile_image}`
                : `https://ui-avatars.com/api/?name=${encodeURIComponent(student.users_first_name)}&background=95A4D8&color=fff`;
            
            // สร้าง row
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${number}</td>
                <td>${student.students_student_code || '-'}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${profileImage}" class="student-avatar me-2" alt="${student.users_first_name}">
                        <span class="student-name">${student.users_name_prefix}${student.users_first_name} ${student.users_last_name}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                            <div class="progress-bar ${scoreClass}" role="progressbar" style="width: ${student.students_current_score || 0}%"></div>
                        </div>
                        <span class="small">${student.students_current_score || 0}/100</span>
                    </div>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary-app view-student-btn" data-id="${student.students_id}">
                        <i class="fas fa-user me-1"></i> ดูข้อมูล
                    </button>
                </td>
            `;
            
            studentsList.appendChild(row);
        });
        
        // เพิ่ม event listener สำหรับปุ่มดูข้อมูลนักเรียน
        document.querySelectorAll('.view-student-btn').forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-id');
                openStudentDetailModal(studentId);
            });
        });
    }
    
    // ฟังก์ชันแสดงการแบ่งหน้า
    function renderStudentPagination() {
        studentPagination.innerHTML = '';
        
        if (studentTotalPages <= 1) return;
        
        // ปุ่ม Previous
        const prevLi = document.createElement('li');
        prevLi.classList.add('page-item');
        if (studentCurrentPage === 1) {
            prevLi.classList.add('disabled');
        }
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${studentCurrentPage - 1}">Previous</a>`;
        studentPagination.appendChild(prevLi);
        
        // หน้าต่าง ๆ
        for (let i = 1; i <= studentTotalPages; i++) {
            const pageLi = document.createElement('li');
            pageLi.classList.add('page-item');
            if (i === studentCurrentPage) {
                pageLi.classList.add('active');
            }
            pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            studentPagination.appendChild(pageLi);
        }
        
        // ปุ่ม Next
        const nextLi = document.createElement('li');
        nextLi.classList.add('page-item');
        if (studentCurrentPage === studentTotalPages) {
            nextLi.classList.add('disabled');
        }
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${studentCurrentPage + 1}">Next</a>`;
        studentPagination.appendChild(nextLi);
        
        // เพิ่ม event listeners
        document.querySelectorAll('#student-pagination li:not(.disabled) a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                loadClassStudents(currentClassId, page, studentSearchTerm);
            });
        });
    }
    
    // ฟังก์ชันโหลดสถิติการกระทำผิด
    function loadViolationStatistics(classId) {
        if (!classId) return;
        
        // แสดงกราฟ loading
        const chartContainer = document.getElementById('chart-container');
        
        // เรียก API
        fetch(`/api/classes/${classId}/violations/stats`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('ไม่สามารถดึงข้อมูลสถิติได้');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.data && data.data.length > 0) {
                    // มีข้อมูล
                    renderViolationChart(data.data);
                    noViolationsMessage.classList.add('d-none');
                } else {
                    // ไม่มีข้อมูล
                    destroyChart();
                    noViolationsMessage.classList.remove('d-none');
                }
            } else {
                throw new Error(data.message || 'ไม่สามารถดึงข้อมูลสถิติได้');
            }
        })
        .catch(error => {
            console.error('Error loading violation statistics:', error);
            destroyChart();
            noViolationsMessage.classList.remove('d-none');
            noViolationsMessage.querySelector('p').textContent = 'ไม่สามารถโหลดข้อมูลสถิติได้';
        });
    }
    
    // ฟังก์ชันแสดงกราฟสถิติการกระทำผิด
    function renderViolationChart(data) {
        const ctx = document.getElementById('classViolationChart');
        if (!ctx) return;
        
        // ทำลายกราฟเดิมถ้ามี
        destroyChart();
        
        // สร้างข้อมูลสำหรับกราฟ
        const labels = data.map(item => item.name);
        const values = data.map(item => item.count);
        const colors = generateColors(data.length);
        
        // สร้างกราฟใหม่
        violationChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw} ครั้ง`;
                            }
                        }
                    }
                },
                cutout: '70%',
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
        
        // แสดงคะแนนเฉลี่ยของห้อง
        const avgScore = data.reduce((total, item) => {
            return total + (item.avg_score || 0);
        }, 0) / data.length;
        
        if (avgScore) {
            const roundedScore = Math.round(avgScore * 10) / 10;
            classAvgScore.textContent = `${roundedScore} คะแนน`;
            classAvgScoreBar.style.width = `${roundedScore}%`;
        } else {
            classAvgScore.textContent = 'ไม่มีข้อมูล';
            classAvgScoreBar.style.width = '0%';
        }
    }
    
    // ฟังก์ชันทำลายกราฟ
    function destroyChart() {
        if (violationChart) {
            violationChart.destroy();
            violationChart = null;
        }
    }
    
    // ฟังก์ชันสร้างสีแบบสุ่มสำหรับกราฟ
    function generateColors(count) {
        // สีพื้นฐาน
        const baseColors = [
            '#4361EE', '#3F8EFC', '#2EC4B6', '#FF9F1C', '#E71D36',
            '#011627', '#FF9F1C', '#E71D36', '#7209B7', '#3A0CA3'
        ];
        
        if (count <= baseColors.length) {
            return baseColors.slice(0, count);
        }
        
        // ถ้าต้องการสีมากกว่าที่มี ให้สร้างสีเพิ่ม
        const colors = [...baseColors];
        
        for (let i = baseColors.length; i < count; i++) {
            const r = Math.floor(Math.random() * 255);
            const g = Math.floor(Math.random() * 255);
            const b = Math.floor(Math.random() * 255);
            colors.push(`rgb(${r}, ${g}, ${b})`);
        }
        
        return colors;
    }
    
    // ฟังก์ชันเปิดหน้ารายละเอียดนักเรียน
    function openStudentDetailModal(studentId) {
        // ปิด modal รายละเอียดห้องเรียน
        const classDetailModalInstance = bootstrap.Modal.getInstance(classDetailModal);
        classDetailModalInstance.hide();
        
        // เปิด modal รายละเอียดนักเรียน
        setTimeout(() => {
            const studentDetailModal = document.getElementById('studentDetailModal');
            if (studentDetailModal) {
                // ตั้งค่า studentId ไว้ใน modal
                studentDetailModal.setAttribute('data-student-id', studentId);
                
                // เปิด modal
                const modal = new bootstrap.Modal(studentDetailModal);
                modal.show();
                
                // โหลดข้อมูลนักเรียน
                if (typeof loadStudentDetails === 'function') {
                    loadStudentDetails(studentId);
                } else {
                    console.warn('Function loadStudentDetails not found');
                }
            }
        }, 500);
    }
    
    // ฟังก์ชันรีเซ็ต modal
    function resetClassDetailModal() {
        currentClassId = null;
        studentsData = [];
        studentCurrentPage = 1;
        studentTotalPages = 1;
        studentSearchTerm = '';
        
        // รีเซ็ตข้อมูลที่แสดง
        classTitle.textContent = '';
        classLevelRoom.textContent = '';
        classAcademicYear.textContent = '';
        classTeacherName.textContent = '';
        classStudentsCount.textContent = '';
        studentCountBadge.textContent = '0';
        
        // ล้างตารางนักเรียน
        studentsList.innerHTML = '';
        studentPagination.innerHTML = '';
        
        // รีเซ็ตการค้นหา
        if (studentSearch) {
            studentSearch.value = '';
        }
        
        // ทำลายกราฟ
        destroyChart();
    }
    
    // ฟังก์ชันแสดง loading
    function showLoading() {
        if (classDetailLoading) {
            classDetailLoading.classList.remove('d-none');
        }
        if (classDetailContent) {
            classDetailContent.classList.add('d-none');
        }
    }
    
    // ฟังก์ชันซ่อน loading
    function hideLoading() {
        if (classDetailLoading) {
            classDetailLoading.classList.add('d-none');
        }
        if (classDetailContent) {
            classDetailContent.classList.remove('d-none');
        }
    }
});