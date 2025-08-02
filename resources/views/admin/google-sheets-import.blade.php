<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>นำเข้าข้อมูลจาก Google Sheets - ระบบพฤติกรรมนักเรียน</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .preview-table {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .table-container {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        
        .status-badge {
            font-size: 0.875rem;
        }
        
        .error-row {
            background-color: #f8d7da;
        }
        
        .duplicate-row {
            background-color: #fff3cd;
        }
        
        .valid-row {
            background-color: #d1edff;
        }
        
        .loading-spinner {
            display: none;
        }
        
        .preview-container {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="fas fa-file-import text-primary"></i> นำเข้าข้อมูลจาก Google Sheets</h2>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> กลับไปยังแดชบอร์ด
                    </a>
                </div>
            </div>
        </div>

        <!-- Google Sheets URL Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fab fa-google text-success"></i> ข้อมูล Google Sheets</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>ข้อมูลจะถูกดึงจาก:</strong> 
                            <a href="https://docs.google.com/spreadsheets/d/1L3O0f5HdX_7cPw2jrQT4IaPsjw_jFD3O0aeH9ZQ499c/edit" target="_blank" class="alert-link">
                                Google Sheets ระบบพฤติกรรมนักเรียน
                            </a>
                        </div>
                        <p class="mb-0">
                            <strong>รูปแบบข้อมูลที่รองรับ:</strong>
                            prefix, first_name, last_name, email, phone, role, student_id, classroom, gender, date_of_birth, hire_date, position, relationship, occupation, address
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <button id="previewBtn" class="btn btn-primary btn-lg">
                            <i class="fas fa-eye"></i> ดูตัวอย่างข้อมูล
                        </button>
                        <div class="loading-spinner d-inline-block ms-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">กำลังโหลด...</span>
                            </div>
                            <span class="ms-2">กำลังดึงข้อมูลจาก Google Sheets...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Container -->
        <div class="preview-container">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-header"><i class="fas fa-check-circle"></i> ข้อมูลถูกต้อง</div>
                        <div class="card-body">
                            <h4 class="card-title" id="validCount">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-header"><i class="fas fa-exclamation-triangle"></i> ข้อมูลซ้ำ</div>
                        <div class="card-body">
                            <h4 class="card-title" id="duplicateCount">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-header"><i class="fas fa-times-circle"></i> ข้อมูลผิดพลาด</div>
                        <div class="card-body">
                            <h4 class="card-title" id="errorCount">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-header"><i class="fas fa-list"></i> รวมทั้งหมด</div>
                        <div class="card-body">
                            <h4 class="card-title" id="totalCount">0</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Tabs -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="dataTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="valid-tab" data-bs-toggle="tab" data-bs-target="#valid" type="button" role="tab">
                                        <i class="fas fa-check-circle text-success"></i> ข้อมูลถูกต้อง
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="duplicate-tab" data-bs-toggle="tab" data-bs-target="#duplicate" type="button" role="tab">
                                        <i class="fas fa-exclamation-triangle text-warning"></i> ข้อมูลซ้ำ
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="error-tab" data-bs-toggle="tab" data-bs-target="#error" type="button" role="tab">
                                        <i class="fas fa-times-circle text-danger"></i> ข้อมูลผิดพลาด
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="dataTabsContent">
                                <!-- Valid Data Tab -->
                                <div class="tab-pane fade show active" id="valid" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5>ข้อมูลที่พร้อมนำเข้า</h5>
                                        <div>
                                            <button id="selectAllValid" class="btn btn-sm btn-outline-primary">เลือกทั้งหมด</button>
                                            <button id="deselectAllValid" class="btn btn-sm btn-outline-secondary">ยกเลิกทั้งหมด</button>
                                        </div>
                                    </div>
                                    <div class="table-container">
                                        <div class="preview-table">
                                            <table class="table table-striped table-hover" id="validTable">
                                                <thead class="table-success sticky-top">
                                                    <tr>
                                                        <th><input type="checkbox" id="checkAllValid"></th>
                                                        <th>แถว</th>
                                                        <th>คำนำหน้า</th>
                                                        <th>ชื่อ</th>
                                                        <th>นามสกุล</th>
                                                        <th>อีเมล</th>
                                                        <th>โทรศัพท์</th>
                                                        <th>บทบาท</th>
                                                        <th>รหัสนักเรียน</th>
                                                        <th>ห้องเรียน</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Duplicate Data Tab -->
                                <div class="tab-pane fade" id="duplicate" role="tabpanel">
                                    <h5>ข้อมูลที่ซ้ำกับฐานข้อมูล</h5>
                                    <div class="table-container">
                                        <div class="preview-table">
                                            <table class="table table-striped table-hover" id="duplicateTable">
                                                <thead class="table-warning sticky-top">
                                                    <tr>
                                                        <th>แถว</th>
                                                        <th>ชื่อ</th>
                                                        <th>นามสกุล</th>
                                                        <th>อีเมล</th>
                                                        <th>บทบาท</th>
                                                        <th>รหัสนักเรียน</th>
                                                        <th>ฟิลด์ที่ซ้ำ</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Error Data Tab -->
                                <div class="tab-pane fade" id="error" role="tabpanel">
                                    <h5>ข้อมูลที่มีข้อผิดพลาด</h5>
                                    <div class="table-container">
                                        <div class="preview-table">
                                            <table class="table table-striped table-hover" id="errorTable">
                                                <thead class="table-danger sticky-top">
                                                    <tr>
                                                        <th>แถว</th>
                                                        <th>ชื่อ</th>
                                                        <th>นามสกุล</th>
                                                        <th>อีเมล</th>
                                                        <th>บทบาท</th>
                                                        <th>ข้อผิดพลาด</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Import Button -->
                            <div class="mt-4 text-center">
                                <button id="importBtn" class="btn btn-success btn-lg" disabled>
                                    <i class="fas fa-download"></i> นำเข้าข้อมูลที่เลือก
                                </button>
                                <div id="importLoading" class="loading-spinner d-inline-block ms-3">
                                    <div class="spinner-border text-success" role="status">
                                        <span class="visually-hidden">กำลังนำเข้าข้อมูล...</span>
                                    </div>
                                    <span class="ms-2">กำลังนำเข้าข้อมูลลงฐานข้อมูล...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let previewData = null;

            // CSRF Token Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Preview Button Click
            $('#previewBtn').click(function() {
                $(this).prop('disabled', true);
                $('.loading-spinner').show();
                
                $.ajax({
                    url: '{{ route("admin.google-sheets.preview") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            previewData = response.data;
                            populatePreviewTables(response.data);
                            updateSummaryCards(response.data);
                            $('.preview-container').show();
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + response.error);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        alert('เกิดข้อผิดพลาด: ' + (response ? response.error : 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้'));
                    },
                    complete: function() {
                        $('#previewBtn').prop('disabled', false);
                        $('.loading-spinner').hide();
                    }
                });
            });

            // Import Button Click
            $('#importBtn').click(function() {
                const selectedData = getSelectedValidData();
                
                if (selectedData.length === 0) {
                    alert('กรุณาเลือกข้อมูลที่ต้องการนำเข้า');
                    return;
                }

                if (!confirm('คุณต้องการนำเข้าข้อมูล ' + selectedData.length + ' รายการหรือไม่?')) {
                    return;
                }

                $(this).prop('disabled', true);
                $('#importLoading').show();

                $.ajax({
                    url: '{{ route("admin.google-sheets.import") }}',
                    method: 'POST',
                    data: {
                        selected_data: selectedData
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('นำเข้าข้อมูลสำเร็จ!\n' +
                                  'สำเร็จ: ' + response.results.success_count + ' รายการ\n' +
                                  'ผิดพลาด: ' + response.results.error_count + ' รายการ');
                            
                            if (response.results.errors.length > 0) {
                                // Log errors silently for admin review
                            }
                            
                            // รีเซ็ตฟอร์ม
                            $('.preview-container').hide();
                            previewData = null;
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + response.error);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        alert('เกิดข้อผิดพลาด: ' + (response ? response.error : 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้'));
                    },
                    complete: function() {
                        $('#importBtn').prop('disabled', false);
                        $('#importLoading').hide();
                    }
                });
            });

            // Select/Deselect All Functions
            $('#selectAllValid').click(function() {
                $('#validTable tbody input[type="checkbox"]').prop('checked', true);
                updateImportButton();
            });

            $('#deselectAllValid').click(function() {
                $('#validTable tbody input[type="checkbox"]').prop('checked', false);
                updateImportButton();
            });

            $('#checkAllValid').change(function() {
                $('#validTable tbody input[type="checkbox"]').prop('checked', this.checked);
                updateImportButton();
            });

            // Update Import Button State
            function updateImportButton() {
                const selectedCount = $('#validTable tbody input[type="checkbox"]:checked').length;
                $('#importBtn').prop('disabled', selectedCount === 0);
            }

            // Populate Preview Tables
            function populatePreviewTables(data) {
                // Valid Data Table
                const validTableBody = $('#validTable tbody');
                validTableBody.empty();
                
                data.valid_data.forEach(function(item) {
                    const row = $(`
                        <tr>
                            <td><input type="checkbox" data-row="${item.row_number}" onchange="updateImportButton()"></td>
                            <td>${item.row_number}</td>
                            <td>${item.data.prefix || ''}</td>
                            <td>${item.data.first_name || ''}</td>
                            <td>${item.data.last_name || ''}</td>
                            <td>${item.data.email || ''}</td>
                            <td>${item.data.phone || ''}</td>
                            <td><span class="badge bg-primary">${item.data.role || ''}</span></td>
                            <td>${item.data.student_id || ''}</td>
                            <td>${item.data.classroom || ''}</td>
                        </tr>
                    `);
                    validTableBody.append(row);
                });

                // Duplicate Data Table
                const duplicateTableBody = $('#duplicateTable tbody');
                duplicateTableBody.empty();
                
                data.duplicate_data.forEach(function(item) {
                    const row = $(`
                        <tr>
                            <td>${item.row_number}</td>
                            <td>${item.data.first_name || ''}</td>
                            <td>${item.data.last_name || ''}</td>
                            <td>${item.data.email || ''}</td>
                            <td><span class="badge bg-primary">${item.data.role || ''}</span></td>
                            <td>${item.data.student_id || ''}</td>
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
                const errorTableBody = $('#errorTable tbody');
                errorTableBody.empty();
                
                data.error_data.forEach(function(item) {
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
            function updateSummaryCards(data) {
                $('#validCount').text(data.valid_data.length);
                $('#duplicateCount').text(data.duplicate_data.length);
                $('#errorCount').text(data.error_data.length);
                $('#totalCount').text(data.valid_data.length + data.duplicate_data.length + data.error_data.length);
            }

            // Get Selected Valid Data
            function getSelectedValidData() {
                const selectedData = [];
                
                $('#validTable tbody input[type="checkbox"]:checked').each(function() {
                    const rowNumber = $(this).data('row');
                    const item = previewData.valid_data.find(item => item.row_number === rowNumber);
                    if (item) {
                        selectedData.push(item);
                    }
                });
                
                return selectedData;
            }

            // Make updateImportButton global
            window.updateImportButton = updateImportButton;
        });
    </script>
</body>
</html>
