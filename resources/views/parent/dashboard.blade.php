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
        <nav class="desktop-navbar d-none d-lg-flex jus">
            <div class="desktop-navbar-container">
                <div class="desktop-navbar-brand">
                    <i class="fas fa-graduation-cap"></i>
                    <span>ระบบจัดการคะแนนพฤติกรรม</span>
                </div>
                <div class="desktop-navbar-menu">
                    <!-- เพิ่มปุ่มออกจากระบบในเมนู -->
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
                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary">
                        ดูทั้งหมด <i class="fas fa-chevron-right"></i>
                    </a>
                </div>

                <div class="app-card p-3">
                    <div class="notification-list">
                        @if(isset($notifications) && $notifications->count() > 0)
                            @foreach($notifications->take(5) as $notification)
                                <div class="notification-item d-flex py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="me-3">
                                        <div class="bg-{{ $notification['type'] }} rounded-circle notification-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="{{ $notification['icon'] }} text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-medium">{{ $notification['message'] }}</p>
                                        <p class="text-muted small mb-0">{{ $notification['date'] }}</p>
                                    </div>
                                    <div class="ms-auto align-self-center">
                                        <span class="badge {{ $notification['badge_class'] }}">{{ $notification['badge_text'] }}</span>
                                    </div>
                                </div>
                            @endforeach
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
                                            <h3 class="h5 text-primary-app mb-3">อันดับในห้องเรียน</h3>
                                            <p class="display-4 fw-bold mb-2 stats-value student-rank">{{ $student['class_rank'] }}<span class="fs-6">/{{ $student['total_students'] }}</span></p>
                                            <span class="badge bg-secondary-app text-dark">
                                                @if($student['class_rank'] == 1)
                                                    อันดับ 1
                                                @elseif($student['class_rank'] <= 3)
                                                    กลุ่มหัวหน้า
                                                @else
                                                    ต้องปรับปรุง
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
                                        <div class="chart-container">
                                            <canvas id="studentBehaviorChart{{ $index+1 }}"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Column: Recent Activities -->
                                <div class="activities-area">
                                    <div class="app-card p-4 h-100">
                                        <h3 class="h5 text-primary-app mb-3">กิจกรรมล่าสุด</h3>
                                        <div class="activity-list">
                                            @if(isset($student['recent_activities']) && count($student['recent_activities']) > 0)
                                                @foreach($student['recent_activities'] as $activity)
                                                    <div class="activity-item d-flex py-2">
                                                        <div class="me-3">
                                                            <div class="bg-{{ $activity['color'] }} rounded-circle activity-icon d-flex align-items-center justify-content-center">
                                                                <i class="{{ $activity['icon'] }} text-white"></i>
                                                            </div>
                                                        </div>
                                                        <div class="activity-content">
                                                            <p class="mb-0 fw-medium">{{ $activity['message'] }}</p>
                                                            <p class="text-muted small mb-0">โดย {{ $activity['teacher'] }} - {{ $activity['date'] }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-center py-4">
                                                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted mb-0">ยังไม่มีกิจกรรมที่บันทึก</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Parent Dashboard JS -->
    <script src="/js/parent-dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ข้อมูลนักเรียนจาก PHP
            const studentsData = @json($studentsData ?? []);
            console.log('Students Data:', studentsData.length, 'students found');
            
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
                        console.log(`Chart ${chartId} destroyed from Map`);
                    } catch (error) {
                        console.warn(`Error destroying chart ${chartId}:`, error);
                        window.parentDashboardCharts.delete(chartId);
                    }
                }
                
                // ทำลายจาก Chart.js registry
                const canvas = document.getElementById(chartId);
                if (canvas) {
                    const existingChart = Chart.getChart(canvas);
                    if (existingChart) {
                        try {
                            existingChart.destroy();
                            console.log(`Chart ${chartId} destroyed from Registry`);
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
                
                // ทำลายทุก chart ใน Chart.js registry
                Chart.helpers.each(Chart.instances, function(instance) {
                    instance.destroy();
                });
            }

            // Main View Functions
            function showAllStudentsView() {
                console.log('Showing all students view');
                
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
                console.log('Showing student detail view:', studentId);
                
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
                    }
                } else {
                    console.error('Target view not found:', `${studentId}-view`);
                }
            }

            function loadStudentDetails(student, index) {
                console.log('Loading details for student:', student.first_name, 'Index:', index);
                
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
                    console.error('Canvas not found:', chartId);
                    return;
                }

                console.log('Loading chart for student ID:', studentId, 'Chart ID:', chartId);
                
                // ทำลาย chart เก่าอย่างสมบูรณ์
                destroyChart(chartId);
                
                // รอให้ Chart.js ทำความสะอาดเสร็จ
                setTimeout(() => {
                    // ใช้ข้อมูล fallback ทันที (ไม่ fetch จาก API)
                    const fallbackData = {
                        labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.'],
                        data: [95, 92, 88, 90, 85, studentsData[index - 1]?.current_score || 100]
                    };
                    
                    createChart(canvas, chartId, fallbackData, index);
                }, 100);
            }

            function createChart(canvas, chartId, data, index) {
                try {
                    // ตรวจสอบ canvas
                    if (!canvas) {
                        throw new Error('Canvas element not found');
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
                    console.log(`Chart created successfully: ${chartId}`);
                    
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
                    console.log('Tab clicked:', studentId);
                    
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
                    console.log('View details clicked:', studentId);
                    
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

            // Initialize
            console.log('Initializing parent dashboard...');
            showAllStudentsView();
            console.log('Parent dashboard initialized successfully');
        });
    </script>
</body>
</html>