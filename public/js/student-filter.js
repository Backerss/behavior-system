/**
 * ระบบกรองข้อมูลนักเรียน
 */

document.addEventListener('DOMContentLoaded', function() {
    // ตัวแปรสำหรับเก็บค่าตัวกรอง
    const studentFilter = {
        name: '',
        classLevel: '',
        classRoom: '',
        score: {
            operator: 'any',
            value: 75
        },
        violation: {
            operator: 'any',
            value: 5
        },
        risk: []
    };
    
    // เพิ่ม Event Listener สำหรับตัวกรองคะแนน
    const filterScoreOperator = document.getElementById('filter_score_operator');
    const filterScoreValue = document.getElementById('filter_score_value');
    
    if (filterScoreOperator) {
        filterScoreOperator.addEventListener('change', function() {
            if (this.value === 'any') {
                filterScoreValue.disabled = true;
            } else {
                filterScoreValue.disabled = false;
            }
        });
    }
    
    // เพิ่ม Event Listener สำหรับตัวกรองจำนวนครั้ง
    const filterViolationOperator = document.getElementById('filter_violation_operator');
    const filterViolationValue = document.getElementById('filter_violation_value');
    
    if (filterViolationOperator) {
        filterViolationOperator.addEventListener('change', function() {
            if (this.value === 'any') {
                filterViolationValue.disabled = true;
            } else {
                filterViolationValue.disabled = false;
            }
        });
    }
    
    // ปุ่มรีเซ็ตตัวกรอง
    const resetFilterBtn = document.getElementById('resetFilterBtn');
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            const form = document.getElementById('studentFilterForm');
            if (form) {
                form.reset();
                
                // รีเซ็ตสถานะ disabled ของ input
                if (filterScoreValue) filterScoreValue.disabled = true;
                if (filterViolationValue) filterViolationValue.disabled = true;
                
                // ดึงข้อมูลทั้งหมดใหม่โดยลบตัวกรองทั้งหมด
                const url = new URL(window.location.href);
                url.searchParams.delete('search');
                url.searchParams.delete('filter_name');
                url.searchParams.delete('filter_class_level');
                url.searchParams.delete('filter_class_room');
                url.searchParams.delete('filter_score_operator');
                url.searchParams.delete('filter_score_value');
                url.searchParams.delete('filter_violation_operator');
                url.searchParams.delete('filter_violation_value');
                url.searchParams.delete('filter_risk');
                url.searchParams.delete('page');
                
                performFilterSearch(url.toString());
            }
        });
    }
    
    // ปุ่มใช้ตัวกรอง
    const applyFilterBtn = document.getElementById('applyFilterBtn');
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            // สร้าง URL พร้อม query string สำหรับตัวกรอง
            const url = new URL(window.location.href);
            
            // อ่านค่าจากฟอร์ม
            const name = document.getElementById('filter_name').value.trim();
            const classLevel = document.getElementById('filter_class_level').value;
            const classRoom = document.getElementById('filter_class_room').value;
            const scoreOp = document.getElementById('filter_score_operator').value;
            const scoreVal = document.getElementById('filter_score_value').value;
            const violationOp = document.getElementById('filter_violation_operator').value;
            const violationVal = document.getElementById('filter_violation_value').value;
            
            const riskHigh = document.getElementById('filter_risk_high').checked;
            const riskMedium = document.getElementById('filter_risk_medium').checked;
            const riskLow = document.getElementById('filter_risk_low').checked;
            
            // ล้าง query parameters ก่อน
            url.searchParams.delete('search');
            url.searchParams.delete('filter_name');
            url.searchParams.delete('filter_class_level');
            url.searchParams.delete('filter_class_room');
            url.searchParams.delete('filter_score_operator');
            url.searchParams.delete('filter_score_value');
            url.searchParams.delete('filter_violation_operator');
            url.searchParams.delete('filter_violation_value');
            url.searchParams.delete('filter_risk');
            url.searchParams.delete('page');
            
            // เพิ่ม query parameters ตัวกรอง
            if (name) url.searchParams.set('filter_name', name);
            if (classLevel) url.searchParams.set('filter_class_level', classLevel);
            if (classRoom) url.searchParams.set('filter_class_room', classRoom);
            if (scoreOp !== 'any') {
                url.searchParams.set('filter_score_operator', scoreOp);
                url.searchParams.set('filter_score_value', scoreVal);
            }
            if (violationOp !== 'any') {
                url.searchParams.set('filter_violation_operator', violationOp);
                url.searchParams.set('filter_violation_value', violationVal);
            }
            
            // รวม risk status
            const risks = [];
            if (riskHigh) risks.push('high');
            if (riskMedium) risks.push('medium');
            if (riskLow) risks.push('low');
            if (risks.length > 0) {
                url.searchParams.set('filter_risk', risks.join(','));
            }
            
            // ดึงข้อมูลจาก server
            performFilterSearch(url.toString());
            
            // ปิด Modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('studentFilterModal'));
            if (modal) {
                modal.hide();
            }
        });
    }
    
    // ฟังก์ชันการค้นหาตัวกรองจาก server
    function performFilterSearch(url) {
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Parse HTML response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // ดึง elements ที่ต้องการอัพเดต
            const newTbody = doc.querySelector('#students table tbody');
            const newNav = doc.querySelector('#students .card-footer nav');
            const newCountLabel = doc.querySelector('#studentCountLabel');
            
            // อัพเดต table body
            const currentTbody = document.querySelector('#students table tbody');
            if (newTbody && currentTbody) {
                currentTbody.innerHTML = newTbody.innerHTML;
            }
            
            // อัพเดต pagination
            const currentNav = document.querySelector('#students .card-footer nav');
            if (newNav && currentNav) {
                currentNav.innerHTML = newNav.innerHTML;
            }
            
            // อัพเดต count label
            const currentCountLabel = document.querySelector('#studentCountLabel');
            if (newCountLabel && currentCountLabel) {
                currentCountLabel.innerHTML = newCountLabel.innerHTML;
            }
            
            // อัพเดต URL โดยไม่ reload หน้า
            if (history.pushState) {
                history.pushState({}, '', url);
            }
            
            // Re-bind pagination clicks
            initAjaxPagination();
        })
        .catch(error => {
            console.error('Filter error:', error);
            alert('เกิดข้อผิดพลาดในการกรองข้อมูล กรุณาลองใหม่อีกครั้ง');
        });
    }
    
    // ฟังก์ชันรีเซ็ตตัวกรอง (deprecated - ไม่ใช้แล้ว)
    function resetFilters() {
        // ไม่ใช้แล้ว ใช้ performFilterSearch แทน
    }
    
    // ฟังก์ชันใช้ตัวกรอง (deprecated - ไม่ใช้แล้ว)
    function applyFilters() {
        // ไม่ใช้แล้ว ใช้ performFilterSearch แทน
    }
    
    // ฟังก์ชันอัพเดตแบดจ์แสดงจำนวนตัวกรองที่ใช้งาน (deprecated - ไม่ใช้แล้ว)
    function updateFilterBadge(count) {
        // ไม่ใช้แล้ว
    }
    
    // ฟังก์ชันแสดงนักเรียนทั้งหมด (deprecated - ไม่ใช้แล้ว)
    function showAllStudents() {
        // ไม่ใช้แล้ว
    }
    
    // ฟังก์ชันกรองข้อมูลนักเรียน (deprecated - ไม่ใช้แล้ว)
    function filterStudents() {
        // ไม่ใช้แล้ว
    }
    
    // ฟังก์ชันอัพเดตข้อความแสดงจำนวนรายการ
    function updateResultCount(count) {
        // ตรวจสอบถ้ามี element แสดงจำนวนรายการ
        let countElement = document.querySelector('#student-result-count');
        
        // ถ้าไม่มีให้สร้างใหม่
        if (!countElement) {
            countElement = document.createElement('div');
            countElement.id = 'student-result-count';
            countElement.className = 'text-muted small mt-2';
            
            // หา card-footer เพื่อใส่ element
            const cardFooter = document.querySelector('#students .card-footer');
            if (cardFooter) {
                // ใส่ก่อน pagination
                const pagination = cardFooter.querySelector('nav');
                if (pagination) {
                    cardFooter.insertBefore(countElement, pagination);
                } else {
                    cardFooter.appendChild(countElement);
                }
            }
        }
        
        // อัพเดตข้อความ
        if (countElement) {
            countElement.textContent = `แสดงข้อมูล ${count} รายการ`;
        }
    }
    
    // ฟังก์ชันสำหรับเรียกใช้จากภายนอก (global)
    window.resetStudentFilters = resetFilters;
    window.applyStudentFilters = applyFilters;

    // -----------------------------
    // Quick search (header input) and AJAX pagination for students table
    // -----------------------------
    initQuickSearch();
    initAjaxPagination();

    // Re-apply filters/search when table is updated via AJAX
    // ลบ event listener ที่ไม่จำเป็นออก เนื่องจากใช้ server-side search
    // document.addEventListener('studentTableUpdated', ...) - ไม่ใช้แล้ว

    function initQuickSearch() {
        const form = document.getElementById('studentSearchForm');
        const input = document.getElementById('studentSearchInput');
        const btn = document.getElementById('studentSearchBtn');
        
        if (!form || !input || !btn) return;
        
        // ป้องกันการ submit form
        form.addEventListener('submit', function(e){ 
            e.preventDefault(); 
            performServerSearch();
        });
        
        // เมื่อกดปุ่มค้นหา
        btn.addEventListener('click', function(e){ 
            e.preventDefault(); 
            performServerSearch();
        });
        
        // เมื่อพิมพ์ในช่องค้นหา - ค้นหาแบบ real-time
        input.addEventListener('input', debounce(function() {
            performServerSearch();
        }, 500));
        
        // เมื่อกด Enter ในช่องค้นหา
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performServerSearch();
            }
        });
        
        // เคลียร์การค้นหาเมื่อลบข้อความทั้งหมด
        input.addEventListener('keyup', function(e) {
            if (this.value.trim() === '') {
                performServerSearch();
            }
        });
    }
    
    function performServerSearch() {
        const input = document.getElementById('studentSearchInput');
        const searchTerm = input ? input.value.trim() : '';
        
        // สร้าง URL พร้อม query string
        const url = new URL(window.location.href);
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        // รีเซ็ตไปหน้าแรก
        url.searchParams.delete('page');
        
        // ดึงข้อมูลจาก server
        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Parse HTML response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // ดึง elements ที่ต้องการอัพเดต
            const newTbody = doc.querySelector('#students table tbody');
            const newNav = doc.querySelector('#students .card-footer nav');
            const newCountLabel = doc.querySelector('#studentCountLabel');
            
            // อัพเดต table body
            const currentTbody = document.querySelector('#students table tbody');
            if (newTbody && currentTbody) {
                currentTbody.innerHTML = newTbody.innerHTML;
            }
            
            // อัพเดต pagination
            const currentNav = document.querySelector('#students .card-footer nav');
            if (newNav && currentNav) {
                currentNav.innerHTML = newNav.innerHTML;
            }
            
            // อัพเดต count label
            const currentCountLabel = document.querySelector('#studentCountLabel');
            if (newCountLabel && currentCountLabel) {
                currentCountLabel.innerHTML = newCountLabel.innerHTML;
            }
            
            // อัพเดต URL โดยไม่ reload หน้า
            if (history.pushState) {
                history.pushState({}, '', url.toString());
            }
            
            // Re-bind pagination clicks
            initAjaxPagination();
        })
        .catch(error => {
            console.error('Search error:', error);
            // แสดงข้อความ error
            alert('เกิดข้อผิดพลาดในการค้นหา กรุณาลองใหม่อีกครั้ง');
        });
    }

    // ฟังก์ชันนี้ไม่ใช้แล้ว เนื่องจากใช้ server-side search แทน
    function applyQuickSearch() {
        // Deprecated - ใช้ performServerSearch() แทน
        performServerSearch();
    }

    function initAjaxPagination() {
        const nav = document.querySelector('#studentsPagination');
        if (!nav) return;
        nav.addEventListener('click', onPageClick);
        window.addEventListener('popstate', function(){ 
            // รีโหลดหน้าจาก URL ปัจจุบัน
            window.location.reload();
        });
    }

    function onPageClick(e) {
        const a = e.target.closest('a');
        if (!a) return;
        const url = a.getAttribute('href');
        if (!url || url === '#') return;
        e.preventDefault();
        loadPage(url);
    }

    function loadPage(url) {
        fetch(url, { 
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
            .then(r => {
                if (!r.ok) {
                    throw new Error('Network response was not ok');
                }
                return r.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTbody = doc.querySelector('#students table tbody');
                const newNav = doc.querySelector('#students .card-footer nav');
                const newCountLabel = doc.querySelector('#studentCountLabel');
                
                const table = document.querySelector('#students table');
                const tbody = table && table.querySelector('tbody');
                const navContainer = document.querySelector('#students .card-footer nav');
                const currentCountLabel = document.querySelector('#studentCountLabel');
                
                if (newTbody && tbody) tbody.innerHTML = newTbody.innerHTML;
                if (newNav && navContainer) navContainer.innerHTML = newNav.innerHTML;
                if (newCountLabel && currentCountLabel) currentCountLabel.innerHTML = newCountLabel.innerHTML;
                
                // Re-bind pagination clicks
                initAjaxPagination();
                
                if (history.pushState) history.pushState({}, '', url);
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล กรุณาลองใหม่อีกครั้ง');
            });
    }

    // Simple debounce utility for quick search
    function debounce(fn, wait) {
        let t;
        return function() {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, arguments), wait);
        };
    }
    
    // Global functions สำหรับใช้จากนอก
    window.resetStudentFilters = function() {
        const form = document.getElementById('studentFilterForm');
        if (form) {
            form.reset();
            
            // รีเซ็ตสถานะ disabled ของ input
            if (filterScoreValue) filterScoreValue.disabled = true;
            if (filterViolationValue) filterViolationValue.disabled = true;
            
            // ดึงข้อมูลทั้งหมดใหม่โดยลบตัวกรองทั้งหมด
            const url = new URL(window.location.href);
            url.searchParams.delete('search');
            url.searchParams.delete('filter_name');
            url.searchParams.delete('filter_class_level');
            url.searchParams.delete('filter_class_room');
            url.searchParams.delete('filter_score_operator');
            url.searchParams.delete('filter_score_value');
            url.searchParams.delete('filter_violation_operator');
            url.searchParams.delete('filter_violation_value');
            url.searchParams.delete('filter_risk');
            url.searchParams.delete('page');
            
            performFilterSearch(url.toString());
        }
    };
});
