// Set current date
document.addEventListener('DOMContentLoaded', function() {
    // Set current date in Thai format
    const today = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const thaiDate = today.toLocaleDateString('th-TH', options);
    document.querySelector('.current-date').textContent = thaiDate;
    
    // Initialize charts
    initViolationTrendChart();
    initViolationTypesChart();
    
    // Mobile navigation active state
    const navLinks = document.querySelectorAll('.bottom-navbar .nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(item => {
                item.classList.remove('text-primary-app');
            });
            this.classList.add('text-primary-app');
        });
    });
    
    // Sidebar navigation active state
    const menuItems = document.querySelectorAll('.sidebar-menu .menu-item');
    menuItems.forEach(item => {
        if (!item.hasAttribute('data-bs-toggle')) {
            item.addEventListener('click', function(e) {
                menuItems.forEach(menuItem => {
                    menuItem.classList.remove('active');
                });
                this.classList.add('active');
            });
        }
    });
    
    // Student search in violation modal
    const studentSearchInput = document.querySelector('#newViolationModal input[placeholder="พิมพ์ชื่อหรือรหัสนักเรียน..."]');
    const selectedStudentContainer = document.querySelector('.selected-student');
    
    if (studentSearchInput) {
        studentSearchInput.addEventListener('keyup', function(e) {
            // Simulating search - in a real app, this would be an AJAX call
            if (e.key === 'Enter' && this.value.trim() !== '') {
                // Show selected student (this is just a mockup)
                selectedStudentContainer.classList.remove('d-none');
                this.value = '';
            }
        });
        
        // Remove selected student
        const removeStudentBtn = document.querySelector('.selected-student .btn-close');
        if (removeStudentBtn) {
            removeStudentBtn.addEventListener('click', function() {
                selectedStudentContainer.classList.add('d-none');
            });
        }
    }
    
    // Date restriction for violation date (max 3 days in the past)
    const dateInput = document.querySelector('#newViolationModal input[type="date"]');
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
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // สำหรับแสดงฟอร์มเพิ่มประเภทพฤติกรรมใหม่
    const btnShowAddViolationType = document.getElementById('btnShowAddViolationType');
    const violationTypesList = document.getElementById('violationTypesList');
    const violationTypeForm = document.getElementById('violationTypeForm');
    const btnCloseViolationForm = document.getElementById('btnCloseViolationForm');
    const btnCancelViolationType = document.getElementById('btnCancelViolationType');
    const formViolationTitle = document.getElementById('formViolationTitle');
    const formViolationType = document.getElementById('formViolationType');
    
    // ปุ่มแสดงฟอร์มเพิ่มใหม่
    if (btnShowAddViolationType) {
        btnShowAddViolationType.addEventListener('click', function() {
            // รีเซ็ตฟอร์ม
            formViolationType.reset();
            document.getElementById('violationTypeId').value = '';
            formViolationTitle.textContent = 'เพิ่มประเภทพฤติกรรมใหม่';
            
            // แสดงฟอร์ม ซ่อนรายการ
            violationTypesList.classList.add('d-none');
            violationTypeForm.classList.remove('d-none');
        });
    }
    
    // ปุ่มปิดฟอร์ม
    if (btnCloseViolationForm) {
        btnCloseViolationForm.addEventListener('click', function() {
            violationTypeForm.classList.add('d-none');
            violationTypesList.classList.remove('d-none');
        });
    }
    
    // ปุ่มยกเลิกในฟอร์ม
    if (btnCancelViolationType) {
        btnCancelViolationType.addEventListener('click', function() {
            violationTypeForm.classList.add('d-none');
            violationTypesList.classList.remove('d-none');
        });
    }
    
    // จัดการปุ่มแก้ไข
    const editButtons = document.querySelectorAll('.edit-violation-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            
            // ในสถานการณ์จริงต้อง fetch ข้อมูลจาก API
            // นี่เป็นเพียงตัวอย่าง mockup:
            let mockData = {
                violations_id: violationId,
                violations_name: '',
                violations_category: '',
                violations_points_deducted: 0,
                violations_description: ''
            };
            
            if (violationId === '1') {
                mockData = {
                    violations_id: '1',
                    violations_name: 'ผิดระเบียบการแต่งกาย',
                    violations_category: 'medium',
                    violations_points_deducted: 5,
                    violations_description: 'นักเรียนแต่งกายไม่ถูกระเบียบตามข้อกำหนดของโรงเรียน'
                };
            } else if (violationId === '2') {
                mockData = {
                    violations_id: '2',
                    violations_name: 'มาสาย',
                    violations_category: 'light',
                    violations_points_deducted: 3,
                    violations_description: 'นักเรียนมาโรงเรียนหลังเวลา 08:00 น.'
                };
            } else if (violationId === '3') {
                mockData = {
                    violations_id: '3',
                    violations_name: 'ทะเลาะวิวาท',
                    violations_category: 'severe',
                    violations_points_deducted: 20,
                    violations_description: 'นักเรียนก่อเหตุทะเลาะวิวาท ทำร้ายร่างกายผู้อื่น'
                };
            }
            
            // เติมข้อมูลลงในฟอร์ม
            document.getElementById('violationTypeId').value = mockData.violations_id;
            document.getElementById('violations_name').value = mockData.violations_name;
            document.getElementById('violations_category').value = mockData.violations_category;
            document.getElementById('violations_points_deducted').value = mockData.violations_points_deducted;
            document.getElementById('violations_description').value = mockData.violations_description;
            
            // เปลี่ยนชื่อหัวฟอร์ม
            formViolationTitle.textContent = 'แก้ไขประเภทพฤติกรรม';
            
            // แสดงฟอร์ม ซ่อนรายการ
            violationTypesList.classList.add('d-none');
            violationTypeForm.classList.remove('d-none');
        });
    });
    
    // จัดการปุ่มลบ
    const deleteButtons = document.querySelectorAll('.delete-violation-btn');
    const deleteViolationModal = new bootstrap.Modal(document.getElementById('deleteViolationModal'));
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const violationId = this.getAttribute('data-id');
            document.getElementById('deleteViolationId').value = violationId;
            deleteViolationModal.show();
        });
    });
    
    // ปุ่มยืนยันการลบ
    const confirmDeleteBtn = document.getElementById('confirmDeleteViolation');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const violationId = document.getElementById('deleteViolationId').value;
            
            // ในสถานการณ์จริงต้องส่ง request ไปลบข้อมูลในฐานข้อมูล
            console.log('Deleting violation with ID: ' + violationId);
            
            // จำลองการลบสำเร็จ
            // show notification toast
            const toast = new bootstrap.Toast(document.createElement('div'));
            
            // ปิด modal ยืนยันการลบ
            deleteViolationModal.hide();
            
            // ในสถานการณ์จริง ควรมีการแสดงผลลัพธ์การลบและ refresh ข้อมูลใหม่
        });
    }
    
    // การบันทึกฟอร์ม
    if (formViolationType) {
        formViolationType.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // ตรวจสอบความถูกต้องของฟอร์ม
            if (this.checkValidity()) {
                // อ่านข้อมูลจากฟอร์ม
                const formData = {
                    violations_id: document.getElementById('violationTypeId').value,
                    violations_name: document.getElementById('violations_name').value,
                    violations_category: document.getElementById('violations_category').value,
                    violations_points_deducted: document.getElementById('violations_points_deducted').value,
                    violations_description: document.getElementById('violations_description').value
                };
                
                // ในสถานการณ์จริงต้องส่ง request ไปเพิ่ม/แก้ไขข้อมูลในฐานข้อมูล
                console.log('Saving violation data:', formData);
                
                // จำลองการบันทึกสำเร็จ
                // show success toast
                
                // กลับไปแสดงรายการ
                violationTypeForm.classList.add('d-none');
                violationTypesList.classList.remove('d-none');
                
                // ในสถานการณ์จริง ควรมีการ refresh ข้อมูลใหม่
            } else {
                // แสดงข้อความเตือนกรณีกรอกข้อมูลไม่ครบ/ไม่ถูกต้อง
                this.classList.add('was-validated');
            }
        });
    }
    
    // ค้นหาประเภทพฤติกรรม
    const violationTypeSearch = document.getElementById('violationTypeSearch');
    if (violationTypeSearch) {
        violationTypeSearch.addEventListener('keyup', function(e) {
            if (e.key === 'Enter' || this.value.length > 2) {
                // ในสถานการณ์จริงต้องค้นหาข้อมูลจากฐานข้อมูล
                console.log('Searching for: ' + this.value);
                
                // จำลองแสดงผลการค้นหา
                // ...
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