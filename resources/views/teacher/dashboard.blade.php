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
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
                    <span>ส่งออกรายงาน</span>
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
                            <h1 class="fw-bold">สวัสดี, {{ $user->users_name_prefix }}{{ $user->users_first_name }} {{ $user->users_last_name }}</h1>
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
                                            <h4 class="stat-value">0</h4>
                                            <p class="stat-change mb-0 no-change">
                                                <i class="fas fa-equals me-1"></i>
                                                0% จากเดือนก่อน
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
                                            <h4 class="stat-value">0</h4>
                                            <p class="stat-change mb-0 no-change">
                                                <i class="fas fa-equals me-1"></i>
                                                0% จากเดือนก่อน
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
                                            <h4 class="stat-value">0</h4>
                                            <p class="stat-change mb-0 no-change">
                                                <i class="fas fa-equals me-1"></i>
                                                0% จากเดือนก่อน
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
                                            <h4 class="stat-value">100.0</h4>
                                            <p class="stat-change mb-0 no-change">
                                                <i class="fas fa-equals me-1"></i>
                                                0 คะแนนจากเดือนก่อน
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
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#studentFilterModal">
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
                                                @forelse($students as $student)
                                                <tr>
                                                    <td>{{ $student->students_student_code ?? '-' }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @php
                                                                $studentName = ($student->user->users_name_prefix ?? '') . ($student->user->users_first_name ?? '') . ' ' . ($student->user->users_last_name ?? '');
                                                                $avatarUrl = $student->user->users_profile_image 
                                                                    ? asset('storage/' . $student->user->users_profile_image)
                                                                    : 'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=95A4D8&color=fff';
                                                            @endphp
                                                            <img src="{{ $avatarUrl }}" class="rounded-circle me-2" width="32" height="32" alt="{{ $studentName }}">
                                                            <span>{{ $studentName }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($student->classroom)
                                                            {{ $student->classroom->classes_level }}/{{ $student->classroom->classes_room_number }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $score = $student->students_current_score ?? 100;
                                                            $progressClass = 'bg-success';
                                                            if ($score <= 50) {
                                                                $progressClass = 'bg-danger';
                                                            } elseif ($score <= 75) {
                                                                $progressClass = 'bg-warning';
                                                            }
                                                        @endphp
                                                        <div style="margin-bottom: 5px; margin-top: 10px;">
                                                            <div class="progress" style="height: 8px; width: 100px; position: relative; margin-top: 10px;">
                                                                <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $score }}%"></div>
                                                                @if($score >= 90)
                                                                <div style="position: absolute; left: {{ $score }}%; top: -10px; transform: translateX(-50%); 
                                                                            background-color: white; width: 24px; height: 24px; 
                                                                            border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.3); 
                                                                            display: flex; align-items: center; justify-content: center; 
                                                                            border: 2px solid white; z-index: 10;">
                                                                    <img src="{{ asset('images/smile.png') }}" 
                                                                         style="height: 16px; width: 16px;" 
                                                                         alt="👍">
                                                                </div>
                                                                @endif
                                                            </div>
                                                            </div>
                                                            <span class="small">{{ $score }}/100</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @php
                                                            // นับจำนวนการกระทำผิดของนักเรียน
                                                            $violationCount = App\Models\BehaviorReport::where('student_id', $student->students_id)->count();
                                                        @endphp
                                                        {{ $violationCount }} ครั้ง
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary-app" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#studentDetailModal"
                                                                data-student-id="{{ $student->students_id }}">
                                                            <i class="fas fa-user me-1"></i> ดูข้อมูล
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                                                            <p>ไม่พบข้อมูลนักเรียน</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white">
                                    <nav>
                                        {{ $students->links('pagination::bootstrap-4') }}
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
            <div class="modal-content animate__animated animate__fadeInUp animate__faster">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">จัดการประเภทพฤติกรรม</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- การค้นหาและการเพิ่มใหม่ -->
                    <div class="d-flex justify-content-between mb-3 animate__animated animate__fadeIn" style="animation-delay: 0.1s">
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control" id="violationTypeSearch" placeholder="ค้นหาประเภทพฤติกรรม...">
                            <button class="btn btn-primary-app" type="button"><i class="fas fa-search"></i></button>
                        </div>
                        <button class="btn btn-primary-app" id="btnShowAddViolationType">
                            <i class="fas fa-plus me-2"></i>เพิ่มประเภทพฤติกรรมใหม่
                        </button>
                    </div>
                    
                    <!-- ส่วนแสดงรายการประเภทพฤติกรรม -->
                    <div id="violationTypesList" class="mb-4 animate__animated animate__fadeIn" style="animation-delay: 0.2s">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%">ชื่อพฤติกรรม</th>
                                        <th style="width: 15%" class="text-center">ระดับความรุนแรง</th>
                                        <th style="width: 15%" class="text-center">คะแนนที่หัก</th>
                                        <th style="width: 25%">รายละเอียด</th>
                                        <th style="width: 10%" class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- ข้อมูลจะถูกเติมด้วย JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <nav aria-label="Violation types pagination">
                            <ul class="pagination pagination-sm justify-content-center mt-3 mb-0">
                                <!-- การแบ่งหน้าจะถูกสร้างด้วย JavaScript -->
                            </ul>
                        </nav>
                    </div>
                    
                    <!-- ฟอร์มเพิ่ม/แก้ไขประเภทพฤติกรรม (ซ่อนไว้ก่อน) -->
                    <div class="card d-none" id="violationTypeForm">
                        <div class="card-body">
                            <!-- เนื้อหาฟอร์มจะถูกเติมด้วย JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Violation Type Modal -->
    <div class="modal fade" id="addViolationTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content animate__animated animate__fadeInUp animate__faster">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">เพิ่มประเภทพฤติกรรมใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addViolationTypeForm" class="needs-validation" novalidate>
                        <input type="hidden" id="violation_id" name="id">
                        
                        <div class="mb-3">
                            <label for="violation_name" class="form-label">ชื่อพฤติกรรม <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="violation_name" name="name" 
                                   placeholder="ระบุชื่อพฤติกรรม เช่น มาสาย, ไม่ทำการบ้าน" required>
                            <div class="invalid-feedback">กรุณาระบุชื่อพฤติกรรม</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="violation_category" class="form-label">ระดับความรุนแรง <span class="text-danger">*</span></label>
                                <select class="form-select" id="violation_category" name="category" required>
                                    <option value="" selected disabled>เลือกระดับความรุนแรง</option>
                                    <option value="light">เบา</option>
                                    <option value="medium">ปานกลาง</option>
                                    <option value="severe">หนัก</option>
                                </select>
                                <div class="invalid-feedback">กรุณาเลือกระดับความรุนแรง</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="violation_points" class="form-label">คะแนนที่หัก <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="violation_points" name="points_deducted" 
                                       min="0" max="100" required placeholder="ระบุคะแนนที่หัก">
                                <div class="invalid-feedback">กรุณาระบุคะแนนที่หัก (0-100)</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="violation_description" class="form-label">รายละเอียด</label>
                            <textarea class="form-control" id="violation_description" name="description" 
                                      rows="3" placeholder="อธิบายรายละเอียดเพิ่มเติม (ถ้ามี)"></textarea>
                        </div>
                        
                        <div class="alert alert-success save-success d-none">
                            <i class="fas fa-check-circle me-2"></i>
                            บันทึกข้อมูลสำเร็จ
                        </div>
                        
                        <div class="alert alert-danger save-error d-none">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <span class="error-message">เกิดข้อผิดพลาดในการบันทึกข้อมูล</span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary-app" id="btnSaveViolationType">
                        <i class="fas fa-save me-1"></i> บันทึกข้อมูล
                    </button>
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
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">ส่งออกรายงาน</h5>
                            <p class="card-text text-muted">เลือกรูปแบบรายงานที่ต้องการส่งออก</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary d-flex justify-content-between align-items-center" 
                                        id="generateMonthlyReport" 
                                        onclick="generateMonthlyReport()">
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
                    <!-- Loading State -->
                    <div id="studentDetailLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                        <p class="mt-2 text-muted">กำลังโหลดข้อมูลนักเรียน...</p>
                    </div>
                    
                    <!-- Error State -->
                    <div id="studentDetailError" class="text-center py-5 text-danger" style="display: none;">
                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                        <p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
                        <button class="btn btn-outline-primary btn-sm" onclick="retryLoadStudentDetail()">ลองใหม่</button>
                    </div>
                    
                    <!-- Content -->
                    <div id="studentDetailContent" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <img id="studentProfileImage" class="rounded-circle" width="100" height="100" alt="รูปโปรไฟล์">
                                    <h5 id="studentFullName" class="mt-3 mb-1"></h5>
                                    <span id="studentClassBadge" class="badge bg-primary-app"></span>
                                    <hr>
                                    <div class="d-grid gap-2 mt-3">
                                        <button class="btn btn-primary-app" onclick="openNewViolationModal()">บันทึกพฤติกรรม</button>
                                        <button id="printReportBtn" class="btn btn-outline-secondary" onclick="printStudentReport(event)">พิมพ์รายงาน</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-bold">รหัสนักเรียน</label>
                                        <p id="studentCode"></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">ชั้นเรียน</label>
                                        <p id="studentClass"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-bold">เลขประจำตัวประชาชน</label>
                                        <p id="studentIdNumber"></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">วันเกิด</label>
                                        <p id="studentBirthdate"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-bold">ชื่อผู้ปกครอง</label>
                                        <p id="guardianName"></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">เบอร์โทรผู้ปกครอง</label>
                                        <p id="guardianPhone"></p>
                                    </div>
                                </div>
                                
                                <h6 class="mt-4">สถิติคะแนนความประพฤติ</h6>
                                <div style="position: relative; margin-bottom: 25px; margin-top: 30px;">
                                    <div id="scoreIcon" style="position: absolute; top: -10px; z-index: 1000; 
                                                background-color: white; width: 40px; height: 40px; 
                                                border-radius: 50%; box-shadow: 0 3px 10px rgba(0,0,0,0.4); 
                                                display: flex; align-items: center; justify-content: center; 
                                                border: 3px solid white;">
                                        <img src="{{ asset('images/smile.png') }}" 
                                             style="height: 30px; width: 30px;" 
                                             alt="👍">
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div id="scoreProgressBar" class="progress-bar" role="progressbar"></div>
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
                                        <tbody id="behaviorHistoryTable">
                                            <!-- ข้อมูลจะถูกเติมด้วย JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
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
                <div class="modal-body" id="violationDetailContent">
                    <!-- Loading State -->
                    <div id="violationDetailLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                        <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
                    </div>
                    
                    <!-- Content will be loaded here -->
                    <div id="violationDetailData" style="display: none;">
                        <div class="d-flex align-items-center mb-3" id="studentInfo">
                            <!-- Student info will be loaded here -->
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-body" id="violationInfo">
                                <!-- Violation details will be loaded here -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Error State -->
                    <div id="violationDetailError" class="text-center py-4 text-danger" style="display: none;">
                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                        <p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-danger me-auto" id="deleteReportBtn" style="display: none;">
                        ลบบันทึก
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary-app" id="editReportBtn" style="display: none;">
                        แก้ไข
                    </button>
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
                                        <th style="width: 15%">ชั้นเรียน</th>
                                        <th style="width: 20%">ปีการศึกษา</th>
                                        <th style="width: 25%">ครูประจำชั้น</th>
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

    <!-- Monthly Report Modal -->
    <div class="modal fade" id="monthlyReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">สร้างรายงานพฤติกรรมประจำเดือน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="report_month" class="form-label">เลือกเดือน</label>
                        <select class="form-select" id="report_month">
                            <option value="1">มกราคม</option>
                            <option value="2">กุมภาพันธ์</option>
                            <option value="3">มีนาคม</option>
                            <option value="4">เมษายน</option>
                            <option value="5">พฤษภาคม</option>
                            <option value="6">มิถุนายน</option>
                            <option value="7">กรกฎาคม</option>
                            <option value="8">สิงหาคม</option>
                            <option value="9">กันยายน</option>
                            <option value="10">ตุลาคม</option>
                            <option value="11">พฤศจิกายน</option>
                            <option value="12">ธันวาคม</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="report_year" class="form-label">เลือกปี</label>
                        <select class="form-select" id="report_year">
                            @php
                                $currentYear = (int)date('Y');
                                $startYear = $currentYear - 2;
                                $endYear = $currentYear + 1;
                            @endphp
                            @for($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                    {{ $year + 543 }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="report_class_id" class="form-label">เลือกห้องเรียน (ไม่ระบุเพื่อดูทั้งหมด)</label>
                        <select class="form-select" id="report_class_id">
                            <option value="">ทุกห้องเรียน</option>
                            <!-- ตัวเลือกจะถูกเติมด้วย JavaScript หรือข้อมูลจาก Controller -->
                            @foreach($classes ?? [] as $class)
                                <option value="{{ $class->classes_id }}">
                                    {{ $class->classes_level }}/{{ $class->classes_room_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary-app" onclick="downloadMonthlyReport()">
                        <i class="fas fa-download me-2"></i>สร้างรายงาน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Filter Modal -->
    <div class="modal fade" id="studentFilterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">กรองข้อมูลนักเรียน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="studentFilterForm">
                        <!-- ค้นหาจากชื่อ/รหัสนักเรียน -->
                        <div class="mb-3">
                            <label for="filter_name" class="form-label">ชื่อหรือรหัสนักเรียน</label>
                            <input type="text" class="form-control" id="filter_name" placeholder="พิมพ์ชื่อหรือรหัสนักเรียน...">
                        </div>
                        
                        <!-- กรองตามระดับชั้น -->
                        <div class="mb-3">
                            <label for="filter_class_level" class="form-label">ระดับชั้น</label>
                            <select class="form-select" id="filter_class_level">
                                <option value="">ทุกระดับชั้น</option>
                                <option value="ม.1">ม.1</option>
                                <option value="ม.2">ม.2</option>
                                <option value="ม.3">ม.3</option>
                                <option value="ม.4">ม.4</option>
                                <option value="ม.5">ม.5</option>
                                <option value="ม.6">ม.6</option>
                            </select>
                        </div>
                        
                        <!-- กรองตามห้อง -->
                        <div class="mb-3">
                            <label for="filter_class_room" class="form-label">ห้อง</label>
                            <select class="form-select" id="filter_class_room">
                                <option value="">ทุกห้อง</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                        </div>
                        
                        <!-- กรองตามคะแนนคงเหลือ -->
                        <div class="mb-3">
                            <label class="form-label">คะแนนคงเหลือ</label>
                            <div class="d-flex gap-2 align-items-center">
                                <select class="form-select" id="filter_score_operator">
                                    <option value="any">ไม่ระบุ</option>
                                    <option value="less">น้อยกว่า</option>
                                    <option value="more">มากกว่า</option>
                                    <option value="equal">เท่ากับ</option>
                                </select>
                                <input type="number" class="form-control" id="filter_score_value" min="0" max="100" value="75" disabled>
                            </div>
                        </div>
                        
                        <!-- กรองตามจำนวนครั้งที่ทำผิด -->
                        <div class="mb-3">
                            <label class="form-label">จำนวนครั้งที่ทำผิด</label>
                            <div class="d-flex gap-2 align-items-center">
                                <select class="form-select" id="filter_violation_operator">
                                    <option value="any">ไม่ระบุ</option>
                                    <option value="less">น้อยกว่า</option>
                                    <option value="more">มากกว่า</option>
                                    <option value="equal">เท่ากับ</option>
                                </select>
                                <input type="number" class="form-control" id="filter_violation_value" min="0" value="5" disabled>
                            </div>
                        </div>
                        
                        <!-- กรองตามสถานะความเสี่ยง -->
                        <div class="mb-3">
                            <label class="form-label">สถานะความเสี่ยง</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="filter_risk_high" value="high">
                                <label class="form-check-label" for="filter_risk_high">
                                    <span class="badge bg-danger">ความเสี่ยงสูง</span> (คะแนนต่ำกว่า 60)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="filter_risk_medium" value="medium">
                                <label class="form-check-label" for="filter_risk_medium">
                                    <span class="badge bg-warning text-dark">ความเสี่ยงปานกลาง</span> (คะแนน 60-75)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="filter_risk_low" value="low">
                                <label class="form-check-label" for="filter_risk_low">
                                    <span class="badge bg-success">ความเสี่ยงต่ำ</span> (คะแนนมากกว่า 75)
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-link text-secondary" id="resetFilterBtn">รีเซ็ตตัวกรอง</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary-app" id="applyFilterBtn">
                        <i class="fas fa-filter me-1"></i> ใช้ตัวกรอง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Parent Notification Modal -->
    <div class="modal fade" id="parentNotificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">แจ้งเตือนผู้ปกครอง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="notification-student-info" class="alert alert-light border mb-3"></div>
                    
                    <div id="notification-warning" class="alert alert-danger d-none">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>คะแนนความประพฤติต่ำกว่าเกณฑ์</strong>
                        </div>
                        <p class="mb-0">นักเรียนมีคะแนนความประพฤติต่ำมาก จำเป็นต้องได้รับการดูแลและติดตามอย่างใกล้ชิด</p>
                    </div>
                    
                    <form id="notification-form">
                        <input type="hidden" id="notification-student-id">
                        <input type="hidden" id="notification-score">
                        <input type="hidden" id="notification-phone">
                        
                        <div class="mb-3">
                            <label for="notification-type" class="form-label">ประเภทการแจ้งเตือน</label>
                            <select class="form-select" id="notification-type" onchange="updateNotificationTemplate()">
                                <option value="behavior">พฤติกรรมเบี่ยงเบน</option>
                                <option value="attendance">การขาดเรียน</option>
                                <option value="meeting">เชิญประชุมผู้ปกครอง</option>
                                <option value="custom">กำหนดเอง</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notification-message" class="form-label">ข้อความ</label>
                            <textarea class="form-control" id="notification-message" rows="5" required></textarea>
                            <div class="form-text">
                                <span id="message-suggestion" class="text-primary cursor-pointer d-none" onclick="applyMessageSuggestion()">
                                    <i class="fas fa-lightbulb"></i> ใช้ข้อความแนะนำ
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notification-method" class="form-label">วิธีการแจ้งเตือน</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notification-sms" checked>
                                <label class="form-check-label" for="notification-sms">
                                    <i class="fas fa-sms me-1"></i> SMS (<span id="notification-phone-display"></span>)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notification-line">
                                <label class="form-check-label" for="notification-line">
                                    <i class="fab fa-line me-1"></i> Line
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="notification-system" checked>
                                <label class="form-check-label" for="notification-system">
                                    <i class="fas fa-bell me-1"></i> ระบบแจ้งเตือน
                                </label>
                            </div>
                        </div>
                    </form>
                    
                    <div id="notification-success" class="alert alert-success d-none">
                        <i class="fas fa-check-circle me-2"></i> ส่งการแจ้งเตือนสำเร็จแล้ว
                    </div>
                    
                    <div id="notification-error" class="alert alert-danger d-none">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span id="notification-error-message">เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง</span>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="send-notification-btn" onclick="sendParentNotification()">
                        <i class="fas fa-paper-plane me-1"></i> ส่งการแจ้งเตือน
                    </button>
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
    <!-- Reports JS -->
    <script src="/js/reports.js"></script>
    <script src="/js/student-filter.js"></script>
    <script src="/js/parent-notification.js"></script>
</body>
</html>