<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบสารสนเทศจัดการคะแนนนักเรียน - หน้าผู้ปกครอง</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- App CSS -->
    <link href="/css/app.css" rel="stylesheet">
    <!-- Parent Dashboard Specific CSS -->
    <link href="{{ asset('css/parent.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/student.css') }}">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Theme Toggle -->
    <div id="theme-toggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </div>
    
    <div class="app-container">
        <!-- Desktop Navbar (displays on larger screens) -->
        <nav class="desktop-navbar d-none d-lg-flex">
            <div class="desktop-navbar-container">
                <div class="desktop-navbar-brand">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ระบบจัดการคะแนนพฤติกรรม</span>
                </div>
                <div class="desktop-navbar-menu">
                    <a href="javascript:void(0);" class="desktop-nav-link active">
                        <i class="fas fa-home"></i>
                        <span>หน้าหลัก</span>
                    </a>
                    <a href="javascript:void(0);" class="desktop-nav-link">
                        <i class="fas fa-history"></i>
                        <span>ประวัติ</span>
                    </a>
                    <a href="javascript:void(0);" class="desktop-nav-link">
                        <i class="fas fa-bell"></i>
                        <span>การแจ้งเตือน</span>
                    </a>
                    <a href="javascript:void(0);" class="desktop-nav-link">
                        <i class="fas fa-user"></i>
                        <span>โปรไฟล์</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <!-- Header (Mobile only) -->
        <header class="dashboard-header text-white py-3 d-lg-none">
            <div class="container">
                <h1 class="h4 mb-0">ระบบสารสนเทศจัดการคะแนนพฤติกรรมนักเรียน</h1>
            </div>
        </header>

        <!-- Main Content -->
        <div class="container py-4">
            <!-- Parent Info Card -->
            <div class="app-card parent-info-card mb-4 p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="parent-avatar rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 60px; height: 60px;">
                            <img src="{{ asset('images/profile.png') }}" alt="โปรไฟล์ผู้ปกครอง" class="w-100 h-100 object-fit-cover">
                        </div>
                    </div>
                    <div>
                        <h2 class="h5 mb-1">สวัสดี คุณ ขจรศักดิ์ ภูมิมาลา</h2>
                        <p class="text-muted mb-0">ผู้ปกครองนักเรียน 3 คน</p>
                    </div>
                    <div class="ms-auto notification-badge">
                        <span class="position-relative">
                            <i class="fas fa-bell fs-4"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                2
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Student Selector -->
            <div class="app-card student-selector mb-4 p-3">
                <h3 class="h5 mb-3">เลือกนักเรียนในการดูแล</h3>
                <div class="student-tabs">
                    <button class="student-tab active" data-student="all">
                        <i class="fas fa-users"></i>
                        <span>ทั้งหมด</span>
                    </button>
                    <button class="student-tab" data-student="student1">
                        <div class="student-avatar-small">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <span>ด.ช. กล้า ภูมิมาลา</span>
                    </button>
                    <button class="student-tab" data-student="student2">
                        <div class="student-avatar-small">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <span>ด.ญ. ใจดี ภูมิมาลา</span>
                    </button>
                    <button class="student-tab" data-student="student3">
                        <div class="student-avatar-small">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <span>ด.ช. จริง ภูมิมาลา</span>
                    </button>
                </div>
            </div>

            <!-- All Students Summary (default view) -->
            <div id="all-students-view">
                <div class="section-header d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h5 mb-0">สรุปคะแนนรวมทุกนักเรียน</h3>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="timeRangeDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-alt"></i> ภาคเรียนนี้
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="timeRangeDropdown">
                            <li><a class="dropdown-item" href="#">วันนี้</a></li>
                            <li><a class="dropdown-item" href="#">สัปดาห์นี้</a></li>
                            <li><a class="dropdown-item" href="#">เดือนนี้</a></li>
                            <li><a class="dropdown-item" href="#">ภาคเรียนนี้</a></li>
                            <li><a class="dropdown-item" href="#">ปีการศึกษานี้</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Student Cards Summary -->
                <div class="desktop-grid-summary">
                    <!-- Student 1 Summary Card -->
                    <div class="app-card student-summary-card p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="student-avatar-summary bg-primary-app text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">ด.ช. กล้า ภูมิมาลา</h4>
                                <p class="text-muted small mb-0">ชั้น ม.2/3 เลขที่ 5</p>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-success">95 คะแนน</span>
                            </div>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 95%;" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <span class="text-success small fw-medium">
                                <i class="fas fa-arrow-up"></i> +5 คะแนน สัปดาห์นี้
                            </span>
                            <a href="javascript:void(0);" class="view-details-link" data-student="student1">
                                ดูรายละเอียด <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Student 2 Summary Card -->
                    <div class="app-card student-summary-card p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="student-avatar-summary bg-primary-app text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">ด.ญ. ใจดี ภูมิมาลา</h4>
                                <p class="text-muted small mb-0">ชั้น ม.3/1 เลขที่ 12</p>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-warning text-dark">75 คะแนน</span>
                            </div>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <span class="text-danger small fw-medium">
                                <i class="fas fa-arrow-down"></i> -3 คะแนน สัปดาห์นี้
                            </span>
                            <a href="javascript:void(0);" class="view-details-link" data-student="student2">
                                ดูรายละเอียด <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Student 3 Summary Card -->
                    <div class="app-card student-summary-card p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="student-avatar-summary bg-primary-app text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">ด.ช. จริง ภูมิมาลา</h4>
                                <p class="text-muted small mb-0">ชั้น ม.1/2 เลขที่ 18</p>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-primary">85 คะแนน</span>
                            </div>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <span class="text-success small fw-medium">
                                <i class="fas fa-arrow-up"></i> +2 คะแนน สัปดาห์นี้
                            </span>
                            <a href="javascript:void(0);" class="view-details-link" data-student="student3">
                                ดูรายละเอียด <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Notifications for All Students -->
                <div class="section-header d-flex justify-content-between align-items-center mt-4 mb-3">
                    <h3 class="h5 mb-0">การแจ้งเตือนล่าสุด</h3>
                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary">
                        ดูทั้งหมด <i class="fas fa-chevron-right"></i>
                    </a>
                </div>

                <div class="app-card p-3">
                    <div class="notification-list">
                        <div class="notification-item d-flex py-2 border-bottom">
                            <div class="me-3">
                                <div class="bg-danger rounded-circle notification-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-exclamation text-white"></i>
                                </div>
                            </div>
                            <div>
                                <p class="mb-0 fw-medium">ด.ช. กล้า มาสาย 3 วันติดต่อกัน</p>
                                <p class="text-muted small mb-0">วันนี้, 08:30 น.</p>
                            </div>
                            <div class="ms-auto align-self-center">
                                <span class="badge bg-danger">ด่วน</span>
                            </div>
                        </div>
                        <div class="notification-item d-flex py-2 border-bottom">
                            <div class="me-3">
                                <div class="bg-warning rounded-circle notification-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-minus-circle text-white"></i>
                                </div>
                            </div>
                            <div>
                                <p class="mb-0 fw-medium">ด.ญ. ใจดี ถูกหักคะแนน -3 จากการไม่ส่งการบ้าน</p>
                                <p class="text-muted small mb-0">เมื่อวานนี้, 14:20 น.</p>
                            </div>
                            <div class="ms-auto align-self-center">
                                <span class="badge bg-warning text-dark">แจ้งเตือน</span>
                            </div>
                        </div>
                        <div class="notification-item d-flex py-2">
                            <div class="me-3">
                                <div class="bg-success rounded-circle notification-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-award text-white"></i>
                                </div>
                            </div>
                            <div>
                                <p class="mb-0 fw-medium">ด.ช. จริง ได้รับเหรียญรางวัล "นักเรียนดีเด่น"</p>
                                <p class="text-muted small mb-0">3 วันที่แล้ว</p>
                            </div>
                            <div class="ms-auto align-self-center">
                                <span class="badge bg-success">ข่าวดี</span>
                            </div>
                        </div>
                    </div>
                </div>
            
            </div>

            <!-- Individual Student View (initially hidden) -->
            <div id="individual-student-view" class="d-none">
                <!-- Back to all students button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-sm btn-outline-primary back-to-all">
                        <i class="fas fa-arrow-left"></i> กลับไปยังภาพรวม
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar-alt"></i> ภาคเรียนนี้
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">วันนี้</a></li>
                            <li><a class="dropdown-item" href="#">สัปดาห์นี้</a></li>
                            <li><a class="dropdown-item" href="#">เดือนนี้</a></li>
                            <li><a class="dropdown-item" href="#">ภาคเรียนนี้</a></li>
                            <li><a class="dropdown-item" href="#">ปีการศึกษานี้</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Student Header Info -->
                <div class="student-header app-card p-3 mb-4">
                    <div class="d-flex align-items-center">
                        <div class="student-avatar bg-primary-app text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="h5 mb-1 student-name">ด.ช. กล้า ภูมิมาลา</h3>
                            <p class="text-muted mb-0 student-class">ชั้น ม.2/3 เลขที่ 5</p>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-success student-points-badge">95 คะแนน</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop Grid for Individual Student -->
                <div class="desktop-grid">
                    <!-- Left Column: Points and Rank -->
                    <div class="metrics-area">
                        <!-- Points Score Card -->
                        <div class="app-card stats-card p-3">
                            <div class="text-center">
                                <h3 class="h5 text-primary-app mb-3">คะแนนความประพฤติ</h3>
                                <p class="display-4 fw-bold mb-2 stats-value student-points">95</p>
                                <span class="badge bg-success student-status">ดีมาก</span>
                            </div>
                        </div>
                        
                        <!-- Class Rank Card -->
                        <div class="app-card stats-card p-3 mt-4">
                            <div class="text-center">
                                <h3 class="h5 text-primary-app mb-3">อันดับในห้องเรียน</h3>
                                <p class="display-4 fw-bold mb-2 stats-value student-rank">3<span class="fs-6">/30</span></p>
                                <span class="badge bg-secondary-app text-dark">กลุ่มหัวหน้า</span>
                            </div>
                        </div>

                        <!-- Teacher Contact Card -->
                        <div class="app-card p-3 mt-4">
                            <h3 class="h5 text-primary-app mb-3">ครูประจำชั้น</h3>
                            <div class="d-flex align-items-center">
                                <div class="teacher-avatar bg-secondary-app rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user-tie text-dark"></i>
                                </div>
                                <div class="ms-3">
                                    <h4 class="h6 mb-1">อ.สมชาย รักดี</h4>
                                    <a href="tel:0812345678" class="small text-primary">
                                        <i class="fas fa-phone-alt"></i> 081-234-5678
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Middle Column: Behavior Chart -->
                    <div class="chart-area">
                        <div class="app-card h-100 p-4">
                            <h3 class="h5 text-primary-app mb-3">สรุปคะแนนพฤติกรรม</h3>
                            <div class="chart-container desktop-chart">
                                <canvas id="studentBehaviorChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column: Recent Activities -->
                    <div class="activities-area">
                        <div class="app-card p-4 h-100">
                            <h3 class="h5 text-primary-app mb-3">กิจกรรมล่าสุด</h3>
                            <div class="activity-list">
                                <div class="activity-item d-flex py-2 border-bottom">
                                    <div class="me-3">
                                        <div class="bg-success rounded-circle activity-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-plus text-white"></i>
                                        </div>
                                    </div>
                                    <div class="activity-content">
                                        <p class="mb-0 fw-medium">ได้รับคะแนน +5 จากกิจกรรมจิตอาสา</p>
                                        <p class="text-muted small mb-0">โดย อ.สมศรี - 10 พ.ค. 2568</p>
                                    </div>
                                </div>
                                <div class="activity-item d-flex py-2 border-bottom">
                                    <div class="me-3">
                                        <div class="bg-danger rounded-circle activity-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-minus text-white"></i>
                                        </div>
                                    </div>
                                    <div class="activity-content">
                                        <p class="mb-0 fw-medium">ถูกหักคะแนน -2 จากการมาสาย</p>
                                        <p class="text-muted small mb-0">โดย อ.ใจดี - 8 พ.ค. 2568</p>
                                    </div>
                                </div>
                                <div class="activity-item d-flex py-2 border-bottom">
                                    <div class="me-3">
                                        <div class="bg-success rounded-circle activity-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-plus text-white"></i>
                                        </div>
                                    </div>
                                    <div class="activity-content">
                                        <p class="mb-0 fw-medium">ได้รับคะแนน +3 จากการช่วยเหลือครู</p>
                                        <p class="text-muted small mb-0">โดย อ.พิมพ์ใจ - 5 พ.ค. 2568</p>
                                    </div>
                                </div>
                                <div class="activity-item d-flex py-2">
                                    <div class="me-3">
                                        <div class="bg-primary-app rounded-circle activity-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-award text-white"></i>
                                        </div>
                                    </div>
                                    <div class="activity-content">
                                        <p class="mb-0 fw-medium">ได้รับเหรียญรางวัล "นักเรียนดีเด่น"</p>
                                        <p class="text-muted small mb-0">ระบบอัตโนมัติ - 1 พ.ค. 2568</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Overview Card -->
                <div class="app-card mt-4 p-3">
                    <h3 class="h5 text-primary-app mb-3">ประวัติการเข้าเรียน</h3>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="attendance-chart-container">
                                <canvas id="attendanceChart" height="200"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="attendance-stats">
                                <div class="attendance-stat-item d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <i class="fas fa-check-circle text-success"></i>
                                        <span>มาเรียน</span>
                                    </div>
                                    <span class="fw-medium">42 วัน (84%)</span>
                                </div>
                                <div class="attendance-stat-item d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <i class="fas fa-clock text-warning"></i>
                                        <span>มาสาย</span>
                                    </div>
                                    <span class="fw-medium">5 วัน (10%)</span>
                                </div>
                                <div class="attendance-stat-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-times-circle text-danger"></i>
                                        <span>ขาดเรียน</span>
                                    </div>
                                    <span class="fw-medium">3 วัน (6%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Teacher Button -->
                <div class="mt-4 mb-4 text-center">
                    <button class="btn btn-primary btn-lg contact-teacher-btn">
                        <i class="fas fa-comment-dots me-2"></i> ติดต่อครูประจำชั้น
                    </button>
                </div>
            </div>
        </div>

        <!-- Bottom Navbar (Mobile Only) -->
        <nav class="bottom-navbar d-lg-none">
            <div class="container">
                <div class="row text-center">
                    <div class="col">
                        <a href="javascript:void(0);" class="nav-link text-primary-app active">
                            <i class="fas fa-home"></i>
                            <span>หน้าหลัก</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="javascript:void(0);" class="nav-link text-muted">
                            <i class="fas fa-history"></i>
                            <span>ประวัติ</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="javascript:void(0);" class="nav-link text-muted position-relative">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-75 translate-middle badge rounded-pill bg-danger">
                                2
                            </span>
                            <span>แจ้งเตือน</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="javascript:void(0);" class="nav-link text-muted">
                            <i class="fas fa-user"></i>
                            <span>โปรไฟล์</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Parent Dashboard JS -->
    <script src="/js/parent-dashboard.js"></script>
</body>
</html>