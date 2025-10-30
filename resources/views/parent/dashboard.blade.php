<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบสารสนเทศจัดการคะแนนพฤติกรรมนักเรียน - หน้าผู้ปกครอง</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- App CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Parent Dashboard Specific CSS -->
    <link href="{{ asset('css/parent.css') }}" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-app: #3b82f6;
            --secondary-app: #64748b;
            --accent-app: #06b6d4;
            --success-app: #10b981;
            --warning-app: #f59e0b;
            --danger-app: #ef4444;
            --light-app: #f8fafc;
            --dark-app: #1e293b;
        }

        body {
            font-family: 'Prompt', sans-serif;
            min-height: 100vh;
            background-color: #f8fafc;
        }

        .btn-primary-app {
            background: var(--primary-app);
            border-color: var(--primary-app);
            color: white;
        }

        .btn-primary-app:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
        }

        .text-primary-app {
            color: var(--primary-app) !important;
        }

        .bg-primary-app {
            background-color: var(--primary-app) !important;
        }

        .bg-secondary-app {
            background-color: var(--secondary-app) !important;
        }
        
        .app-card {
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
        }
        
        .app-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06);
            border-color: rgba(59, 130, 246, 0.2);
        }

        .navbar {
            background: white;
            backdrop-filter: blur(10px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #e2e8f0;
        }

        .student-tab {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 20px;
            margin: 0 6px;
            transition: all 0.2s ease;
            color: #64748b;
        }

        .student-tab:hover {
            border-color: var(--primary-app);
            color: var(--primary-app);
            transform: translateY(-1px);
        }

        .student-tab.active {
            background: var(--primary-app);
            color: white;
            border-color: var(--primary-app);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
        }

        .stats-card {
            background: white;
            color: #1e293b;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-app);
        }

        .stats-value {
            font-size: 3rem !important;
            font-weight: 700;
            color: var(--primary-app);
        }

        .desktop-grid {
            display: grid;
            grid-template-columns: 300px 1fr 350px;
            gap: 24px;
            margin-bottom: 2rem;
        }

        @media (max-width: 991.98px) {
            .desktop-grid {
                display: none;
            }
        }

        .chart-container.desktop-chart {
            height: 400px;
            position: relative;
        }

        /* Force canvas to fill container to avoid 0 width/height rendering issues */
        .chart-container.desktop-chart canvas,
        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        .activity-item {
            transition: all 0.2s ease;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 6px;
        }

        .activity-item:hover {
            background: #f8fafc;
            transform: translateX(2px);
        }

        .notification-item {
            transition: all 0.2s ease;
            border-radius: 8px;
            margin-bottom: 6px;
            padding: 12px;
        }

        .notification-item:hover {
            background: #f8fafc;
            transform: translateX(2px);
        }

        .parent-info-card {
            background: white;
            color: #1e293b;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            position: relative;
        }

        .parent-info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-app);
        }

        .student-summary-card {
            border-radius: 12px;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }

        .student-summary-card:hover {
            border-color: var(--primary-app);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.1);
        }

        /* Student Tabs Enhancement */
        .student-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }

        .student-avatar-small {
            width: 24px;
            height: 24px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.2s ease;
            color: #64748b;
        }

        .student-tab.active .student-avatar-small {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Bottom Navbar Styles */
        .bottom-navbar .nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 4px;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s ease;
            border-radius: 6px;
        }

        .bottom-navbar .nav-link:hover {
            color: var(--primary-app);
            background: #f8fafc;
        }

        .bottom-navbar .nav-link.active {
            color: var(--primary-app);
            background: #f1f5f9;
        }

        .bottom-navbar .nav-link i {
            font-size: 1.1rem;
            margin-bottom: 2px;
        }

        /* Enhanced Cards */
        .student-info-card {
            background: white;
            color: #1e293b;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        /* Progress Bars */
        .progress {
            border-radius: 6px;
            overflow: hidden;
            background: #f1f5f9;
            height: 6px;
        }

        .progress-bar {
            border-radius: 6px;
            transition: width 0.4s ease;
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .student-tabs {
                flex-direction: column;
                gap: 8px;
            }
            
            .student-tab {
                justify-content: center;
                text-align: center;
                padding: 16px 20px;
            }
            
            .desktop-grid-summary {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .app-container {
                padding-bottom: 80px;
            }
        }

        /* Loading Animation */
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(59, 130, 246, 0.1);
            border-left: 4px solid var(--primary-app);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary-app" href="{{ route('parent.dashboard') }}">
                <i class="fas fa-family me-2"></i>
                ระบบจัดการคะแนนพฤติกรรม - ผู้ปกครอง
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('parent.dashboard') }}">
                                <i class="fas fa-home me-1"></i>แดชบอร์ด
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0);" onclick="showAllNotifications(); return false;">
                                <i class="fas fa-bell me-1"></i>การแจ้งเตือน
                                @if($user->notifications && $user->notifications->where('read_at', null)->count() > 0)
                                <span class="badge bg-danger ms-1 nav-notification-badge">{{ $user->notifications->where('read_at', null)->count() }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                {{ $user->first_name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="app-container" style="margin-top: 80px;">
        <!-- Header (Mobile only) -->
        <header class="dashboard-header text-white py-3 d-lg-none" style="background: var(--primary-app);">
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
                            @if($user->profile_image)
                                <img src="{{ asset('storage/'.$user->profile_image) }}" alt="โปรไฟล์ผู้ปกครอง" class="w-100 h-100 object-fit-cover">
                            @else
                                <img src="{{ asset('images/profile.png') }}" alt="โปรไฟล์ผู้ปกครอง" class="w-100 h-100 object-fit-cover">
                            @endif
                        </div>
                    </div>
                    <div>
                        <h2 class="h5 mb-1">สวัสดี {{ $user->name_prefix }}{{ $user->first_name }} {{ $user->last_name }}</h2>
                        <p class="text-muted mb-0">
                            ผู้ปกครองนักเรียน {{ $user->guardian && $user->guardian->students ? $user->guardian->students->count() : 0 }} คน
                        </p>
                    </div>
                    <div class="ms-auto notification-badge">
                        <span class="position-relative">
                            <i class="fas fa-bell fs-4"></i>
                            @if($user->notifications && $user->notifications->where('read_at', null)->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $user->notifications->where('read_at', null)->count() }}
                            </span>
                            @endif
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
                    
                    @if(isset($studentsData) && count($studentsData) > 0)
                        @foreach($studentsData as $index => $student)
                            <button class="student-tab" data-student="student{{ $index+1 }}">
                                <div class="student-avatar-small">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <span>{{ $student['name_prefix'] }}{{ $student['first_name'] }} {{ $student['last_name'] }}</span>
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- All Students Summary (default view) -->
            <div id="all-students-view">
                <div class="section-header d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h5 mb-0">สรุปคะแนนรวมทุกนักเรียน</h3>
                    <div class="dropdown">
                       ภาคเรียนนี้
                    </div>
                </div>

                <!-- Student Cards Summary -->
                <div class="desktop-grid-summary">
                    @if(isset($studentsData) && count($studentsData) > 0)
                        @foreach($studentsData as $index => $student)
                            <div class="app-card student-summary-card p-3 mb-3">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <div class="student-avatar-summary bg-primary-app text-white rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="h6 mb-1">{{ $student['name_prefix'] }}{{ $student['first_name'] }} {{ $student['last_name'] }}</h4>
                                        <p class="text-muted small mb-0">ชั้น {{ $student['class_level'] }}/{{ $student['class_room'] }} เลขที่ {{ $student['student_code'] }}</p>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-{{ $student['score_color'] }} {{ $student['score_color'] == 'warning' ? 'text-dark' : '' }}">{{ $student['current_score'] }} คะแนน</span>
                                    </div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $student['score_color'] }}" role="progressbar" style="width: {{ $student['current_score'] }}%;" aria-valuenow="{{ $student['current_score'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <span class="text-{{ $student['change_color'] }} small fw-medium">
                                        <i class="fas fa-arrow-{{ $student['change_direction'] }}"></i> 
                                        {{ $student['weekly_change'] >= 0 ? '+' : '' }}{{ $student['weekly_change'] }} คะแนน สัปดาห์นี้
                                    </span>
                                    <a href="javascript:void(0);" class="view-details-link" data-student="student{{ $index+1 }}">
                                        ดูรายละเอียด <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="app-card p-4 text-center">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">ยังไม่มีข้อมูลนักเรียน</h5>
                            <p class="text-muted">กรุณาติดต่อเจ้าหน้าที่เพื่อเชื่อมโยงข้อมูลนักเรียน</p>
                        </div>
                    @endif
                </div>

                <!-- Recent Notifications for All Students -->
                <div class="section-header d-flex justify-content-between align-items-center mt-4 mb-3">
                    <h3 class="h5 mb-0">การแจ้งเตือนล่าสุด</h3>
                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary" onclick="showAllNotifications()">
                        ดูทั้งหมด <i class="fas fa-chevron-right"></i>
                    </a>
                </div>

                <div class="app-card p-3">
                    <div class="notification-list">
                        @if(isset($notifications) && $notifications->count() > 0)
                            @foreach($notifications->take(5) as $notification)
                                <div class="notification-item d-flex py-2 {{ !$loop->last ? 'border-bottom' : '' }} {{ !$notification['is_read'] ? 'notification-unread' : '' }}" 
                                     data-notification-id="{{ $notification['id'] }}" 
                                     onclick="markAsRead({{ $notification['id'] }})">
                                    <div class="me-3">
                                        <div class="bg-{{ $notification['type'] }} rounded-circle notification-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="{{ $notification['icon'] }} text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-medium">{{ $notification['title'] }}</h6>
                                        <p class="mb-1">{{ Str::limit($notification['message'], 100) }}</p>
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-clock me-1"></i>{{ $notification['date'] }}
                                        </p>
                                    </div>
                                    <div class="ms-auto align-self-start">
                                        <span class="badge {{ $notification['badge_class'] }}">{{ $notification['badge_text'] }}</span>
                                        @if(!$notification['is_read'])
                                            <div class="notification-dot"></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($notifications->count() > 5)
                                <div class="text-center mt-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="loadMoreNotifications()">
                                        <i class="fas fa-plus me-1"></i> โหลดเพิ่มเติม
                                    </button>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">ยังไม่มีการแจ้งเตือน</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Individual Student View (initially hidden) -->
            <div id="individual-student-view" class="d-none">
                @if(isset($studentsData) && count($studentsData) > 0)
                    @foreach($studentsData as $index => $student)
                        <div id="student{{ $index+1 }}-view" class="student-detail-view d-none">
                            <!-- Student Header Info -->
                            <div class="student-header app-card p-3 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="student-avatar bg-primary-app text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-user-graduate fa-2x"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h3 class="h5 mb-1 student-name">{{ $student['name_prefix'] }}{{ $student['first_name'] }} {{ $student['last_name'] }}</h3>
                                        <p class="text-muted mb-0 student-class">ชั้น {{ $student['class_level'] }}/{{ $student['class_room'] }} เลขที่ {{ $student['student_code'] }}</p>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-{{ $student['score_color'] }} student-points-badge">{{ $student['current_score'] }} คะแนน</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop Grid Layout -->
                            <div class="desktop-grid">
                                <!-- Left Column: Points and Rank -->
                                <div class="metrics-area">
                                    <!-- Points Score Card -->
                                    <div class="app-card stats-card p-3">
                                        <div class="text-center">
                                            <h3 class="h5 text-primary-app mb-3">คะแนนความประพฤติ</h3>
                                            <p class="display-4 fw-bold mb-2 stats-value student-points">{{ $student['current_score'] }}</p>
                                            <span class="badge bg-{{ $student['score_color'] }} student-status">{{ $student['score_status'] }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Class Rank Card -->
                                    <div class="app-card stats-card p-3 mt-3">
                                        <div class="text-center">
                                            <h3 class="h5 text-primary-app mb-3">อันดับในห้อง {{ $student['class_level'] }}/{{ $student['class_room'] }}</h3>
                                            <p class="display-4 fw-bold mb-2 stats-value student-rank">{{ $student['class_rank'] }}<span class="fs-6">/{{ $student['total_students'] }}</span></p>
                                            <span class="badge bg-secondary-app text-dark">
                                                @if($student['class_rank'] == 1)
                                                    อันดับ 1 ในห้อง
                                                @elseif($student['class_rank'] <= 3)
                                                    กลุ่มหัวหน้าในห้อง
                                                @else
                                                    ต้องปรับปรุงในห้อง
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Teacher Contact Card -->
                                    <div class="app-card p-3 mt-3">
                                        <h3 class="h5 text-primary-app mb-3">ครูประจำชั้น</h3>
                                        <div class="d-flex align-items-center">
                                            <div class="teacher-avatar bg-secondary-app rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-user-tie text-dark"></i>
                                            </div>
                                            <div class="ms-3">
                                                <h4 class="h6 mb-1">{{ $student['homeroom_teacher']['name'] }}</h4>
                                                @if($student['homeroom_teacher']['phone'])
                                                <a href="tel:{{ $student['homeroom_teacher']['phone'] }}" class="small text-primary">
                                                    <i class="fas fa-phone-alt"></i> {{ $student['homeroom_teacher']['phone'] }}
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Middle Column: Behavior Chart -->
                                <div class="chart-area">
                                    <div class="app-card h-100 p-4">
                                        <h3 class="h5 text-primary-app mb-3">สรุปคะแนนพฤติกรรม</h3>
                                        <div class="chart-container desktop-chart">
                                            <canvas id="studentBehaviorChart{{ $index+1 }}"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Column: Recent Activities -->
                                <div class="activities-area">
                                    <div class="app-card p-4 h-100">
                                        <h3 class="h5 text-primary-app mb-3">ประวัติพฤติกรรมล่าสุด</h3>
                                        <div class="activity-list">
                                            @if(isset($student['recent_activities']) && count($student['recent_activities']) > 0)
                                                @foreach($student['recent_activities'] as $activity)
                                                    <div class="activity-item d-flex py-2">
                                                        <div class="me-3">
                                                            <div class="bg-{{ $activity['color'] }} rounded-circle activity-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <i class="{{ $activity['icon'] }} text-white"></i>
                                                            </div>
                                                        </div>
                                                        <div class="activity-content">
                                                            <p class="mb-0 fw-medium">{{ $activity['message'] }}</p>
                                                            <p class="text-muted small mb-0">โดย {{ $activity['teacher'] }} - {{ $activity['date'] }}</p>
                                                            @if(isset($activity['points']))
                                                                <span class="badge bg-{{ $activity['points'] > 0 ? 'success' : 'danger' }} mt-1">
                                                                    {{ $activity['points'] > 0 ? '+' : '' }}{{ $activity['points'] }} คะแนน
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-center py-4">
                                                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted mb-0">ยังไม่มีการบันทึกพฤติกรรม</p>
                                                </div>
                                            @endif
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
                                        <p class="display-4 fw-bold mb-2 stats-value">{{ $student['current_score'] }}</p>
                                        <span class="badge bg-{{ $student['score_color'] }}">{{ $student['score_status'] }}</span>
                                    </div>
                                </div>
                                
                                <!-- Class Rank Card -->
                                <div class="app-card stats-card p-3 mb-4">
                                    <div class="text-center">
                                        <h3 class="h5 text-primary-app mb-3">อันดับในห้อง {{ $student['class_level'] }}/{{ $student['class_room'] }}</h3>
                                        <p class="display-4 fw-bold mb-2 stats-value">{{ $student['class_rank'] }}<span class="fs-6">/{{ $student['total_students'] }}</span></p>
                                        <span class="badge bg-secondary-app text-dark">
                                            @if($student['class_rank'] == 1)
                                                อันดับ 1 ในห้อง
                                            @elseif($student['class_rank'] <= 3)
                                                กลุ่มหัวหน้าในห้อง
                                            @else
                                                ต้องปรับปรุงในห้อง
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Behavior Chart -->
                                <div class="app-card mb-4 p-4">
                                    <h3 class="h5 text-primary-app mb-3">สรุปคะแนนพฤติกรรม</h3>
                                    <div class="chart-container" style="height: 250px;">
                                        <canvas id="studentBehaviorChartMobile{{ $index+1 }}"></canvas>
                                    </div>
                                </div>
                                
                                <!-- Recent Activities -->
                                <div class="app-card p-4">
                                    <h3 class="h5 text-primary-app mb-3">ประวัติพฤติกรรมล่าสุด</h3>
                                    <div class="activity-list mobile-activities">
                                        @if(isset($student['recent_activities']) && count($student['recent_activities']) > 0)
                                            @foreach($student['recent_activities'] as $activity)
                                                <div class="activity-item d-flex py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                    <div class="me-3">
                                                        <div class="bg-{{ $activity['color'] }} rounded-circle activity-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="{{ $activity['icon'] }} text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="activity-content">
                                                        <p class="mb-0 fw-medium">{{ $activity['message'] }}</p>
                                                        <p class="text-muted small mb-0">โดย {{ $activity['teacher'] }} - {{ $activity['date'] }}</p>
                                                        @if(isset($activity['points']))
                                                            <span class="badge bg-{{ $activity['points'] > 0 ? 'success' : 'danger' }} mt-1">
                                                                {{ $activity['points'] > 0 ? '+' : '' }}{{ $activity['points'] }} คะแนน
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle mb-2 fa-2x"></i>
                                                <p>ยังไม่มีการบันทึกพฤติกรรมในระบบ</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>

        <!-- Bottom Navbar (Mobile Only) -->
        <nav class="bottom-navbar d-lg-none" style="background: white; position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000; box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.1); border-top: 1px solid #e2e8f0;">
            <div class="container">
                <div class="row text-center py-2">
                    <div class="col">
                        <a href="{{ route('parent.dashboard') }}" class="nav-link text-primary-app active">
                            <i class="fas fa-home"></i>
                            <span class="small">หน้าหลัก</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="javascript:void(0);" class="nav-link text-muted" onclick="showAllNotifications(); return false;">
                            <i class="fas fa-bell"></i>
                            <span class="small">การแจ้งเตือน</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="#" class="nav-link text-muted">
                            <i class="fas fa-user-cog"></i>
                            <span class="small">ตั้งค่า</span>
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
    <!-- Parent Notifications JS -->
    <script src="/js/parent-notifications.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ข้อมูลนักเรียนจาก PHP
            const studentsData = @json($studentsData ?? []);
            
            // Global chart management - ใช้ Map แทน Object
            window.parentDashboardCharts = new Map();
            
            // DOM Elements
            const studentTabs = document.querySelectorAll('.student-tab');
            const allStudentsView = document.getElementById('all-students-view');
            const individualStudentView = document.getElementById('individual-student-view');
            const viewDetailsLinks = document.querySelectorAll('.view-details-link');

            // Utility Functions
            function showElement(element) {
                if (element) {
                    element.classList.remove('d-none');
                }
            }

            function hideElement(element) {
                if (element) {
                    element.classList.add('d-none');
                }
            }

            function setActiveTab(targetTab) {
                studentTabs.forEach(tab => tab.classList.remove('active'));
                if (targetTab) {
                    targetTab.classList.add('active');
                }
            }

            // Chart Management - ปรับปรุงใหม่
            function destroyChart(chartId) {
                // ทำลายจาก Map
                if (window.parentDashboardCharts.has(chartId)) {
                    try {
                        const chart = window.parentDashboardCharts.get(chartId);
                        chart.destroy();
                        window.parentDashboardCharts.delete(chartId);
                    } catch (error) {
                        console.warn(`Error destroying chart ${chartId}:`, error);
                        window.parentDashboardCharts.delete(chartId);
                    }
                }
                
                // ทำลายจาก Chart.js registry
                const canvas = document.getElementById(chartId);
                if (canvas && typeof Chart !== 'undefined' && typeof Chart.getChart === 'function') {
                    const existingChart = Chart.getChart(canvas);
                    if (existingChart) {
                        try {
                            existingChart.destroy();
                        } catch (error) {
                            console.warn(`Error destroying chart from registry:`, error);
                        }
                    }
                }
            }

            function destroyAllCharts() {
                // ทำลายทุก chart ใน Map
                window.parentDashboardCharts.forEach((chart, chartId) => {
                    destroyChart(chartId);
                });
                window.parentDashboardCharts.clear();

                // ตรวจสอบ instance ที่อาจยังเหลืออยู่ใน Chart.js registry (สำหรับหลายเวอร์ชัน)
                if (typeof Chart !== 'undefined' && typeof Chart.getChart === 'function') {
                    const canvases = document.querySelectorAll('canvas[id^="studentBehaviorChart"]');
                    canvases.forEach(canvas => {
                        const existingChart = Chart.getChart(canvas);
                        if (existingChart) {
                            existingChart.destroy();
                        }
                    });
                }
            }

            // Main View Functions
            function showAllStudentsView() {
                hideElement(individualStudentView);
                showElement(allStudentsView);
                
                // Hide all individual views
                document.querySelectorAll('.student-detail-view').forEach(view => {
                    hideElement(view);
                });
                
                // Set "All" tab as active
                const allTab = document.querySelector('[data-student="all"]');
                setActiveTab(allTab);
                
                // Destroy all charts
                destroyAllCharts();
            }

            function showStudentDetailView(studentId) {
                hideElement(allStudentsView);
                showElement(individualStudentView);
                
                // Hide all individual views first
                document.querySelectorAll('.student-detail-view').forEach(view => {
                    hideElement(view);
                });
                
                // Show target view
                const targetView = document.getElementById(`${studentId}-view`);
                if (targetView) {
                    showElement(targetView);
                    
                    // Load student details immediately
                    const studentIndex = parseInt(studentId.replace('student', '')) - 1;
                    if (studentsData[studentIndex]) {
                        loadStudentDetails(studentsData[studentIndex], studentIndex + 1);
                        console.log(`Loaded details for ${studentsData}`);
                    }
                } else {
                    console.error('Target view not found:', `${studentId}-view`);
                }
            }

            function loadStudentDetails(student, index) {
                // Update student data in view
                updateStudentViewData(student, index);
                
                // Load chart immediately
                loadStudentScoreChart(student.id, index);
            }

            function updateStudentViewData(student, index) {
                const viewElement = document.getElementById(`student${index}-view`);
                if (!viewElement) {
                    console.error('View element not found:', `student${index}-view`);
                    return;
                }
                
                // Update student name
                const nameElement = viewElement.querySelector('.student-name');
                if (nameElement) {
                    nameElement.textContent = `${student.name_prefix}${student.first_name} ${student.last_name}`;
                }
                
                // Update class info
                const classElement = viewElement.querySelector('.student-class');
                if (classElement) {
                    classElement.textContent = `ชั้น ${student.class_level}/${student.class_room} เลขที่ ${student.student_code}`;
                }
                
                // Update points
                const pointsElement = viewElement.querySelector('.student-points');
                if (pointsElement) {
                    pointsElement.textContent = student.current_score;
                }
                
                // Update badge
                const badgeElement = viewElement.querySelector('.student-points-badge');
                if (badgeElement) {
                    badgeElement.className = `badge bg-${student.score_color}`;
                    badgeElement.textContent = `${student.current_score} คะแนน`;
                }
                
                // Update status
                const statusElement = viewElement.querySelector('.student-status');
                if (statusElement) {
                    statusElement.className = `badge bg-${student.score_color}`;
                    statusElement.textContent = student.score_status;
                }
                
                // Update rank
                const rankElement = viewElement.querySelector('.student-rank');
                if (rankElement) {
                    rankElement.innerHTML = `${student.class_rank}<span class="fs-6">/${student.total_students}</span>`;
                }
            }

            function loadStudentScoreChart(studentId, index) {
                const chartId = `studentBehaviorChart${index}`;
                const canvas = document.getElementById(chartId);

                if (!canvas) {
                    console.error('[Chart] Canvas not found:', chartId);
                    return;
                }

                // ทำลาย chart เก่า (ถ้ามี)
                destroyChart(chartId);

                // ข้อมูล fallback (สามารถแทนที่ด้วยข้อมูลจริงภายหลัง)
                const fallbackData = {
                    labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.'],
                    data: [95, 92, 88, 90, 85, studentsData[index - 1]?.current_score || 100]
                };

                console.log('[Chart] loadStudentScoreChart()', {studentId, index, chartId, fallbackData});

                // สร้างกราฟหลัก (เดสก์ท็อป)
                createChart(canvas, chartId, fallbackData, index);

                // สร้างกราฟสำหรับมือถือถ้ามี
                const mobileCanvasId = `studentBehaviorChartMobile${index}`;
                const mobileCanvas = document.getElementById(mobileCanvasId);
                if (mobileCanvas) {
                    destroyChart(mobileCanvasId); // ทำลายถ้ามี
                    createChart(mobileCanvas, mobileCanvasId, fallbackData, index);
                }
            }

            function createChart(canvas, chartId, data, index) {
                try {
                    console.log('[Chart] createChart()', chartId, data);
                    // ตรวจสอบ canvas
                    if (!canvas) {
                        throw new Error('Canvas element not found');
                    }
                    
                    // ทำให้ canvas มีขนาดชัดเจนก่อน (บางครั้ง display:none ก่อนหน้า จะทำให้ width/height เป็น 0)
                    if (canvas.width === 0 || canvas.height === 0) {
                        canvas.width = canvas.parentElement.clientWidth || 600;
                        canvas.height = canvas.parentElement.clientHeight || 400;
                    }

                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        throw new Error('Cannot get canvas context');
                    }

                    // สร้าง chart ใหม่ (ลบ animation ทั้งหมด)
                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels || ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.'],
                            datasets: [{
                                label: 'คะแนนพฤติกรรม',
                                data: data.data || [95, 92, 88, 90, 85, 100],
                                borderColor: '#1020AD',
                                backgroundColor: 'rgba(16, 32, 173, 0.1)',
                                tension: 0,
                                fill: true,
                                pointBackgroundColor: '#1020AD',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            resizeDelay: 0,
                            animation: false, // ปิด animation
                            transitions: {
                                active: {
                                    animation: {
                                        duration: 0
                                }
                            }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    animation: false, // ปิด tooltip animation
                                    callbacks: {
                                        label: function(context) {
                                            return `คะแนน: ${context.parsed.y} คะแนน`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    min: 0,
                                    max: 100,
                                    ticks: {
                                        callback: function(value) {
                                            return value + ' คะแนน';
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            onHover: function(event, activeElements) {
                                // ปิด hover animation
                                event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                            }
                        }
                    });
                    
                    // เก็บ chart instance ใน Map
                    window.parentDashboardCharts.set(chartId, chart);
                    console.log('[Chart] created', chartId);
                    
                } catch (error) {
                    console.error(`Error creating chart ${chartId}:`, error);
                    showChartError(canvas.parentElement);
                }
            }

            function showChartError(container) {
                container.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <p class="mb-2">ไม่สามารถโหลดกราฟได้</p>
                        <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                            <i class="fas fa-redo me-1"></i> ลองใหม่
                        </button>
                    </div>
                `;
            }

            // Event Listeners
            studentTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const studentId = this.dataset.student;
                    
                    setActiveTab(this);
                    
                    if (studentId === 'all') {
                        showAllStudentsView();
                    } else {
                        showStudentDetailView(studentId);
                    }
                });
            });

            viewDetailsLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const studentId = this.dataset.student;
                    
                    const targetTab = document.querySelector(`[data-student="${studentId}"]`);
                    setActiveTab(targetTab);
                    
                    showStudentDetailView(studentId);
                });
            });

            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                destroyAllCharts();
            });
            
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    destroyAllCharts();
                }
            });

            // Initialize: ถ้ากดเข้า tab นักเรียนคนแรกอัตโนมัติเพื่อให้เห็นกราฟทันที
            console.log('[Init] studentsData:', studentsData);
            if (studentsData.length > 0) {
                const firstTab = document.querySelector('[data-student="student1"]');
                if (firstTab) {
                    setActiveTab(firstTab);
                    showStudentDetailView('student1');
                } else {
                    showAllStudentsView();
                }
            } else {
                showAllStudentsView();
            }
        });
    </script>
</body>
</html>