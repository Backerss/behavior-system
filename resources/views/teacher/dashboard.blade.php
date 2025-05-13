<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการพฤติกรรม | แดชบอร์ดครู</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Font: Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.min.css">
    <!-- App CSS -->
    <link href="/css/app.css" rel="stylesheet">
    <!-- Dashboard CSS -->
    <link href="/css/teacher-dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (Desktop) -->
        <div class="sidebar d-none d-lg-flex">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียน" class="logo">
                    <h5 class="mb-0 ms-2">ระบบจัดการพฤติกรรม</h5>
                </div>
            </div>
            <div class="sidebar-menu">
                <a href="#overview" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>ภาพรวม</span>
                </a>
                <a href="#students" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>รายชื่อนักเรียน</span>
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#newViolationModal" class="menu-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>บันทึกพฤติกรรม</span>
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#importExportModal" class="menu-item">
                    <i class="fas fa-file-import"></i>
                    <span>นำเข้า/ส่งออก</span>
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#profileModal" class="menu-item">
                    <i class="fas fa-user-circle"></i>
                    <span>โปรไฟล์</span>
                </a>
                <a href="/logout" class="menu-item mt-auto">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>ออกจากระบบ</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Mobile Header -->
            <div class="mobile-header d-flex d-lg-none">
                <div class="d-flex justify-content-between align-items-center w-100 px-3">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียน" class="logo">
                        <h5 class="mb-0 ms-2">ระบบจัดการพฤติกรรม</h5>
                    </div>
                    <div class="dropdown">
                        <img src="https://ui-avatars.com/api/?name=ครูใจดี&background=1020AD&color=fff" class="rounded-circle" width="40" height="40" data-bs-toggle="dropdown">
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">โปรไฟล์</a>
                            <a class="dropdown-item" href="/logout">ออกจากระบบ</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Welcome Section -->
                    <div class="welcome-section d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="fw-bold">สวัสดี, คุณครูใจดี</h1>
                            <p class="text-muted">วันนี้คือวันที่ <span class="current-date">12 พฤษภาคม 2568</span></p>
                        </div>
                        <div class="d-none d-md-flex">
                            <button class="btn btn-primary-app me-2" data-bs-toggle="modal" data-bs-target="#newViolationModal">
                                <i class="fas fa-plus-circle me-2"></i>บันทึกพฤติกรรมใหม่
                            </button>
                        </div>
                    </div>
                    
                    <!-- Stats Overview -->
                    <div class="row mb-4" id="overview">
                        <div class="col-12">
                            <h5 class="section-title">ภาพรวมประจำเดือน พฤษภาคม 2568</h5>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary-app">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="stat-title">พฤติกรรมที่บันทึกทั้งหมด</h6>
                                            <h4 class="stat-value">256</h4>
                                            <span class="stat-change increase">+12% จากเดือนที่แล้ว</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-secondary-app">
                                            <i class="fas fa-user-slash"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="stat-title">นักเรียนที่ถูกบันทึกพฤติกรรม</h6>
                                            <h4 class="stat-value">128</h4>
                                            <span class="stat-change decrease">-5% จากเดือนที่แล้ว</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-accent-app">
                                            <i class="fas fa-award"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="stat-title">คะแนนความประพฤติเฉลี่ย</h6>
                                            <h4 class="stat-value">85.2</h4>
                                            <span class="stat-change increase">+2.3 จากเดือนที่แล้ว</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="stat-title">ต้องติดตามพฤติกรรม</h6>
                                            <h4 class="stat-value">42</h4>
                                            <span class="stat-change no-change">คงที่จากเดือนที่แล้ว</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Violation Trends Chart -->
                    <div class="row mb-4">
                        <div class="col-12 col-lg-8 mb-3">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">แนวโน้มการบันทึกพฤติกรรมประจำเดือน</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="violationTrend" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 mb-3">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">ประเภทการกระทำผิด</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="violationTypes" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Violations -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">พฤติกรรมที่บันทึกล่าสุด</h5>
                                    <div class="d-flex">
                                        <div class="input-group me-2">
                                            <input type="text" class="form-control form-control-sm" placeholder="ค้นหา...">
                                            <button class="btn btn-sm btn-primary-app"><i class="fas fa-search"></i></button>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-filter"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>นักเรียน</th>
                                                    <th>ชั้นเรียน</th>
                                                    <th>ประเภท</th>
                                                    <th>คะแนนที่หัก</th>
                                                    <th>วันที่บันทึก</th>
                                                    <th>บันทึกโดย</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=สมชาย&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>สมชาย รักเรียน</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/1</td>
                                                    <td><span class="badge bg-danger">ผิดระเบียบการแต่งกาย</span></td>
                                                    <td>5</td>
                                                    <td>12/05/2568</td>
                                                    <td>ครูใจดี</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#violationDetailModal"><i class="fas fa-eye"></i></button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=มานี&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>มานี มีทรัพย์</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/2</td>
                                                    <td><span class="badge bg-warning text-dark">มาสาย</span></td>
                                                    <td>3</td>
                                                    <td>12/05/2568</td>
                                                    <td>ครูใจดี</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#violationDetailModal"><i class="fas fa-eye"></i></button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=สมศรี&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>สมศรี มีมานะ</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/3</td>
                                                    <td><span class="badge bg-info">ลืมอุปกรณ์</span></td>
                                                    <td>2</td>
                                                    <td>11/05/2568</td>
                                                    <td>ครูใจดี</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#violationDetailModal"><i class="fas fa-eye"></i></button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=วิชัย&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>วิชัย ไม่ย่อท้อ</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/1</td>
                                                    <td><span class="badge bg-danger">ใช้โทรศัพท์ในเวลาเรียน</span></td>
                                                    <td>10</td>
                                                    <td>10/05/2568</td>
                                                    <td>ครูใจดี</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#violationDetailModal"><i class="fas fa-eye"></i></button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=อรุณ&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>อรุณ สดใส</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/2</td>
                                                    <td><span class="badge bg-warning text-dark">ไม่ส่งการบ้าน</span></td>
                                                    <td>5</td>
                                                    <td>09/05/2568</td>
                                                    <td>ครูใจดี</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#violationDetailModal"><i class="fas fa-eye"></i></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white">
                                    <nav>
                                        <ul class="pagination pagination-sm justify-content-end mb-0">
                                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Student List -->
                    <div class="row mb-4" id="students">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">รายชื่อนักเรียน</h5>
                                    <div class="d-flex">
                                        <div class="input-group me-2">
                                            <input type="text" class="form-control form-control-sm" placeholder="ค้นหานักเรียน...">
                                            <button class="btn btn-sm btn-primary-app"><i class="fas fa-search"></i></button>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-filter"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>รหัสนักเรียน</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>ชั้นเรียน</th>
                                                    <th>คะแนนคงเหลือ</th>
                                                    <th>สถิติการกระทำผิด</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>1001</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=สมชาย&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>สมชาย รักเรียน</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/1</td>
                                                    <td>
                                                        <div style="margin-bottom: 5px; margin-top: 10px;">
                                                            <div class="progress" style="height: 8px; width: 100px; position: relative; margin-top: 10px;">
                                                                <div class="progress-bar bg-success" role="progressbar" style="width: 90%"></div>
                                                                <div style="position: absolute; left: 90%; top: -10px; transform: translateX(-50%); 
                                                                            background-color: white; width: 24px; height: 24px; 
                                                                            border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.3); 
                                                                            display: flex; align-items: center; justify-content: center; 
                                                                            border: 2px solid white; z-index: 10;">
                                                                    <img src="{{ asset('images/smile.png') }}" 
                                                                         style="height: 16px; width: 16px;" 
                                                                         alt="👍">
                                                                </div>
                                                            </div>
                                                            </div>
                                                            <span class="small">90/100</span>
                                                        </div>
                                                    </td>
                                                    <td>5 ครั้ง</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary-app" data-bs-toggle="modal" data-bs-target="#studentDetailModal"><i class="fas fa-user me-1"></i> ดูข้อมูล</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>1002</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=มานี&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>มานี มีทรัพย์</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/2</td>
                                                    <td>
                                                        <div class="progress" style="height: 8px; width: 100px;">
                                                            <div class="progress-bar bg-success" role="progressbar" style="width: 95%"></div>
                                                        </div>
                                                        <span class="small">95/100</span>
                                                    </td>
                                                    <td>2 ครั้ง</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary-app" data-bs-toggle="modal" data-bs-target="#studentDetailModal"><i class="fas fa-user me-1"></i> ดูข้อมูล</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>1003</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=สมศรี&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>สมศรี มีมานะ</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/3</td>
                                                    <td>
                                                        <div class="progress" style="height: 8px; width: 100px;">
                                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 75%"></div>
                                                        </div>
                                                        <span class="small">75/100</span>
                                                    </td>
                                                    <td>8 ครั้ง</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary-app" data-bs-toggle="modal" data-bs-target="#studentDetailModal"><i class="fas fa-user me-1"></i> ดูข้อมูล</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>1004</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=วิชัย&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>วิชัย ไม่ย่อท้อ</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/1</td>
                                                    <td>
                                                        <div class="progress" style="height: 8px; width: 100px;">
                                                            <div class="progress-bar bg-danger" role="progressbar" style="width: 60%"></div>
                                                        </div>
                                                        <span class="small">60/100</span>
                                                    </td>
                                                    <td>12 ครั้ง</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary-app" data-bs-toggle="modal" data-bs-target="#studentDetailModal"><i class="fas fa-user me-1"></i> ดูข้อมูล</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>1005</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="https://ui-avatars.com/api/?name=อรุณ&background=95A4D8&color=fff" class="rounded-circle me-2" width="32" height="32">
                                                            <span>อรุณ สดใส</span>
                                                        </div>
                                                    </td>
                                                    <td>ม.5/2</td>
                                                    <td>
                                                        <div class="progress" style="height: 8px; width: 100px;">
                                                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                                                        </div>
                                                        <span class="small">85/100</span>
                                                    </td>
                                                    <td>4 ครั้ง</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary-app" data-bs-toggle="modal" data-bs-target="#studentDetailModal"><i class="fas fa-user me-1"></i> ดูข้อมูล</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white">
                                    <nav>
                                        <ul class="pagination pagination-sm justify-content-end mb-0">
                                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Bottom Nav -->
            <div class="bottom-navbar d-lg-none">
                <div class="row g-0">
                    <div class="col">
                        <a href="#overview" class="nav-link text-center text-primary-app">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>ภาพรวม</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="#students" class="nav-link text-center">
                            <i class="fas fa-users"></i>
                            <span>นักเรียน</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="#" class="nav-link text-center" data-bs-toggle="modal" data-bs-target="#newViolationModal">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>บันทึก</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="#" class="nav-link text-center" data-bs-toggle="modal" data-bs-target="#importExportModal">
                            <i class="fas fa-file-import"></i>
                            <span>นำเข้า</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="#" class="nav-link text-center" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i class="fas fa-user-circle"></i>
                            <span>โปรไฟล์</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">โปรไฟล์ของฉัน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="https://ui-avatars.com/api/?name=ครูใจดี&background=1020AD&color=fff" class="rounded-circle" width="100" height="100">
                            <button class="btn btn-sm btn-primary-app position-absolute bottom-0 end-0 rounded-circle">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        <h4 class="mt-3 mb-1">ครูใจดี มีเมตตา</h4>
                        <p class="text-muted">ครูประจำชั้น ม.5/1</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">อีเมล</label>
                        <input type="email" class="form-control" value="teacher@school.ac.th" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" class="form-control" value="ครูใจดี มีเมตตา">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ตำแหน่ง</label>
                        <input type="text" class="form-control" value="ครูประจำชั้น ม.5/1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" class="form-control" value="088-888-8888">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" class="form-control" placeholder="ใส่รหัสผ่านใหม่">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" class="form-control" placeholder="ยืนยันรหัสผ่านใหม่">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary-app">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Violation Modal -->
    <div class="modal fade" id="newViolationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">บันทึกพฤติกรรมนักเรียน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">ค้นหานักเรียน</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="พิมพ์ชื่อหรือรหัสนักเรียน...">
                                <button class="btn btn-primary-app" type="button"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        
                        <div class="selected-student mb-4 p-3 border rounded d-none">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=สมชาย&background=95A4D8&color=fff" class="rounded-circle me-3" width="50" height="50">
                                <div>
                                    <h5 class="mb-1">สมชาย รักเรียน</h5>
                                    <p class="mb-0 text-muted">รหัสนักเรียน: 1001 | ชั้น ม.5/1</p>
                                </div>
                                <button type="button" class="btn-close ms-auto"></button>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">ประเภทการกระทำผิด</label>
                                <select class="form-select">
                                    <option selected disabled>เลือกประเภทการกระทำผิด</option>
                                    <option>ผิดระเบียบการแต่งกาย</option>
                                    <option>มาสาย</option>
                                    <option>ขาดเรียน</option>
                                    <option>ใช้โทรศัพท์ในเวลาเรียน</option>
                                    <option>ไม่ส่งการบ้าน</option>
                                    <option>ลืมอุปกรณ์</option>
                                    <option>ทะเลาะวิวาท</option>
                                    <option>ทำลายทรัพย์สิน</option>
                                    <option>อื่น ๆ</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">คะแนนที่หัก</label>
                                <input type="number" class="form-control" min="0" max="100" value="5">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">วันที่เกิดเหตุการณ์</label>
                                <input type="date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เวลาที่เกิดเหตุการณ์</label>
                                <input type="time" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">รายละเอียดเพิ่มเติม</label>
                            <textarea class="form-control" rows="3" placeholder="รายละเอียดของการกระทำผิด..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">แนบรูปภาพ (ถ้ามี)</label>
                            <input type="file" class="form-control" accept="image/*">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary-app">บันทึกพฤติกรรม</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Import/Export Modal -->
    <div class="modal fade" id="importExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">นำเข้าและส่งออกข้อมูล</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title mb-3">นำเข้าข้อมูลนักเรียน</h5>
                            <p class="card-text text-muted">อัพโหลดไฟล์ Excel ที่มีรายชื่อนักเรียนตามรูปแบบที่กำหนด</p>
                            <div class="mb-3">
                                <input type="file" class="form-control" accept=".xlsx, .xls, .csv">
                            </div>
                            <a href="#" class="btn btn-sm btn-link">ดาวน์โหลดเทมเพลตไฟล์นำเข้า</a>
                            <button class="btn btn-primary-app w-100 mt-2">อัพโหลดไฟล์</button>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">ส่งออกรายงาน</h5>
                            <p class="card-text text-muted">เลือกรูปแบบรายงานที่ต้องการส่งออก</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary d-flex justify-content-between align-items-center">
                                    <span>รายงานพฤติกรรมประจำเดือน</span>
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button class="btn btn-outline-primary d-flex justify-content-between align-items-center">
                                    <span>รายงานสรุปนักเรียนที่มีความเสี่ยง</span>
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button class="btn btn-outline-primary d-flex justify-content-between align-items-center">
                                    <span>ส่งออกข้อมูลพฤติกรรมทั้งหมด</span>
                                    <i class="fas fa-file-excel"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Student Detail Modal -->
    <div class="modal fade" id="studentDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">ข้อมูลนักเรียน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-center">
                                <img src="https://ui-avatars.com/api/?name=สมชาย&background=95A4D8&color=fff" class="rounded-circle" width="100" height="100">
                                <h5 class="mt-3 mb-1">สมชาย รักเรียน</h5>
                                <span class="badge bg-primary-app">ม.5/1</span>
                                <hr>
                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-primary-app">บันทึกพฤติกรรม</button>
                                    <button class="btn btn-outline-secondary">พิมพ์รายงาน</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">รหัสนักเรียน</label>
                                    <p>1001</p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">ชั้นเรียน</label>
                                    <p>ม.5/1</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">เลขประจำตัวประชาชน</label>
                                    <p>1-2345-67890-12-3</p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">วันเกิด</label>
                                    <p>15 มกราคม 2553</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">ชื่อผู้ปกครอง</label>
                                    <p>นายสมบัติ รักเรียน</p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">เบอร์โทรผู้ปกครอง</label>
                                    <p>099-999-9999</p>
                                </div>
                            </div>
                            
                            <h6 class="mt-4">สถิติคะแนนความประพฤติ</h6>
                            <div style="position: relative; margin-bottom: 25px; margin-top: 30px;">
                                <div style="position: absolute; left: calc(90% - 18px); top: -10px; z-index: 1000; 
                                            background-color: white; width: 40px; height: 40px; 
                                            border-radius: 50%; box-shadow: 0 3px 10px rgba(0,0,0,0.4); 
                                            display: flex; align-items: center; justify-content: center; 
                                            border: 3px solid white;">
                                    <img src="{{ asset('images/smile.png') }}" 
                                         style="height: 30px; width: 30px;" 
                                         alt="👍">
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 90%">90/100</div>
                                </div>
                            </div>
                            
                            <h6 class="mt-4">ประวัติการกระทำผิดล่าสุด</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <thead class="table-light">
                                        <tr>
                                            <th>วันที่</th>
                                            <th>ประเภท</th>
                                            <th>คะแนนที่หัก</th>
                                            <th>บันทึกโดย</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>12/05/2568</td>
                                            <td><span class="badge bg-danger">ผิดระเบียบการแต่งกาย</span></td>
                                            <td>5</td>
                                            <td>ครูใจดี</td>
                                        </tr>
                                        <tr>
                                            <td>05/05/2568</td>
                                            <td><span class="badge bg-warning text-dark">มาสาย</span></td>
                                            <td>3</td>
                                            <td>ครูใจดี</td>
                                        </tr>
                                        <tr>
                                            <td>25/04/2568</td>
                                            <td><span class="badge bg-info">ลืมอุปกรณ์</span></td>
                                            <td>2</td>
                                            <td>ครูมานะ</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Violation Detail Modal -->
    <div class="modal fade" id="violationDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">รายละเอียดการกระทำผิด</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://ui-avatars.com/api/?name=สมชาย&background=95A4D8&color=fff" class="rounded-circle me-3" width="50" height="50">
                        <div>
                            <h5 class="mb-1">สมชาย รักเรียน</h5>
                            <p class="mb-0 text-muted">รหัสนักเรียน: 1001 | ชั้น ม.5/1</p>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted d-block">ประเภทการกระทำผิด</label>
                                <span class="badge bg-danger">ผิดระเบียบการแต่งกาย</span>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted d-block">วันและเวลา</label>
                                <p class="mb-0">12 พฤษภาคม 2568, 08:30 น.</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted d-block">คะแนนที่หัก</label>
                                <p class="mb-0">5 คะแนน</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted d-block">บันทึกโดย</label>
                                <p class="mb-0">ครูใจดี มีเมตตา</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted d-block">รายละเอียด</label>
                                <p class="mb-0">นักเรียนมาโรงเรียนโดยไม่สวมเนคไทและเข็มขัด ครั้งที่ 2 ในรอบสัปดาห์นี้</p>
                            </div>
                            <div>
                                <label class="text-muted d-block">รูปภาพ</label>
                                <img src="https://via.placeholder.com/300x200?text=Example+Photo" class="img-fluid rounded" alt="รูปภาพหลักฐาน">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-danger me-auto">ลบบันทึก</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary-app">แก้ไข</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Dashboard JS -->
    <script src="/js/teacher-dashboard.js"></script>
</body>
</html>