/**
 * Student Management Module
 * จัดการฟังก์ชันที่เกี่ยวข้องกับนักเรียน
 */

class StudentManager {
    constructor() {
        this.selectedStudent = null;
        this.searchResults = [];
        this.init();
    }

    /**
     * เริ่มต้นการทำงาน
     */
    init() {
        this.bindEvents();
    }

    /**
     * ผูก event listeners
     */
    bindEvents() {
        this.bindStudentSearch();
        this.bindStudentSelection();
        this.bindModalEvents();
    }

    /**
     * ฟังก์ชันค้นหานักเรียน
     */
    bindStudentSearch() {
        const searchInput = document.getElementById('behaviorStudentSearch');
        if (!searchInput) return;

        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    this.searchStudents(query);
                }, 300);
            } else {
                this.hideSearchResults();
            }
        });
    }

    /**
     * ค้นหานักเรียน
     * @param {string} query - คำค้นหา
     */
    async searchStudents(query) {
        try {
            const response = await fetch(`/api/behavior-reports/students/search?term=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                this.searchResults = data.data;
                this.displaySearchResults(data.data);
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการค้นหา');
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('ไม่สามารถค้นหานักเรียนได้');
        }
    }

    /**
     * แสดงผลการค้นหา
     * @param {Array} students - รายการนักเรียน
     */
    displaySearchResults(students) {
        const resultsContainer = document.getElementById('studentSearchResults');
        if (!resultsContainer) return;

        if (students.length === 0) {
            resultsContainer.innerHTML = '<div class="search-no-results">ไม่พบนักเรียน</div>';
            resultsContainer.style.display = 'block';
            return;
        }

        const resultsHTML = students.map(student => `
            <div class="search-result-item" data-student-id="${student.id}">
                <div class="student-info">
                    <img src="${this.getStudentAvatar(student)}" 
                         alt="Avatar" class="student-avatar">
                    <div class="student-details">
                        <div class="student-name">${student.name}</div>
                        <div class="student-meta">
                            ${student.student_id} | 
                            ${student.class}
                        </div>
                    </div>
                </div>
                <div class="student-score">
                    <span class="score-badge ${this.getScoreClass(student.current_score)}">
                        ${student.current_score || 100}
                    </span>
                </div>
            </div>
        `).join('');

        resultsContainer.innerHTML = resultsHTML;
        resultsContainer.style.display = 'block';
    }

    /**
     * ซ่อนผลการค้นหา
     */
    hideSearchResults() {
        const resultsContainer = document.getElementById('studentSearchResults');
        if (resultsContainer) {
            resultsContainer.style.display = 'none';
        }
    }

    /**
     * ผูก event การเลือกนักเรียน
     */
    bindStudentSelection() {
        const resultsContainer = document.getElementById('studentSearchResults');
        if (!resultsContainer) return;

        resultsContainer.addEventListener('click', (e) => {
            const resultItem = e.target.closest('.search-result-item');
            if (resultItem) {
                const studentId = resultItem.dataset.studentId;
                const student = this.searchResults.find(s => s.id == studentId);
                if (student) {
                    this.selectStudent(student);
                }
            }
        });
    }

    /**
     * เลือกนักเรียน
     * @param {Object} student - ข้อมูลนักเรียน
     */
    selectStudent(student) {
        this.selectedStudent = student;
        this.hideSearchResults();
        this.displaySelectedStudent(student);
        this.updateFormFields(student);
    }

    /**
     * แสดงข้อมูลนักเรียนที่เลือก
     * @param {Object} student - ข้อมูลนักเรียน
     */
    displaySelectedStudent(student) {
        const selectedInfo = document.getElementById('selectedStudentInfo');
        const infoDisplay = document.getElementById('studentInfoDisplay');
        
        if (selectedInfo && infoDisplay) {
            infoDisplay.innerHTML = `
                <div class="selected-student-card">
                    <img src="${this.getStudentAvatar(student)}" 
                         alt="Avatar" class="selected-avatar">
                    <div class="selected-info">
                        <div class="selected-name">${student.name}</div>
                        <div class="selected-details">
                            รหัส: ${student.student_id} | 
                            ห้อง: ${student.class} | 
                            คะแนน: <span class="score-badge ${this.getScoreClass(student.current_score)}">
                                ${student.current_score || 100}
                            </span>
                        </div>
                    </div>
                    <button type="button" class="btn-remove-student" onclick="studentManager.clearSelection()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            selectedInfo.style.display = 'block';
        }
    }

    /**
     * อัปเดตฟิลด์ในฟอร์ม
     * @param {Object} student - ข้อมูลนักเรียน
     */
    updateFormFields(student) {
        const selectedStudentId = document.getElementById('selectedStudentId');
        const searchInput = document.getElementById('behaviorStudentSearch');
        
        if (selectedStudentId) {
            selectedStudentId.value = student.id;
        }
        
        if (searchInput) {
            searchInput.value = student.name;
        }
    }

    /**
     * ล้างการเลือกนักเรียน
     */
    clearSelection() {
        this.selectedStudent = null;
        
        const selectedInfo = document.getElementById('selectedStudentInfo');
        const searchInput = document.getElementById('behaviorStudentSearch');
        const selectedStudentId = document.getElementById('selectedStudentId');
        
        if (selectedInfo) selectedInfo.style.display = 'none';
        if (searchInput) searchInput.value = '';
        if (selectedStudentId) selectedStudentId.value = '';
    }

    /**
     * ผูก event สำหรับ modal
     */
    bindModalEvents() {
        // Reset form เมื่อปิด modal
        const violationModal = document.getElementById('newViolationModal');
        if (violationModal) {
            violationModal.addEventListener('hidden.bs.modal', () => {
                this.clearSelection();
                this.resetForm();
            });
        }
    }

    /**
     * รีเซ็ตฟอร์ม
     */
    resetForm() {
        const form = document.getElementById('violationForm');
        if (form) {
            form.reset();
        }
        this.clearSelection();
    }

    // Helper Methods
    /**
     * ดึง avatar ของนักเรียน
     * @param {Object} student - ข้อมูลนักเรียน
     * @returns {string}
     */
    getStudentAvatar(student) {
        if (student.user?.users_profile_image) {
            return `/storage/${student.user.users_profile_image}`;
        }
        
        const name = student.user?.users_first_name || 'Student';
        return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=95A4D8&color=fff`;
    }

    /**
     * ดึงชื่อเต็มของนักเรียน
     * @param {Object} student - ข้อมูลนักเรียน
     * @returns {string}
     */
    getStudentFullName(student) {
        if (!student.user) return 'ไม่มีชื่อ';
        
        const prefix = student.user.users_name_prefix || '';
        const firstName = student.user.users_first_name || '';
        const lastName = student.user.users_last_name || '';
        
        return `${prefix}${firstName} ${lastName}`.trim();
    }

    /**
     * ดึงชื่อห้องเรียน
     * @param {Object} student - ข้อมูลนักเรียน
     * @returns {string}
     */
    getClassroomName(student) {
        if (!student.classroom) return 'ไม่ระบุ';
        
        const level = student.classroom.classes_level;
        const room = student.classroom.classes_room_number;
        
        return `${level}/${room}`;
    }

    /**
     * ดึง CSS class สำหรับคะแนน
     * @param {number} score - คะแนน
     * @returns {string}
     */
    getScoreClass(score) {
        if (score >= 80) return 'score-excellent';
        if (score >= 60) return 'score-good';
        if (score >= 40) return 'score-fair';
        return 'score-poor';
    }

    /**
     * แสดงข้อความข้อผิดพลาด
     * @param {string} message - ข้อความ
     */
    showError(message) {
        console.error(message);
        // สามารถเพิ่ม notification system ได้ที่นี่
    }
}

// Export สำหรับใช้งาน
window.StudentManager = StudentManager;

// สร้าง instance หลัก
window.studentManager = new StudentManager();
