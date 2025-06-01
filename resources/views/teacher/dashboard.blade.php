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
    <link href="/css/loading-effects.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                <a href="#" data-bs-toggle="modal" data-bs-target="#violationTypesModal" class="menu-item">
                    <i class="fas fa-list-ul"></i>
                    <span>จัดการประเภทพฤติกรรม</span>
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#classManagementModal" class="menu-item">
                    <i class="fas fa-school"></i>
                    <span>จัดการห้องเรียน</span>
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#importExportModal" class="menu-item">
                    <i class="fas fa-file-import"></i>
                    <span>นำเข้า/ส่งออก</span>
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#profileModal" class="menu-item">
                    <i class="fas fa-user-circle"></i>
                    <span>โปรไฟล์</span>
                </a>
                <a href="javascript:void(0);" onclick="document.getElementById('logout-form').submit();" class="menu-item mt-auto">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>ออกจากระบบ</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
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
                            <a class="dropdown-item" href="javascript:void(0);" onclick="document.getElementById('logout-form').submit();">ออกจากระบบ</a>
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
                            <h1 class="fw-bold">สวัสดี, {{ $user->name_prefix }}{{ $user->first_name }} {{ $user->last_name }}</h1>
                            <p class="text-muted">วันนี้คือวันที่ <span class="current-date">{{ date('d F Y') }}</span></p>
                        </div>
                        <div class="d-none d-md-flex">
                            <button class="btn btn-primary-app me-2" data-bs-toggle="modal" data-bs-target="#newViolationModal">
                                <i class="fas fa-plus me-2"></i> บันทึกพฤติกรรม
                            </button>
                        </div>
                    </div>
                    
                    <!-- Stats Overview -->
                    <div class="row mb-4" id="overview">
                        <div class="col-12">
                            <h5 class="section-title">ภาพรวมประจำเดือน {{ now()->locale('th')->translatedFormat('F Y') }}</h5>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary-app me-3">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div>
                                            <h6 class="stat-title">พฤติกรรมที่บันทึกเดือนนี้</h6>
                                            <h4 class="stat-value">{{ $monthlyStats['violation_count'] ?? 0 }}</h4>
                                            <p class="stat-change mb-0 {{ $monthlyStats['violation_trend'] > 0 ? 'increase' : ($monthlyStats['violation_trend'] < 0 ? 'decrease' : 'no-change') }}">
                                                <i class="fas fa-{{ $monthlyStats['violation_trend'] > 0 ? 'arrow-up' : ($monthlyStats['violation_trend'] < 0 ? 'arrow-down' : 'equals') }} me-1"></i>
                                                {{ abs($monthlyStats['violation_trend']) }}% จากเดือนก่อน
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning me-3">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <h6 class="stat-title">นักเรียนที่ถูกบันทึก</h6>
                                            <h4 class="stat-value">{{ $monthlyStats['students_count'] ?? 0 }}</h4>
                                            <p class="stat-change mb-0 {{ $monthlyStats['students_trend'] > 0 ? 'increase' : ($monthlyStats['students_trend'] < 0 ? 'decrease' : 'no-change') }}">
                                                <i class="fas fa-{{ $monthlyStats['students_trend'] > 0 ? 'arrow-up' : ($monthlyStats['students_trend'] < 0 ? 'arrow-down' : 'equals') }} me-1"></i>
                                                {{ abs($monthlyStats['students_trend']) }}% จากเดือนก่อน
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-danger me-3">
                                            <i class="fas fa-fire"></i>
                                        </div>
                                        <div>
                                            <h6 class="stat-title">พฤติกรรมรุนแรง</h6>
                                            <h4 class="stat-value">{{ $monthlyStats['severe_count'] ?? 0 }}</h4>
                                            <p class="stat-change mb-0 {{ $monthlyStats['severe_trend'] > 0 ? 'increase' : ($monthlyStats['severe_trend'] < 0 ? 'decrease' : 'no-change') }}">
                                                <i class="fas fa-{{ $monthlyStats['severe_trend'] > 0 ? 'arrow-up' : ($monthlyStats['severe_trend'] < 0 ? 'arrow-down' : 'equals') }} me-1"></i>
                                                {{ abs($monthlyStats['severe_trend']) }}% จากเดือนก่อน
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success me-3">
                                            <i class="fas fa-award"></i>
                                        </div>
                                        <div>
                                            <h6 class="stat-title">คะแนนเฉลี่ย</h6>
                                            <h4 class="stat-value">{{ number_format($monthlyStats['avg_score'] ?? 0, 1) }}</h4>
                                            <p class="stat-change mb-0 {{ $monthlyStats['score_trend'] > 0 ? 'increase' : ($monthlyStats['score_trend'] < 0 ? 'decrease' : 'no-change') }}">
                                                <i class="fas fa-{{ $monthlyStats['score_trend'] > 0 ? 'arrow-up' : ($monthlyStats['score_trend'] < 0 ? 'arrow-down' : 'equals') }} me-1"></i>
                                                {{ abs($monthlyStats['score_trend']) }} คะแนนจากเดือนก่อน
                                            </p>
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
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">การบันทึกพฤติกรรมล่าสุด</h5>
                                    <button class="btn btn-sm btn-outline-primary" onclick="loadRecentReports()">
                                        <i class="fas fa-sync-alt"></i> รีเฟรช
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>นักเรียน</th>
                                                    <th>ประเภทพฤติกรรม</th>
                                                    <th>คะแนนที่หัก</th>
                                                    <th>วันที่บันทึก</th>
                                                    <th>บันทึกโดย</th>
                                                    <th>การดำเนินการ</th>
                                                </tr>
                                            </thead>
                                            <tbody id="recentViolationsTable">
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                                                            <p>ไม่มีข้อมูลการบันทึกพฤติกรรม</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
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
                        <a href="#" class="nav-link text-center" data-bs-toggle="modal" data-bs-target="#violationTypesModal">
                            <i class="fas fa-list-ul"></i>
                            <span>ประเภท</span>
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">โปรไฟล์ของฉัน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('teacher.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success mb-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="row">
                            <!-- คอลัมน์ซ้าย: รูปและข้อมูลพื้นฐาน -->
                            <div class="col-md-4 mb-3">
                                <div class="text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        @if($user->users_profile_image)
                                            <img src="{{ asset('storage/'.$user->users_profile_image) }}" class="rounded-circle" width="100" height="100" id="profile-preview">
                                        @else
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->users_first_name) }}&background=1020AD&color=fff" class="rounded-circle" width="100" height="100" id="profile-preview">
                                        @endif
                                        <label for="profile_image" class="btn btn-sm btn-primary-app position-absolute bottom-0 end-0 rounded-circle" style="cursor: pointer;">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                        <input type="file" name="profile_image" id="profile_image" style="display: none;" accept="image/*">
                                    </div>
                                    <h5 class="mt-3 mb-1">{{ $user->users_name_prefix }}{{ $user->users_first_name }} {{ $user->users_last_name }}</h5>
                                    <p class="text-muted">
                                        @if($user->teacher && $user->teacher->teachers_position)
                                            {{ $user->teacher->teachers_position }}
                                        @else
                                            ครู
                                        @endif
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">อีเมล</label>
                                    <input type="email" class="form-control" name="users_email" value="{{ $user->users_email }}" disabled>
                                    <div class="form-text">อีเมลไม่สามารถแก้ไขได้</div>
                                </div>
                            </div>
                            
                            <!-- คอลัมน์ขวา: แท็บข้อมูลและการตั้งค่า -->
                            <div class="col-md-8">
                                <ul class="nav nav-tabs mb-3" id="profileTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-selected="true">ข้อมูลส่วนตัว</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="work-tab" data-bs-toggle="tab" data-bs-target="#work" type="button" role="tab" aria-selected="false">ข้อมูลการทำงาน</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-selected="false">รหัสผ่าน</button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="profileTabContent">
                                    <!-- แท็บข้อมูลส่วนตัว -->
                                    <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <label class="form-label">คำนำหน้า</label>
                                                <select class="form-select" name="users_name_prefix">
                                                    <option value="นาย" {{ $user->users_name_prefix == 'นาย' ? 'selected' : '' }}>นาย</option>
                                                    <option value="นาง" {{ $user->users_name_prefix == 'นาง' ? 'selected' : '' }}>นาง</option>
                                                    <option value="นางสาว" {{ $user->users_name_prefix == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                                                    <option value="อาจารย์" {{ $user->users_name_prefix == 'อาจารย์' ? 'selected' : '' }}>อาจารย์</option>
                                                    <option value="ดร." {{ $user->users_name_prefix == 'ดร.' ? 'selected' : '' }}>ดร.</option>
                                                </select>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label class="form-label">ชื่อ</label>
                                                <input type="text" class="form-control" name="users_first_name" value="{{ $user->users_first_name }}">
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label class="form-label">นามสกุล</label>
                                                <input type="text" class="form-control" name="users_last_name" value="{{ $user->users_last_name }}">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">เบอร์โทรศัพท์</label>
                                                <input type="tel" class="form-control" name="users_phone_number" value="{{ $user->users_phone_number }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">วันเกิด</label>
                                                <input type="date" class="form-control" name="users_birthdate" value="{{ \Carbon\Carbon::parse($user->users_birthdate)->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- แท็บข้อมูลการทำงาน -->
                                    <div class="tab-pane fade" id="work" role="tabpanel" aria-labelledby="work-tab">
                                        @if($user->teacher)
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">ตำแหน่ง</label>
                                                    <input type="text" class="form-control" name="teachers_position" value="{{ $user->teacher->teachers_position }}" autocomplete="organization-title">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">รหัสประจำตัวครู</label>
                                                    <input type="text" class="form-control" value="{{ $user->teacher->teachers_employee_code }}" disabled>
                                                    <div class="form-text">รหัสประจำตัวไม่สามารถแก้ไขได้</div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">กลุ่มสาระ/ฝ่าย</label>
                                                    <input type="text" class="form-control" name="teachers_department" value="{{ $user->teacher->teachers_department }}" autocomplete="organization">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">วิชาที่สอน</label>
                                                    <input type="text" class="form-control" name="teachers_major" value="คอมพิวเตอร์" autocomplete="off">
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info">ไม่พบข้อมูลการทำงาน</div>
                                        @endif
                                    </div>
                                    
                                    <!-- แท็บเปลี่ยนรหัสผ่าน -->
                                    <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                                        <div class="mb-3">
                                            <label class="form-label">รหัสผ่านเดิม</label>
                                            <input type="password" class="form-control" name="current_password" 
                                                   autocomplete="current-password" placeholder="ใส่รหัสผ่านเดิม">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">รหัสผ่านใหม่</label>
                                            <input type="password" class="form-control" name="new_password" 
                                                   autocomplete="new-password" placeholder="ใส่รหัสผ่านใหม่">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                            <input type="password" class="form-control" name="new_password_confirmation"
                                                   autocomplete="new-password" placeholder="ยืนยันรหัสผ่านใหม่">
                                        </div>
                                        <div class="form-text">เว้นว่างถ้าไม่ต้องการเปลี่ยนรหัสผ่าน</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary-app">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
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
                    <form id="violationForm">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">ค้นหานักเรียน <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="behaviorStudentSearch" placeholder="พิมพ์ชื่อหรือรหัสนักเรียน..." autocomplete="off">
                                <div id="studentResults" class="list-group mt-2" style="display: none;"></div>
                                <input type="hidden" id="selectedStudentId" name="student_id" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">กรองตามห้อง</label>
                                <select class="form-select" id="classFilter">
                                    <option value="">ทุกห้อง</option>
                                    <!-- ตัวเลือกจะถูกเพิ่มด้วย JavaScript -->
                                </select>
                            </div>
                        </div>
                        
                        <div id="selectedStudentInfo" class="alert alert-info" style="display: none;">
                            <h6 class="mb-1">นักเรียนที่เลือก:</h6>
                            <div id="studentInfoDisplay"></div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">ประเภทพฤติกรรม <span class="text-danger">*</span></label>
                                <select class="form-select" id="violationType" name="violation_id" data-violation-select required>
                                    <option value="">เลือกประเภทพฤติกรรม</option>
                                    <!-- ตัวเลือกจะถูกเพิ่มด้วย JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">คะแนนที่หัก <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="pointsDeducted" min="0" max="100" value="0" readonly>
                                <div class="form-text">คะแนนจะกำหนดตามประเภทที่เลือก</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">วันที่เกิดเหตุการณ์ <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="violationDate" name="violation_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เวลาที่เกิดเหตุการณ์ <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="violationTime" name="violation_time" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">รายละเอียดเพิ่มเติม</label>
                            <textarea class="form-control" id="violationDescription" name="description" rows="3" placeholder="อธิบายรายละเอียดของเหตุการณ์..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">แนบหลักฐาน (ถ้ามี)</label>
                            <input type="file" class="form-control" id="evidenceFile" name="evidence" accept="image/*">
                            <div class="form-text">รองรับไฟล์ภาพเท่านั้น (JPG, PNG, GIF)</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary-app" id="saveViolationBtn">
                        <i class="fas fa-save me-1"></i> บันทึกพฤติกรรม
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Violation Types Modal -->
    <div class="modal fade" id="violationTypesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">จัดการประเภทพฤติกรรม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- การค้นหาและการเพิ่มใหม่ -->
                    <div class="d-flex justify-content-between mb-3">
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control" id="violationTypeSearch" placeholder="ค้นหาประเภทพฤติกรรม...">
                            <button class="btn btn-primary-app" type="button"><i class="fas fa-search"></i></button>
                        </div>
                        <button class="btn btn-primary-app" id="btnShowAddViolationType">
                            <i class="fas fa-plus me-2"></i>เพิ่มประเภทพฤติกรรมใหม่
                        </button>
                    </div>
                    
                    <!-- ส่วนแสดงรายการประเภทพฤติกรรม -->
                    <div id="violationTypesList" class="mb-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">ชื่อพฤติกรรม</th>
                                        <th style="width: 15%">ระดับความรุนแรง</th>
                                        <th style="width: 15%">คะแนนที่หัก</th>
                                        <th style="width: 25%">รายละเอียด</th>
                                        <th style="width: 10%"></th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination pagination-sm justify-content-end mt-3 mb-0">
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>
                    
                    <!-- ฟอร์มเพิ่ม/แก้ไขประเภทพฤติกรรม (ซ่อนไว้ก่อน) -->
                    <div class="card d-none" id="violationTypeForm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0" id="formViolationTitle">เพิ่มประเภทพฤติกรรมใหม่</h5>
                                <button type="button" class="btn-close" id="btnCloseViolationForm"></button>
                            </div>
                            
                            <form id="formViolationType">
                                <input type="hidden" id="violationTypeId" name="violations_id">
                                
                                <div class="mb-3">
                                    <label for="violations_name" class="form-label">ชื่อประเภทพฤติกรรม <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="violations_name" name="violations_name" 
                                           placeholder="ระบุชื่อประเภทพฤติกรรม เช่น ผิดระเบียบการแต่งกาย" required maxlength="150">
                                    <div class="form-text">สูงสุด 150 ตัวอักษร</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="violations_category" class="form-label">ระดับความรุนแรง <span class="text-danger">*</span></label>
                                        <select class="form-select" id="violations_category" name="violations_category" required>
                                            <option value="" selected disabled>เลือกระดับความรุนแรง</option>
                                            <option value="light">เบา (light)</option>
                                            <option value="medium">ปานกลาง (medium)</option>
                                            <option value="severe">รุนแรง (severe)</option>
                                        </select>
                                        <div class="form-text">เลือกระดับความรุนแรงตามประเภทของพฤติกรรม</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="violations_points_deducted" class="form-label">คะแนนที่หัก <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="violations_points_deducted" name="violations_points_deducted" 
                                               min="1" max="100" required placeholder="ระบุคะแนนที่จะหัก">
                                        <div class="form-text">ระบุเป็นตัวเลขระหว่าง 1-100</div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="violations_description" class="form-label">รายละเอียดเพิ่มเติม</label>
                                    <textarea class="form-control" id="violations_description" name="violations_description" rows="3"
                                              placeholder="อธิบายรายละเอียดเพิ่มเติมเกี่ยวกับพฤติกรรมนี้..."></textarea>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-secondary me-2" id="btnCancelViolationType">ยกเลิก</button>
                                    <button type="submit" class="btn btn-primary-app" id="btnSaveViolationType">บันทึก</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Violation Confirmation Modal -->
    <div class="modal fade" id="deleteViolationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    <h5>ยืนยันการลบประเภทพฤติกรรมนี้?</h5>
                    <p class="text-muted">การลบประเภทพฤติกรรมนี้อาจส่งผลกระทบต่อข้อมูลพฤติกรรมที่บันทึกไว้แล้ว</p>
                    <input type="hidden" id="deleteViolationId">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteViolation">ยืนยันการลบ</button>
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
                                <img src="https://placehold.co/400x300?text=รูปภาพตัวอย่าง" class="img-fluid rounded" alt="รูปภาพหลักฐาน">
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

    <!-- Class Management Modal -->
    <div class="modal fade" id="classManagementModal" tabindex="-1" aria-labelledby="classManagementModalLabel" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="classManagementModalLabel">จัดการห้องเรียน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- การค้นหาและการเพิ่มใหม่ -->
                    <div class="d-flex justify-content-between mb-3">
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control" id="classroomSearch" placeholder="ค้นหาห้องเรียน..." autocomplete="off">
                            <button class="btn btn-primary-app" type="button" id="btnSearchClass"><i class="fas fa-search"></i></button>
                        </div>
                        <button class="btn btn-primary-app" id="btnShowAddClass">
                            <i class="fas fa-plus me-2"></i>เพิ่มห้องเรียนใหม่
                        </button>
                    </div>
                    
                    <!-- ตัวกรองข้อมูล -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">ปีการศึกษา</label>
                                    <select class="form-select form-select-sm" id="filterAcademicYear" autocomplete="off">
                                        <option value="">ทั้งหมด</option>
                                        <!-- จะถูกเติมโดย JavaScript -->
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">ระดับชั้น</label>
                                    <select class="form-select form-select-sm" id="filterLevel" autocomplete="off">
                                        <option value="">ทั้งหมด</option>
                                        <!-- จะถูกเติมโดย JavaScript -->
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-sm btn-outline-secondary w-100" id="btnApplyFilter">
                                        <i class="fas fa-filter me-1"></i> กรองข้อมูล
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ส่วนแสดงรายการห้องเรียน -->
                    <div id="classroomList" class="mb-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%">ชั้นเรียน</th>
                                        <th style="width: 20%">ปีการศึกษา</th>
                                        <th style="width: 40%">ครูประจำชั้น</th>
                                        <th style="width: 15%">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- จะถูกเติมโดย JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination pagination-sm justify-content-end mt-3 mb-0">
                                <!-- จะถูกเติมโดย JavaScript -->
                            </ul>
                        </nav>
                    </div>
                    
                    <!-- ฟอร์มเพิ่ม/แก้ไขห้องเรียน (ซ่อนไว้ก่อน) -->
                    <div class="card d-none" id="classroomForm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0" id="formClassTitle">เพิ่มห้องเรียนใหม่</h5>
                                <button type="button" class="btn-close" id="btnCloseClassForm"></button>
                            </div>
                            
                            <form id="formClassroom">
                                <input type="hidden" id="classId" name="classes_id">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="classes_level" class="form-label">ระดับชั้น <span class="text-danger">*</span></label>
                                        <select class="form-select" id="classes_level" name="classes_level" required autocomplete="off">
                                            <option value="" selected disabled>เลือกระดับชั้น</option>
                                            <option value="ม.1">ม.1</option>
                                            <option value="ม.2">ม.2</option>
                                            <option value="ม.3">ม.3</option>
                                            <option value="ม.4">ม.4</option>
                                            <option value="ม.5">ม.5</option>
                                            <option value="ม.6">ม.6</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกระดับชั้น</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="classes_room_number" class="form-label">ห้อง <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="classes_room_number" name="classes_room_number" 
                                               placeholder="ระบุเลขห้อง เช่น 1, 2, 3, ..." required maxlength="5" autocomplete="off">
                                        <div class="invalid-feedback">กรุณาระบุเลขห้อง</div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="classes_academic_year" class="form-label">ปีการศึกษา <span class="text-danger">*</span></label>
                                        <select class="form-select" id="classes_academic_year" name="classes_academic_year" required autocomplete="off">
                                            <option value="" selected disabled>เลือกปีการศึกษา</option>
                                            <option value="2566">2566</option>
                                            <option value="2567">2567</option>
                                            <option value="2568">2568</option>
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกปีการศึกษา</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="teacher_id" class="form-label">ครูประจำชั้น <span class="text-danger">*</span></label>
                                        <select class="form-select" id="teacher_id" name="teacher_id" required autocomplete="off">
                                            <option value="" selected disabled>เลือกครูประจำชั้น</option>
                                            <!-- จะถูกเติมโดย JavaScript -->
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกครูประจำชั้น</div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-secondary me-2" id="btnCancelClass">ยกเลิก</button>
                                    <button type="submit" class="btn btn-primary-app" id="btnSaveClass">บันทึก</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Detail Modal -->
    <div class="modal fade" id="classDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">รายละเอียดห้องเรียน <span class="class-title"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Loading Indicator -->
                    <div id="classDetailLoading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                        <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
                    </div>

                    <div id="classDetailContent">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex align-items-center">
                                            <i class="fas fa-info-circle me-2 text-primary"></i>ข้อมูลห้องเรียน
                                        </h6>
                                        <hr>
                                        <div class="row mb-2">
                                            <div class="col-sm-5 text-muted">ชั้นเรียน:</div>
                                            <div class="col-sm-7 fw-medium" id="class-level-room"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5 text-muted">ปีการศึกษา:</div>
                                            <div class="col-sm-7 fw-medium" id="class-academic-year"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5 text-muted">ครูประจำชั้น:</div>
                                            <div class="col-sm-7 fw-medium" id="class-teacher-name"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5 text-muted">จำนวนนักเรียน:</div>
                                            <div class="col-sm-7 fw-medium" id="class-students-count"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5 text-muted">คะแนนเฉลี่ย:</div>
                                            <div class="col-sm-7">
                                                <div class="d-flex align-items-center">
                                                    <span class="fw-medium me-2" id="class-avg-score">-</span>
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-success" role="progressbar" id="class-avg-score-bar" style="width: 0%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex align-items-center">
                                            <i class="fas fa-chart-pie me-2 text-primary"></i>สถิติการกระทำผิด
                                        </h6>
                                        <hr>
                                        <div id="chart-container" class="d-flex justify-content-center align-items-center" style="height: 200px;">
                                            <canvas id="classViolationChart"></canvas>
                                            <div id="no-violations-message" class="text-center text-muted d-none">
                                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                                <p>ไม่พบข้อมูลการกระทำผิดในห้องเรียนนี้</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-users me-2 text-primary"></i>รายชื่อนักเรียน
                                    <span class="badge bg-primary-app rounded-pill ms-2" id="student-count-badge">0</span>
                                </h6>
                                <div class="d-flex">
                                    <div class="input-group input-group-sm" style="width: 250px;">
                                        <input type="text" class="form-control" id="studentSearch" placeholder="ค้นหานักเรียน...">
                                        <button class="btn btn-sm btn-primary-app" id="btnSearchStudent">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 8%">เลขที่</th>
                                                <th style="width: 15%">รหัสนักเรียน</th>
                                                <th style="width: 32%">ชื่อ-สกุล</th>
                                                <th style="width: 25%">คะแนนคงเหลือ</th>
                                                <th style="width: 20%">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="students-list">
                                            <!-- ข้อมูลจะถูกเติมโดย JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <nav>
                                    <ul class="pagination pagination-sm justify-content-end mb-0" id="student-pagination">
                                        <!-- การแบ่งหน้าจะถูกสร้างโดย JavaScript -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-primary me-auto" id="btnExportClassReport">
                        <i class="fas fa-file-export me-1"></i> ส่งออกรายงาน
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary-app" id="btnEditClassDetail">
                        <i class="fas fa-edit me-1"></i> แก้ไขข้อมูลห้องเรียน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Class Confirmation Modal -->
    <div class="modal fade" id="deleteClassModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">ยืนยันการลบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    <h5>ยืนยันการลบห้องเรียนนี้?</h5>
                    <p class="text-muted">การลบห้องเรียนอาจส่งผลกระทบต่อข้อมูลนักเรียน และข้อมูลพฤติกรรมที่บันทึกไว้</p>
                    <input type="hidden" id="deleteClassId">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteClass">ยืนยันการลบ</button>
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
    <script src="/js/violation-manager.js"></script>
    <script src="/js/class-manager.js"></script>
    <script src="/js/class-detail.js"></script>
    <!-- เพิ่ม behavior report script -->
    <script src="/js/behavior-report.js"></script>
</body>
</html>