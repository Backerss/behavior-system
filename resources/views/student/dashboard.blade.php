<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบสารสนเทศจัดการคะแนนนักเรียน - หน้านักเรียน</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- App CSS -->
    <link href="/css/app.css" rel="stylesheet">
    <!-- Student Dashboard Specific CSS -->
    <link href="/css/student.css" rel="stylesheet">
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
                        <i class="fas fa-trophy"></i>
                        <span>รางวัล</span>
                    </a>
                    <a href="javascript:void(0);" class="desktop-nav-link">
                        <i class="fas fa-user"></i>
                        <span>โปรไฟล์</span>
                    </a>
                    <a href="javascript:void(0);" onclick="document.getElementById('logout-form').submit();" class="desktop-nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>ออกจากระบบ</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
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
            <!-- Student Info Card -->
            <div class="app-card student-info-card mb-4 p-3">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="student-avatar bg-primary-app text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="h5 mb-1">สวัสดี {{ $user->name_prefix }}{{ $user->first_name }} {{ $user->last_name }}</h2>
                        <p class="text-muted mb-0">
                            @if($user->student)
                                ชั้น {{ $user->student->class ? $user->student->class->level.$user->student->class->room_number : 'ไม่ระบุชั้นเรียน' }}
                                รหัสนักเรียน {{ $user->student->student_code }}
                            @else
                                ข้อมูลนักเรียนไม่สมบูรณ์
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Desktop Layout -->
            <div class="desktop-grid">
                <!-- Left Column: Points and Rank -->
                <div class="metrics-area">
                    <!-- Points Score Card -->
                    <div class="app-card stats-card p-3">
                        <div class="text-center">
                            <h3 class="h5 text-primary-app mb-3">คะแนนความประพฤติ</h3>
                            <p class="display-4 fw-bold mb-2 stats-value" id="behavior-points">
                                {{ $user->student ? $user->student->current_score : 0 }}
                            </p>
                            <span class="badge bg-success">ดีมาก</span>
                        </div>
                    </div>
                    
                    <!-- Class Rank Card -->
                    <div class="app-card stats-card p-3 mt-4">
                        <div class="text-center">
                            <h3 class="h5 text-primary-app mb-3">อันดับในห้องเรียน</h3>
                            <p class="display-4 fw-bold mb-2 stats-value" id="class-rank">5<span class="fs-6">/30</span></p>
                            <span class="badge bg-secondary-app text-dark">กลุ่มหัวหน้า</span>
                        </div>
                    </div>
                </div>
                
                <!-- Middle Column: Behavior Chart -->
                <div class="chart-area">
                    <div class="app-card h-100 p-4">
                        <h3 class="h5 text-primary-app mb-3">สรุปคะแนนพฤติกรรม</h3>
                        <div class="chart-container desktop-chart">
                            <canvas id="behaviorChart"></canvas>
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

            <!-- Mobile Layout (Row-based) -->
            <div class="mobile-grid d-lg-none">
                <!-- Points Score Card -->
                <div class="app-card stats-card p-3 mb-4">
                    <div class="text-center">
                        <h3 class="h5 text-primary-app mb-3">คะแนนความประพฤติ</h3>
                        <p class="display-4 fw-bold mb-2 stats-value">100</p>
                        <span class="badge bg-success">ดีมาก</span>
                    </div>
                </div>
                
                <!-- Class Rank Card -->
                <div class="app-card stats-card p-3 mb-4">
                    <div class="text-center">
                        <h3 class="h5 text-primary-app mb-3">อันดับในห้องเรียน</h3>
                        <p class="display-4 fw-bold mb-2 stats-value">5<span class="fs-6">/30</span></p>
                        <span class="badge bg-secondary-app text-dark">กลุ่มหัวหน้า</span>
                    </div>
                </div>
                
                <!-- Behavior Chart -->
                <div class="app-card mb-4 p-4">
                    <h3 class="h5 text-primary-app mb-3">สรุปคะแนนพฤติกรรม</h3>
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="behaviorChartMobile"></canvas>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="app-card p-4">
                    <h3 class="h5 text-primary-app mb-3">กิจกรรมล่าสุด</h3>
                    <div class="activity-list mobile-activities">
                        <!-- Activities cloned by JavaScript -->
                    </div>
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
                        <a href="javascript:void(0);" class="nav-link text-muted">
                            <i class="fas fa-trophy"></i>
                            <span>รางวัล</span>
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
    <!-- Student Dashboard JS -->
    <script src="/js/student-dashboard.js"></script>
</body>
</html>