// Google Sheets Import JavaScript for Admin
$(document).ready(function () {
    let googleSheetsPreviewData = null;
    
    // Toast Notification Function
    function showToast(type, title, message) {
        const toastId = 'toast-' + Date.now();
        const iconClass = {
            'success': 'fas fa-check-circle text-success',
            'error': 'fas fa-times-circle text-danger',
            'warning': 'fas fa-exclamation-triangle text-warning',
            'info': 'fas fa-info-circle text-info'
        };

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        <div class="d-flex align-items-center">
                            <i class="${iconClass[type] || iconClass.info} me-2"></i>
                            <div>
                                <strong>${title}</strong><br>
                                <small>${message}</small>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        $('body').append(toastHtml);
        const toast = new bootstrap.Toast(document.getElementById(toastId), {
            autohide: true,
            delay: type === 'error' ? 8000 : 5000
        });
        toast.show();

        // Remove toast after hiding
        document.getElementById(toastId).addEventListener('hidden.bs.toast', function () {
            this.remove();
        });
    }

    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Global Variables
    selectedSheetType = 'students'; // default

    // Load Available Sheets when modal opens
    $('#googleSheetsImportModal').on('show.bs.modal', function () {
        loadAvailableSheets();
    });

    // Load Available Sheets
    function loadAvailableSheets() {
        $.ajax({
            url: window.adminGoogleSheetsRoutes.sheets,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    populateSheetSelection(response.sheets);
                } else {
                    $('#sheetSelectionContainer').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> 
                            ไม่สามารถโหลดรายการแผ่นข้อมูลได้: ${response.error}
                        </div>
                    `);
                }
            },
            error: function (xhr) {
                $('#sheetSelectionContainer').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์
                    </div>
                `);
            }
        });
    }

    // Populate Sheet Selection
    function populateSheetSelection(sheets) {
        let html = '<div class="row">';

        Object.keys(sheets).forEach(function (sheetKey) {
            const sheet = sheets[sheetKey];
            const isSelected = sheetKey === selectedSheetType ? 'active' : '';

            html += `
                <div class="col-md-4 mb-3">
                    <div class="card sheet-card ${isSelected}" data-sheet="${sheetKey}" style="cursor: pointer;">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="fas fa-file-alt text-primary"></i>
                                ${sheet.name}
                            </h5>
                            <p class="card-text text-muted">${sheet.description}</p>
                            <div class="mt-2">
                                <span class="badge bg-secondary">${sheet.role}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        $('#sheetSelectionContainer').html(html);

        // Add click handlers
        $('.sheet-card').click(function () {
            const sheetType = $(this).data('sheet');
            selectSheet(sheetType, sheets[sheetType]);
        });
    }

    // Select Sheet
    function selectSheet(sheetType, sheetInfo) {
        selectedSheetType = sheetType;

        // Update visual selection
        $('.sheet-card').removeClass('active');
        $(`.sheet-card[data-sheet="${sheetType}"]`).addClass('active');

        // Show sheet info
        $('#sheetDescription').text(sheetInfo.description);
        $('#expectedColumns').text(sheetInfo.expected_columns.join(', '));
        $('#selectedSheetInfo').removeClass('d-none');

        // Reset preview
        $('#googleSheetsPreviewContainer').addClass('d-none');
        googleSheetsPreviewData = null;
    }

    // Preview Button Click
    $('#previewGoogleSheetsBtn').click(function () {
        if (!selectedSheetType) {
            showToast('warning', 'กรุณาเลือกแผ่นข้อมูล', 'เลือกแผ่นข้อมูลที่ต้องการนำเข้าก่อน');
            return;
        }

        $(this).prop('disabled', true);
        $('#googleSheetsLoading').removeClass('d-none');

        $.ajax({
            url: window.adminGoogleSheetsRoutes.preview,
            method: 'GET',
            data: {
                sheet: selectedSheetType
            },
            success: function (response) {
                if (response.success) {
                    googleSheetsPreviewData = response.data;
                    populateGoogleSheetsPreviewTables(response.data);
                    updateGoogleSheetsSummaryCards(response.data);
                    $('#googleSheetsPreviewContainer').removeClass('d-none');

                    // Show success message
                    showToast('success', 'ดึงข้อมูลสำเร็จ', 'พบข้อมูล ' + response.total_rows + ' รายการ');
                } else {
                    showToast('error', 'เกิดข้อผิดพลาด', response.error);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                showToast('error', 'เกิดข้อผิดพลาด', response ? response.error : 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
            },
            complete: function () {
                $('#previewGoogleSheetsBtn').prop('disabled', false);
                $('#googleSheetsLoading').addClass('d-none');
            }
        });
    });

    // Import Button Click
    $('#importGoogleSheetsBtn').click(function () {
        const selectedData = getSelectedGoogleSheetsValidData();
        
        if (selectedData.length === 0) {
            showToast('warning', 'แจ้งเตือน', 'กรุณาเลือกข้อมูลที่ต้องการนำเข้า');
            return;
        }

        // เตือนเมื่อข้อมูลเยอะ
        let warningMessage = `คุณต้องการนำเข้าข้อมูล ${selectedData.length} รายการหรือไม่?`;
        if (selectedData.length > 30) {
            warningMessage += `\n\n⚠️ ข้อมูลจำนวนมาก (${selectedData.length} รายการ) อาจใช้เวลาในการประมวลผลนานกว่าปกติ`;
        }

        if (!confirm(warningMessage)) {
            return;
        }
        
        // ตรวจสอบ CSRF token
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            showToast('error', 'เกิดข้อผิดพลาด', 'ไม่พบ CSRF Token กรุณารีเฟรชหน้าและลองใหม่');
            return;
        }

        $(this).prop('disabled', true);
        $('#googleSheetsImportLoading').removeClass('d-none');

        $.ajax({
            url: window.adminGoogleSheetsRoutes.import,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            data: {
                selected_data: selectedData,
                _token: csrfToken
            },
            timeout: 300000, // 5 นาที timeout
            success: function (response, textStatus, xhr) {
                // ตรวจสอบว่า response text มี PHP warning หรือไม่
                let cleanResponse = response;
                if (xhr.responseText && xhr.responseText.includes('<b>Warning</b>')) {
                    try {
                        // หา JSON part จาก response text
                        const jsonStart = xhr.responseText.lastIndexOf('{');
                        if (jsonStart !== -1) {
                            const jsonPart = xhr.responseText.substring(jsonStart);
                            cleanResponse = JSON.parse(jsonPart);
                        }
                    } catch (parseError) {
                        // ใช้ response เดิม
                    }
                }
                
                if (cleanResponse && cleanResponse.success) {
                    showToast('success', 'นำเข้าข้อมูลสำเร็จ!',
                        'สำเร็จ: ' + (cleanResponse.results.success_count || 0) + ' รายการ\n' +
                        'ผิดพลาด: ' + (cleanResponse.results.error_count || 0) + ' รายการ');

                    // รีเซ็ตฟอร์ม
                    $('#googleSheetsPreviewContainer').addClass('d-none');
                    googleSheetsPreviewData = null;

                    // ปิด modal
                    $('#googleSheetsImportModal').modal('hide');

                    // Refresh หน้า
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showToast('error', 'เกิดข้อผิดพลาด', cleanResponse.error || 'ไม่สามารถนำเข้าข้อมูลได้');
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                // ตรวจสอบว่าเป็น success response ที่มี PHP warning หรือไม่
                if (xhr.status === 200 && xhr.responseText) {
                    try {
                        // ลองหา JSON ใน response text
                        const jsonStart = xhr.responseText.lastIndexOf('{');
                        if (jsonStart !== -1) {
                            const jsonPart = xhr.responseText.substring(jsonStart);
                            const parsedResponse = JSON.parse(jsonPart);
                            
                            if (parsedResponse.success) {
                                showToast('success', 'นำเข้าข้อมูลสำเร็จ!',
                                    'สำเร็จ: ' + (parsedResponse.results.success_count || 0) + ' รายการ\n' +
                                    'ผิดพลาด: ' + (parsedResponse.results.error_count || 0) + ' รายการ\n' +
                                    '(มีคำเตือนเล็กน้อยจาก PHP แต่ข้อมูลถูกบันทึกเรียบร้อย)');

                                // รีเซ็ตฟอร์ม
                                $('#googleSheetsPreviewContainer').addClass('d-none');
                                googleSheetsPreviewData = null;

                                // ปิด modal
                                $('#googleSheetsImportModal').modal('hide');

                                // Refresh หน้า
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                                
                                return; // ออกจาก error handler
                            }
                        }
                    } catch (parseError) {
                        // ไม่สามารถ parse ได้
                    }
                }
                
                let errorMessage = 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้';
                let errorType = 'error';
                
                try {
                    // ลองแปลง response เป็น JSON
                    let response = xhr.responseJSON;
                    
                    if (!response && xhr.responseText) {
                        try {
                            response = JSON.parse(xhr.responseText);
                        } catch (parseError) {
                            // ไม่สามารถ parse JSON ได้
                        }
                    }
                    
                    if (response && response.error) {
                        errorMessage = response.error;
                    } else if (response && response.message) {
                        errorMessage = response.message;
                    } else if (xhr.status === 0) {
                        errorMessage = 'การเชื่อมต่อถูกขัดจังหวะ อาจเนื่องจากข้อมูลใช้เวลาในการประมวลผลนาน\nกรุณาตรวจสอบผลลัพธ์ในฐานข้อมูล';
                        errorType = 'warning';
                    } else if (xhr.status === 419) {
                        errorMessage = 'CSRF Token หมดอายุ กรุณารีเฟรชหน้าและลองใหม่';
                    } else if (xhr.status === 422) {
                        errorMessage = 'ข้อมูลที่ส่งไม่ถูกต้อง กรุณาตรวจสอบข้อมูลและลองใหม่อีกครั้ง';
                    } else if (xhr.status === 500) {
                        errorMessage = 'เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์';
                    } else if (xhr.status === 504 || xhr.status === 408) {
                        errorMessage = 'เซิร์ฟเวอร์ใช้เวลาในการประมวลผลนานเกินไป\nข้อมูลอาจถูกบันทึกเรียบร้อยแล้ว กรุณาตรวจสอบผลลัพธ์';
                        errorType = 'warning';
                    } else if (xhr.status === 413) {
                        errorMessage = 'ข้อมูลมีขนาดใหญ่เกินไป กรุณาลดจำนวนข้อมูลและลองใหม่';
                    } else if (textStatus === 'timeout') {
                        errorMessage = 'หมดเวลาในการประมวลผล ข้อมูลอาจถูกบันทึกเรียบร้อยแล้ว\nกรุณาตรวจสอบผลลัพธ์ในระบบ';
                        errorType = 'warning';
                    } else if (textStatus === 'parsererror') {
                        errorMessage = 'เกิดข้อผิดพลาดในการประมวลผลข้อมูลตอบกลับ\nข้อมูลอาจถูกบันทึกเรียบร้อยแล้ว กรุณาตรวจสอบผลลัพธ์';
                        errorType = 'warning';
                    } else if (xhr.statusText) {
                        errorMessage = `เกิดข้อผิดพลาด: ${xhr.status} - ${xhr.statusText}`;
                    }
                    
                } catch (error) {
                    // ไม่สามารถประมวลผล response ได้
                    errorMessage = `เกิดข้อผิดพลาดไม่ทราบสาเหตุ (Status: ${xhr.status})`;
                }
                
                showToast(errorType, errorType === 'warning' ? 'แจ้งเตือน' : 'เกิดข้อผิดพลาด', errorMessage);
                
                // ถ้าเป็น timeout หรือ connection error ให้แสดงข้อความแนะนำ
                if (xhr.status === 0 || xhr.status === 504 || xhr.status === 408 || textStatus === 'timeout' || textStatus === 'parsererror') {
                    setTimeout(() => {
                        if (confirm('ต้องการรีเฟรชหน้าเพื่อดูผลลัพธ์หรือไม่?\n\nข้อมูลอาจถูกบันทึกสำเร็จแล้วในเบื้องหลัง')) {
                            location.reload();
                        }
                    }, 3000);
                }
            },
            complete: function (xhr, textStatus) {
                $('#importGoogleSheetsBtn').prop('disabled', false);
                $('#googleSheetsImportLoading').addClass('d-none');
            }
        });
    });

    // Select/Deselect All Functions
    $('#selectAllGoogleSheetsValid').click(function () {
        $('#googleSheetsValidTable tbody input[type="checkbox"]').prop('checked', true);
        updateGoogleSheetsImportButton();
    });

    $('#deselectAllGoogleSheetsValid').click(function () {
        $('#googleSheetsValidTable tbody input[type="checkbox"]').prop('checked', false);
        updateGoogleSheetsImportButton();
    });

    $('#checkAllGoogleSheetsValid').change(function () {
        $('#googleSheetsValidTable tbody input[type="checkbox"]').prop('checked', this.checked);
        updateGoogleSheetsImportButton();
    });

    // Update Import Button State
    function updateGoogleSheetsImportButton() {
        const selectedCount = $('#googleSheetsValidTable tbody input[type="checkbox"]:checked').length;
        $('#importGoogleSheetsBtn').prop('disabled', selectedCount === 0);
    }

    // Populate Preview Tables
    function populateGoogleSheetsPreviewTables(data) {
        // Valid Data Table
        const validTableBody = $('#googleSheetsValidTable tbody');
        validTableBody.empty();

        data.valid_data.forEach(function (item) {
            const row = $(`
                <tr>
                    <td><input type="checkbox" data-row="${item.row_number}" onchange="updateGoogleSheetsImportButton()"></td>
                    <td>${item.row_number}</td>
                    <td>${item.data.first_name || ''}</td>
                    <td>${item.data.last_name || ''}</td>
                    <td>${item.data.email || ''}</td>
                    <td><span class="badge bg-primary">${item.data.role || ''}</span></td>
                    <td>${item.data.student_id || ''}</td>
                </tr>
            `);
            validTableBody.append(row);
        });

        // Duplicate Data Table
        const duplicateTableBody = $('#googleSheetsDuplicateTable tbody');
        duplicateTableBody.empty();

        data.duplicate_data.forEach(function (item) {
            const row = $(`
                <tr>
                    <td>${item.row_number}</td>
                    <td>${item.data.first_name || ''}</td>
                    <td>${item.data.last_name || ''}</td>
                    <td>${item.data.email || ''}</td>
                    <td><span class="badge bg-primary">${item.data.role || ''}</span></td>
                    <td>
                        ${item.duplicate_fields.map(field =>
                            `<span class="badge bg-warning">${field}</span>`
                        ).join(' ')}
                    </td>
                </tr>
            `);
            duplicateTableBody.append(row);
        });

        // Error Data Table
        const errorTableBody = $('#googleSheetsErrorTable tbody');
        errorTableBody.empty();

        data.error_data.forEach(function (item) {
            const row = $(`
                <tr>
                    <td>${item.row_number}</td>
                    <td>${item.data.first_name || ''}</td>
                    <td>${item.data.last_name || ''}</td>
                    <td>${item.data.email || ''}</td>
                    <td><span class="badge bg-primary">${item.data.role || ''}</span></td>
                    <td>
                        ${item.errors.map(error =>
                            `<span class="badge bg-danger">${error}</span>`
                        ).join('<br>')}
                    </td>
                </tr>
            `);
            errorTableBody.append(row);
        });
    }

    // Update Summary Cards
    function updateGoogleSheetsSummaryCards(data) {
        $('#googleSheetsValidCount').text(data.valid_data.length);
        $('#googleSheetsDuplicateCount').text(data.duplicate_data.length);
        $('#googleSheetsErrorCount').text(data.error_data.length);
        $('#googleSheetsTotalCount').text(data.valid_data.length + data.duplicate_data.length + data.error_data.length);
    }

    // Get Selected Valid Data
    function getSelectedGoogleSheetsValidData() {
        const selectedData = [];

        $('#googleSheetsValidTable tbody input[type="checkbox"]:checked').each(function () {
            const rowNumber = $(this).data('row');
            const item = googleSheetsPreviewData.valid_data.find(item => item.row_number === rowNumber);
            if (item) {
                selectedData.push(item);
            }
        });

        return selectedData;
    }

    // Make functions global
    window.updateGoogleSheetsImportButton = updateGoogleSheetsImportButton;

    // Reset modal when closed
    $('#googleSheetsImportModal').on('hidden.bs.modal', function () {
        $('#googleSheetsPreviewContainer').addClass('d-none');
        googleSheetsPreviewData = null;
        $('#previewGoogleSheetsBtn').prop('disabled', false);
        $('#importGoogleSheetsBtn').prop('disabled', true);
    });
});
