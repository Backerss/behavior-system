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
                filterScoreValue.disabled = true;
                filterViolationValue.disabled = true;
                
                // รีเซ็ตตัวแปรตัวกรอง
                resetFilters();
            }
        });
    }
    
    // ปุ่มใช้ตัวกรอง
    const applyFilterBtn = document.getElementById('applyFilterBtn');
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            // อ่านค่าจากฟอร์ม
            studentFilter.name = document.getElementById('filter_name').value.trim();
            studentFilter.classLevel = document.getElementById('filter_class_level').value;
            studentFilter.classRoom = document.getElementById('filter_class_room').value;
            
            studentFilter.score.operator = document.getElementById('filter_score_operator').value;
            studentFilter.score.value = parseInt(document.getElementById('filter_score_value').value) || 75;
            
            studentFilter.violation.operator = document.getElementById('filter_violation_operator').value;
            studentFilter.violation.value = parseInt(document.getElementById('filter_violation_value').value) || 5;
            
            // อ่านค่าความเสี่ยง
            studentFilter.risk = [];
            if (document.getElementById('filter_risk_high').checked) {
                studentFilter.risk.push('high');
            }
            if (document.getElementById('filter_risk_medium').checked) {
                studentFilter.risk.push('medium');
            }
            if (document.getElementById('filter_risk_low').checked) {
                studentFilter.risk.push('low');
            }
            
            // นำค่าตัวกรองไปใช้
            applyFilters();
            
            // ปิด Modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('studentFilterModal'));
            if (modal) {
                modal.hide();
            }
        });
    }
    
    // ฟังก์ชันรีเซ็ตตัวกรอง
    function resetFilters() {
        studentFilter.name = '';
        studentFilter.classLevel = '';
        studentFilter.classRoom = '';
        studentFilter.score.operator = 'any';
        studentFilter.score.value = 75;
        studentFilter.violation.operator = 'any';
        studentFilter.violation.value = 5;
        studentFilter.risk = [];
        
        // แสดงข้อมูลทั้งหมดใหม่
        showAllStudents();
        
        // อัพเดตแบดจ์ตัวกรอง
        updateFilterBadge(0);
    }
    
    // ฟังก์ชันใช้ตัวกรอง
    function applyFilters() {
        // นับจำนวนตัวกรองที่เลือก
        let activeFilterCount = 0;
        
        // ตรวจสอบแต่ละตัวกรอง
        if (studentFilter.name) activeFilterCount++;
        if (studentFilter.classLevel) activeFilterCount++;
        if (studentFilter.classRoom) activeFilterCount++;
        if (studentFilter.score.operator !== 'any') activeFilterCount++;
        if (studentFilter.violation.operator !== 'any') activeFilterCount++;
        if (studentFilter.risk.length > 0) activeFilterCount++;
        
        // อัพเดตแบดจ์แสดงจำนวนตัวกรอง
        updateFilterBadge(activeFilterCount);
        
        // กรองข้อมูลนักเรียน
        filterStudents();
    }
    
    // ฟังก์ชันอัพเดตแบดจ์แสดงจำนวนตัวกรองที่ใช้งาน
    function updateFilterBadge(count) {
        const filterButton = document.querySelector('[data-bs-target="#studentFilterModal"] i.fas.fa-filter');
        
        if (filterButton) {
            // ลบ span เก่าถ้ามี
            const oldBadge = filterButton.nextElementSibling;
            if (oldBadge && oldBadge.classList.contains('filter-badge')) {
                oldBadge.remove();
            }
            
            // สร้างแบดจ์ใหม่ถ้ามีตัวกรองที่ใช้งาน
            if (count > 0) {
                const badge = document.createElement('span');
                badge.textContent = count;
                badge.className = 'filter-badge';
                badge.style.position = 'absolute';
                badge.style.top = '-5px';
                badge.style.right = '-5px';
                badge.style.fontSize = '10px';
                badge.style.background = '#1020AD';
                badge.style.color = 'white';
                badge.style.borderRadius = '50%';
                badge.style.width = '16px';
                badge.style.height = '16px';
                badge.style.display = 'flex';
                badge.style.alignItems = 'center';
                badge.style.justifyContent = 'center';
                
                // เพิ่ม position: relative ให้กับ parent ถ้ายังไม่มี
                filterButton.parentElement.style.position = 'relative';
                
                // เพิ่มแบดจ์เข้าไป
                filterButton.parentElement.appendChild(badge);
            }
        }
    }
    
    // ฟังก์ชันแสดงนักเรียนทั้งหมด
    function showAllStudents() {
        const tableRows = document.querySelectorAll('#students .table tbody tr');
        
        tableRows.forEach(row => {
            row.style.display = '';
        });
        
        // อัพเดตข้อความแสดงจำนวนรายการ
        updateResultCount(tableRows.length);
    }
    
    // ฟังก์ชันกรองข้อมูลนักเรียน
    function filterStudents() {
        const tableRows = document.querySelectorAll('#students .table tbody tr');
        let visibleCount = 0;
        
        // ตรวจสอบทุกแถว
        tableRows.forEach(row => {
            // ซ่อนแถวก่อน
            row.style.display = 'none';
            
            // ข้ามถ้าเป็นแถวข้อความ "ไม่พบข้อมูล"
            if (row.querySelector('.text-muted')) {
                return;
            }
            
            // ดึงข้อมูลจากแถว
            const studentCode = row.cells[0].textContent.trim();
            const studentName = row.cells[1].querySelector('span').textContent.trim();
            const classInfo = row.cells[2].textContent.trim();
            let classLevel = '';
            let classRoom = '';
            
            // แยกระดับชั้นและห้อง
            if (classInfo && classInfo !== '-') {
                const parts = classInfo.split('/');
                if (parts.length === 2) {
                    classLevel = parts[0].trim();
                    classRoom = parts[1].trim();
                }
            }
            
            // คะแนน
            const scoreText = row.cells[3].querySelector('.small').textContent.trim();
            const score = parseInt(scoreText.split('/')[0]) || 0;
            
            // จำนวนครั้งที่ทำผิด
            const violationText = row.cells[4].textContent.trim();
            const violationCount = parseInt(violationText) || 0;
            
            // กำหนดระดับความเสี่ยง
            let riskLevel = '';
            if (score <= 60) {
                riskLevel = 'high';
            } else if (score <= 75) {
                riskLevel = 'medium';
            } else {
                riskLevel = 'low';
            }
            
            // ตรวจสอบตามเงื่อนไขทั้งหมด
            let showRow = true;
            
            // กรองตามชื่อหรือรหัสนักเรียน
            if (studentFilter.name && 
                !studentCode.toLowerCase().includes(studentFilter.name.toLowerCase()) && 
                !studentName.toLowerCase().includes(studentFilter.name.toLowerCase())) {
                showRow = false;
            }
            
            // กรองตามระดับชั้น
            if (studentFilter.classLevel && classLevel !== studentFilter.classLevel) {
                showRow = false;
            }
            
            // กรองตามห้อง
            if (studentFilter.classRoom && classRoom !== studentFilter.classRoom) {
                showRow = false;
            }
            
            // กรองตามคะแนน
            if (studentFilter.score.operator !== 'any') {
                switch (studentFilter.score.operator) {
                    case 'less':
                        if (!(score < studentFilter.score.value)) showRow = false;
                        break;
                    case 'more':
                        if (!(score > studentFilter.score.value)) showRow = false;
                        break;
                    case 'equal':
                        if (score !== studentFilter.score.value) showRow = false;
                        break;
                }
            }
            
            // กรองตามจำนวนครั้งที่ทำผิด
            if (studentFilter.violation.operator !== 'any') {
                switch (studentFilter.violation.operator) {
                    case 'less':
                        if (!(violationCount < studentFilter.violation.value)) showRow = false;
                        break;
                    case 'more':
                        if (!(violationCount > studentFilter.violation.value)) showRow = false;
                        break;
                    case 'equal':
                        if (violationCount !== studentFilter.violation.value) showRow = false;
                        break;
                }
            }
            
            // กรองตามความเสี่ยง
            if (studentFilter.risk.length > 0 && !studentFilter.risk.includes(riskLevel)) {
                showRow = false;
            }
            
            // แสดงแถวถ้าผ่านเงื่อนไขทั้งหมด
            if (showRow) {
                row.style.display = '';
                visibleCount++;
            }
        });
        
        // แสดงข้อความถ้าไม่พบข้อมูล
        if (visibleCount === 0) {
            // ตรวจสอบถ้ามีข้อความว่าไม่พบข้อมูลอยู่แล้ว
            if (!document.querySelector('#students .table tbody tr.no-results')) {
                const tbody = document.querySelector('#students .table tbody');
                if (tbody) {
                    const noResultRow = document.createElement('tr');
                    noResultRow.className = 'no-results';
                    noResultRow.innerHTML = `
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-filter fa-2x mb-3"></i>
                                <p>ไม่พบข้อมูลนักเรียนที่ตรงกับเงื่อนไข</p>
                                <button class="btn btn-sm btn-outline-secondary mt-2" onclick="resetStudentFilters()">
                                    <i class="fas fa-sync-alt me-1"></i> รีเซ็ตตัวกรอง
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(noResultRow);
                }
            }
        } else {
            // ลบข้อความไม่พบข้อมูลถ้ามี
            const noResultRow = document.querySelector('#students .table tbody tr.no-results');
            if (noResultRow) {
                noResultRow.remove();
            }
        }
        
        // อัพเดตข้อความแสดงจำนวนรายการ
        updateResultCount(visibleCount);
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
    document.addEventListener('studentTableUpdated', function() {
        // Re-apply the quick search and modal filters (if any)
        applyQuickSearch();
        try { applyFilters(); } catch (e) { /* ignore if modal filter not used */ }
    });

    function initQuickSearch() {
        const form = document.getElementById('studentSearchForm');
        const input = document.getElementById('studentSearchInput');
        const btn = document.getElementById('studentSearchBtn');
        if (!form || !input || !btn) return;
        form.addEventListener('submit', function(e){ e.preventDefault(); });
        btn.addEventListener('click', function(e){ e.preventDefault(); applyQuickSearch(); });
        input.addEventListener('input', debounce(applyQuickSearch, 150));
        // Initial run
        applyQuickSearch();
    }

    function applyQuickSearch() {
        const input = document.getElementById('studentSearchInput');
        const q = (input?.value || '').toString().toLowerCase().trim();
        const tbody = document.querySelector('#students table tbody');
        if (!tbody) return;
        const rows = Array.from(tbody.querySelectorAll('tr[data-student-row="1"]'));
        let shown = 0;
        rows.forEach(function(row){
            const code = (row.cells[0]?.innerText || '').toLowerCase();
            const name = (row.cells[1]?.innerText || '').toLowerCase();
            const cls = (row.cells[2]?.innerText || '').toLowerCase();
            const combined = code + ' ' + name + ' ' + cls;
            const match = q === '' || combined.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) shown++;
        });
        // Handle no-results row (prefer existing server empty row)
        let noRow = tbody.querySelector('tr[data-empty-row="1"]') || tbody.querySelector('tr[data-no-results="1"]');
        if (!noRow) {
            noRow = document.createElement('tr');
            noRow.setAttribute('data-no-results', '1');
            const td = document.createElement('td');
            td.colSpan = 6;
            td.className = 'text-center py-4 text-muted';
            td.innerHTML = '<i class="fas fa-info-circle me-2"></i>ไม่พบข้อมูลที่ตรงกับคำค้นหา';
            noRow.appendChild(td);
            noRow.style.display = 'none';
            tbody.appendChild(noRow);
        }
        noRow.style.display = shown === 0 ? '' : 'none';
        // Update footer small count (not the header total)
        try { updateResultCount(shown); } catch (e) {}
    }

    function initAjaxPagination() {
        const nav = document.querySelector('#studentsPagination');
        if (!nav) return;
        nav.addEventListener('click', onPageClick);
        window.addEventListener('popstate', function(){ loadPage(location.href); });
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
        const card = document.querySelector('#students .card');
        if (card) card.classList.add('opacity-50');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTbody = doc.querySelector('#students table tbody');
                const newNav = doc.querySelector('#students .card-footer nav');
                const table = document.querySelector('#students table');
                const tbody = table && table.querySelector('tbody');
                const navContainer = document.querySelector('#students .card-footer nav');
                if (newTbody && tbody) tbody.replaceWith(newTbody);
                if (newNav && navContainer) navContainer.replaceWith(newNav);
                const updatedNav = document.querySelector('#students .card-footer nav');
                if (updatedNav && !updatedNav.id) updatedNav.id = 'studentsPagination';
                // Re-bind pagination clicks
                const newNavEl = document.querySelector('#studentsPagination');
                if (newNavEl) newNavEl.addEventListener('click', onPageClick);
                // Fire update event for filters/search to re-apply
                document.dispatchEvent(new CustomEvent('studentTableUpdated'));
                if (history.pushState) history.pushState({}, '', url);
            })
            .catch(err => console.error(err))
            .finally(() => { if (card) card.classList.remove('opacity-50'); });
    }

    // Simple debounce utility for quick search
    function debounce(fn, wait) {
        let t;
        return function() {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, arguments), wait);
        };
    }
});