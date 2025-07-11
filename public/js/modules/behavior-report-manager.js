/**
 * Behavior Report Manager Module
 * จัดการฟังก์ชันที่เกี่ยวข้องกับการบันทึกรายงานพฤติกรรม
 */

class BehaviorReportManager {
    constructor() {
        this.currentReport = null;
        this.selectedStudent = null;
        this.violations = [];
        this.config = {
            apiEndpoints: {
                store: '/api/behavior-reports',
                students: '/api/behavior-reports/students/search',
                violations: '/api/violations'
            },
            fileUpload: {
                maxSize: 2048 * 1024, // 2MB
                allowedTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
            }
        };
        this.init();
    }

    /**
     * เริ่มต้นการทำงาน
     */
    init() {
        this.bindEvents();
        this.loadViolations();
        this.setupFormValidation();
    }

    /**
     * ผูก event listeners
     */
    bindEvents() {
        this.bindFormSubmission();
        this.bindFileUpload();
        this.bindViolationTypeChange();
        this.bindDateTimeValidation();
        this.bindModalEvents();
    }

    /**
     * ผูก event การส่งฟอร์ม
     */
    bindFormSubmission() {
        const form = document.getElementById('violationForm');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.submitReport();
        });
    }

    /**
     * ผูก event การอัปโหลดไฟล์
     */
    bindFileUpload() {
        const fileInput = document.getElementById('evidenceFile');
        if (!fileInput) return;

        fileInput.addEventListener('change', (e) => {
            this.handleFileSelection(e.target.files[0]);
        });

        // Drag and drop support
        const dropZone = document.getElementById('evidenceDropZone');
        if (dropZone) {
            this.setupDragAndDrop(dropZone, fileInput);
        }
    }

    /**
     * ผูก event การเปลี่ยนประเภทพฤติกรรม
     */
    bindViolationTypeChange() {
        const violationSelect = document.getElementById('violationType');
        if (!violationSelect) return;

        violationSelect.addEventListener('change', (e) => {
            this.handleViolationTypeChange(e.target.value);
        });
    }

    /**
     * ผูก event การตรวจสอบวันที่และเวลา
     */
    bindDateTimeValidation() {
        const datetimeInput = document.getElementById('violationDateTime');
        if (!datetimeInput) return;

        // Set default to current datetime
        const now = new Date();
        const formatted = now.toISOString().slice(0, 16);
        datetimeInput.value = formatted;

        // Validate datetime
        datetimeInput.addEventListener('change', (e) => {
            this.validateDateTime(e.target.value);
        });
    }

    /**
     * ผูก event สำหรับ modal
     */
    bindModalEvents() {
        const modal = document.getElementById('newViolationModal');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', () => {
            this.onModalShow();
        });

        modal.addEventListener('hidden.bs.modal', () => {
            this.onModalHide();
        });
    }

    /**
     * โหลดข้อมูลประเภทการกระทำผิด
     */
    async loadViolations() {
        try {
            const response = await fetch(this.config.apiEndpoints.violations);
            const data = await response.json();
            
            if (data.success) {
                this.violations = data.violations;
                this.populateViolationSelect(data.violations);
            }
        } catch (error) {
            console.error('Error loading violations:', error);
            this.showError('ไม่สามารถโหลดข้อมูลประเภทการกระทำผิดได้');
        }
    }

    /**
     * เติมข้อมูลใน select ประเภทการกระทำผิด
     */
    populateViolationSelect(violations) {
        const select = document.getElementById('violationType');
        if (!select) return;

        // Clear existing options except the first one
        select.innerHTML = '<option value="">เลือกประเภทการกระทำผิด</option>';

        // Group violations by category
        const grouped = violations.reduce((acc, violation) => {
            const category = violation.violations_category || 'other';
            if (!acc[category]) acc[category] = [];
            acc[category].push(violation);
            return acc;
        }, {});

        // Add optgroups
        Object.entries(grouped).forEach(([category, items]) => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = this.getCategoryLabel(category);
            
            items.forEach(violation => {
                const option = document.createElement('option');
                option.value = violation.violations_id;
                option.textContent = `${violation.violations_name} (-${violation.violations_points_deducted} คะแนน)`;
                option.dataset.points = violation.violations_points_deducted;
                option.dataset.category = violation.violations_category;
                optgroup.appendChild(option);
            });
            
            select.appendChild(optgroup);
        });
    }

    /**
     * จัดการการเปลี่ยนประเภทพฤติกรรม
     */
    handleViolationTypeChange(violationId) {
        if (!violationId) {
            this.updatePointsDisplay(0);
            return;
        }

        const violation = this.violations.find(v => v.violations_id == violationId);
        if (violation) {
            this.updatePointsDisplay(violation.violations_points_deducted);
            this.updateSeverityIndicator(violation.violations_category);
        }
    }

    /**
     * อัปเดตการแสดงคะแนนที่หัก
     */
    updatePointsDisplay(points) {
        const pointsInput = document.getElementById('pointsDeducted');
        const pointsDisplay = document.getElementById('pointsDisplay');
        
        if (pointsInput) pointsInput.value = Math.abs(points);
        if (pointsDisplay) {
            pointsDisplay.textContent = Math.abs(points);
            pointsDisplay.className = `points-display ${this.getPointsClass(points)}`;
        }
    }

    /**
     * อัปเดตตัวบ่งชี้ความรุนแรง
     */
    updateSeverityIndicator(category) {
        const indicator = document.getElementById('severityIndicator');
        if (!indicator) return;

        const severityConfig = {
            'light': { class: 'severity-light', text: 'เบา', color: '#28a745' },
            'medium': { class: 'severity-medium', text: 'ปานกลาง', color: '#ffc107' },
            'severe': { class: 'severity-severe', text: 'รุนแรง', color: '#dc3545' }
        };

        const config = severityConfig[category] || severityConfig['light'];
        indicator.className = `severity-indicator ${config.class}`;
        indicator.textContent = config.text;
        indicator.style.backgroundColor = config.color;
    }

    /**
     * จัดการการเลือกไฟล์
     */
    handleFileSelection(file) {
        if (!file) return;

        // Validate file
        const validation = this.validateFile(file);
        if (!validation.valid) {
            this.showError(validation.message);
            this.clearFileInput();
            return;
        }

        // Show file preview
        this.showFilePreview(file);
    }

    /**
     * ตรวจสอบไฟล์
     */
    validateFile(file) {
        // Check file size
        if (file.size > this.config.fileUpload.maxSize) {
            return {
                valid: false,
                message: `ไฟล์มีขนาดใหญ่เกินไป (สูงสุด ${this.config.fileUpload.maxSize / 1024 / 1024}MB)`
            };
        }

        // Check file type
        if (!this.config.fileUpload.allowedTypes.includes(file.type)) {
            return {
                valid: false,
                message: 'ประเภทไฟล์ไม่ถูกต้อง (รองรับเฉพาะรูปภาพ)'
            };
        }

        return { valid: true };
    }

    /**
     * แสดงตัวอย่างไฟล์
     */
    showFilePreview(file) {
        const preview = document.getElementById('filePreview');
        if (!preview) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.innerHTML = `
                <div class="file-preview-item">
                    <img src="${e.target.result}" alt="Preview" class="preview-image">
                    <div class="preview-info">
                        <div class="preview-name">${file.name}</div>
                        <div class="preview-size">${this.formatFileSize(file.size)}</div>
                        <button type="button" class="btn-remove-file" onclick="behaviorReportManager.clearFileInput()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }

    /**
     * ล้างการเลือกไฟล์
     */
    clearFileInput() {
        const fileInput = document.getElementById('evidenceFile');
        const preview = document.getElementById('filePreview');
        
        if (fileInput) fileInput.value = '';
        if (preview) preview.innerHTML = '';
    }

    /**
     * ตั้งค่า drag and drop
     */
    setupDragAndDrop(dropZone, fileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                this.handleFileSelection(files[0]);
            }
        }, false);
    }

    /**
     * ป้องกัน default behavior
     */
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * ตรวจสอบวันที่และเวลา
     */
    validateDateTime(datetime) {
        const input = document.getElementById('violationDateTime');
        const now = new Date();
        const selected = new Date(datetime);

        if (selected > now) {
            this.showError('วันที่และเวลาไม่สามารถเป็นอนาคตได้');
            input.value = now.toISOString().slice(0, 16);
            return false;
        }

        return true;
    }

    /**
     * ส่งรายงาน
     */
    async submitReport() {
        if (!this.validateForm()) return;

        const formData = this.prepareFormData();
        
        try {
            this.showLoading(true);
            
            const response = await fetch(this.config.apiEndpoints.store, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.onReportSubmitted(data.data);
                this.hideModal();
                this.showSuccess('บันทึกรายงานพฤติกรรมสำเร็จ');
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการบันทึก');
            }
        } catch (error) {
            console.error('Error submitting report:', error);
            this.showError(error.message || 'ไม่สามารถบันทึกรายงานได้');
        } finally {
            this.showLoading(false);
        }
    }

    /**
     * ตรวจสอบฟอร์ม
     */
    validateForm() {
        const form = document.getElementById('violationForm');
        if (!form) return false;

        // Check required fields
        const requiredFields = [
            { id: 'selectedStudentId', message: 'กรุณาเลือกนักเรียน' },
            { id: 'violationType', message: 'กรุณาเลือกประเภทการกระทำผิด' },
            { id: 'violationDateTime', message: 'กรุณาระบุวันที่และเวลา' }
        ];

        for (const field of requiredFields) {
            const element = document.getElementById(field.id);
            if (!element || !element.value.trim()) {
                this.showError(field.message);
                element?.focus();
                return false;
            }
        }

        return true;
    }

    /**
     * เตรียมข้อมูลฟอร์ม
     */
    prepareFormData() {
        const formData = new FormData();
        
        // Add form fields
        const fields = [
            'selectedStudentId',
            'violationType',
            'violationDateTime',
            'violationDescription'
        ];

        fields.forEach(fieldId => {
            const element = document.getElementById(fieldId);
            if (element && element.value) {
                const name = this.getFieldName(fieldId);
                formData.append(name, element.value);
            }
        });

        // Add file if selected
        const fileInput = document.getElementById('evidenceFile');
        if (fileInput && fileInput.files[0]) {
            formData.append('evidence', fileInput.files[0]);
        }

        return formData;
    }

    /**
     * แปลง field ID เป็นชื่อ field ในฐานข้อมูล
     */
    getFieldName(fieldId) {
        const mapping = {
            'selectedStudentId': 'student_id',
            'violationType': 'violation_id',
            'violationDateTime': 'violation_datetime',
            'violationDescription': 'description'
        };
        return mapping[fieldId] || fieldId;
    }

    /**
     * เมื่อ modal แสดง
     */
    onModalShow() {
        this.resetForm();
        this.setupFormValidation();
    }

    /**
     * เมื่อ modal ซ่อน
     */
    onModalHide() {
        this.resetForm();
        this.currentReport = null;
    }

    /**
     * รีเซ็ตฟอร์ม
     */
    resetForm() {
        const form = document.getElementById('violationForm');
        if (form) {
            form.reset();
        }

        this.clearFileInput();
        this.updatePointsDisplay(0);
        
        // Reset datetime to now
        const datetimeInput = document.getElementById('violationDateTime');
        if (datetimeInput) {
            const now = new Date();
            datetimeInput.value = now.toISOString().slice(0, 16);
        }
    }

    /**
     * ซ่อน modal
     */
    hideModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('newViolationModal'));
        if (modal) {
            modal.hide();
        }
    }

    /**
     * เมื่อบันทึกรายงานสำเร็จ
     */
    onReportSubmitted(reportData) {
        // Trigger custom event
        const event = new CustomEvent('reportSubmitted', {
            detail: { report: reportData }
        });
        document.dispatchEvent(event);

        // Update UI if needed
        if (typeof window.refreshDashboard === 'function') {
            window.refreshDashboard();
        }
    }

    /**
     * ตั้งค่าการตรวจสอบฟอร์ม
     */
    setupFormValidation() {
        // Add real-time validation if needed
        const form = document.getElementById('violationForm');
        if (!form) return;

        // Example: Real-time validation for description length
        const descriptionInput = document.getElementById('violationDescription');
        if (descriptionInput) {
            descriptionInput.addEventListener('input', (e) => {
                const maxLength = 1000;
                const current = e.target.value.length;
                const counter = document.getElementById('descriptionCounter');
                
                if (counter) {
                    counter.textContent = `${current}/${maxLength}`;
                    counter.className = current > maxLength ? 'text-danger' : 'text-muted';
                }
            });
        }
    }

    // Utility Methods
    getCategoryLabel(category) {
        const labels = {
            'light': 'ความผิดเบา',
            'medium': 'ความผิดปานกลาง',
            'severe': 'ความผิดรุนแรง',
            'other': 'อื่นๆ'
        };
        return labels[category] || 'ไม่ระบุ';
    }

    getPointsClass(points) {
        const absPoints = Math.abs(points);
        if (absPoints >= 10) return 'points-severe';
        if (absPoints >= 5) return 'points-medium';
        return 'points-light';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    showLoading(show) {
        const button = document.querySelector('#violationForm button[type="submit"]');
        if (!button) return;

        if (show) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังบันทึก...';
        } else {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-save me-2"></i>บันทึกรายงาน';
        }
    }

    showSuccess(message) {
        // Implementation depends on your notification system
        if (typeof Swal !== 'undefined') {
            Swal.fire('สำเร็จ', message, 'success');
        } else {
            alert(message);
        }
    }

    showError(message) {
        // Implementation depends on your notification system
        if (typeof Swal !== 'undefined') {
            Swal.fire('เกิดข้อผิดพลาด', message, 'error');
        } else {
            alert(message);
        }
    }
}

// Export for use
window.BehaviorReportManager = BehaviorReportManager;

// Create main instance
window.behaviorReportManager = new BehaviorReportManager();
