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

    <style>
        /* Hide console errors from broken images */
        img {
            image-rendering: auto;
        }

        img[src=""] {
            display: none;
        }

        /* Enhanced UI Styles for Status Sync */
        /* Minimal adjustments */
        .btn-group .dropdown-toggle {
            font-weight: 500;
            transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
            box-shadow: none !important;
        }
        .btn-group .dropdown-toggle:hover,
        .btn-group .dropdown-toggle:focus {
            background-color: #198754 !important; /* keep success tone */
            color: #fff !important;
            box-shadow: none !important;
            transform: none !important;
        }
        .dropdown-menu {
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 4px 12px rgba(0,0,0,.06) !important;
            border-radius: 10px !important;
            padding: .25rem 0 !important;
            margin-top: 6px !important;
        }
        .dropdown-item {
            padding: .85rem 1.1rem !important;
            display: block;
            transition: background-color .12s ease;
        }
        .dropdown-item:hover {
            background: #f5f7fa !important;
            color: #212529 !important;
        }
        .dropdown-item:hover .text-muted { color: #6c757d !important; }
        .dropdown-item:hover .bg-primary { background: #0d6efd !important; }
        .toast { border-radius: 10px !important; box-shadow: 0 4px 18px rgba(0,0,0,.12) !important; backdrop-filter: none; }
        .toast-body { padding: .9rem 1rem; }
        /* Remove intensive loading pulse for minimal style */
        .btn[data-loading="1"] { position: relative; }

        /* Google Sheets Modal Styles */
        .modal-xl {
            max-width: 95%;
        }

        @media (max-width: 768px) {
            .modal-xl {
                margin: 10px;
                max-width: calc(100% - 20px);
            }
        }

        .table-sm th,
        .table-sm td {
            padding: 0.5rem;
            font-size: 0.875rem;
        }

        /* Sheet Selection Cards */
        .sheet-card {
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
            height: 100%;
        }

        .sheet-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .sheet-card.active {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .sheet-card.active .card-title {
            color: #0d6efd;
        }

        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Sidebar/Drawer Styles */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .sidebar-content {
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            max-width: 500px;
            height: 100%;
            background-color: white;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-overlay.show .sidebar-content {
            transform: translateX(0);
        }

        .sidebar-content.sidebar-detail {
            max-width: 400px;
        }

        .sidebar-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .sidebar-title {
            margin: 0;
            font-size: 1.1rem;
            color: #495057;
            flex: 1;
        }

        .sidebar-actions {
            display: flex;
            align-items: center;
        }

        .btn-close-sidebar,
        .btn-back-sidebar {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #6c757d;
            cursor: pointer;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .btn-close-sidebar:hover,
        .btn-back-sidebar:hover {
            background-color: #e9ecef;
            color: #495057;
        }

        .sidebar-body {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }

        .filter-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
        }

        .filter-title {
            color: #495057;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        /* Student Cards for Sidebar */
        .student-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background-color: white;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .student-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .student-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .student-card-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .student-card-info {
            flex: 1;
            min-width: 0;
        }

        .student-card-name {
            font-weight: 600;
            margin: 0 0 0.25rem 0;
            font-size: 0.95rem;
            color: #212529;
        }

        .student-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .student-card-meta .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        .student-card-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }

        .student-info-card {
            background: #1020AD;
            color: white;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .student-info-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .student-avatar {
            flex-shrink: 0;
        }

        .student-avatar img {
            border: 3px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .student-details {
            flex: 1;
            min-width: 0;
        }

        .student-name {
            color: white;
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }

        .student-meta {
            margin-bottom: 0.5rem;
        }

        .student-meta .badge {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
        }

        .student-status .badge {
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
        }

        /* New Student Meta Grid Layout */
        .student-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .meta-label {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .meta-label i {
            width: 12px;
            text-align: center;
        }

        .meta-value {
            font-size: 0.8rem;
            color: white;
            font-weight: 600;
        }

        .meta-value .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }

        /* Badge colors override for student info */
        .student-info-card .badge.bg-primary {
            background-color: rgba(13, 110, 253, 0.9) !important;
            color: white !important;
        }

        .student-info-card .badge.bg-info {
            background-color: rgba(13, 202, 240, 0.9) !important;
            color: white !important;
        }

        .student-info-card .badge.bg-success {
            background-color: rgba(25, 135, 84, 0.9) !important;
            color: white !important;
        }

        .student-info-card .badge.bg-warning {
            background-color: rgba(255, 193, 7, 0.9) !important;
            color: #212529 !important;
        }

        .student-info-card .badge.bg-danger {
            background-color: rgba(220, 53, 69, 0.9) !important;
            color: white !important;
        }

        .student-info-card .badge.bg-secondary {
            background-color: rgba(108, 117, 125, 0.9) !important;
            color: white !important;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .student-meta-grid {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
        }

        .behavior-stats {
            display: flex;
            gap: 0.5rem;
        }

        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 0.75rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex: 1;
        }

        .stat-card.stat-positive {
            border-left: 4px solid #28a745;
        }

        .stat-card.stat-negative {
            border-left: 4px solid #dc3545;
        }

        .stat-card.stat-violations {
            border-left: 4px solid #dc3545;
        }

        .stat-card.stat-score {
            border-left: 4px solid #ffc107;
        }

        .stat-card.stat-average {
            border-left: 4px solid #17a2b8;
        }

        .stat-icon {
            font-size: 1.2rem;
            margin-bottom: 0.25rem;
        }

        .stat-positive .stat-icon {
            color: #28a745;
        }

        .stat-negative .stat-icon {
            color: #dc3545;
        }

        .stat-violations .stat-icon {
            color: #dc3545;
        }

        .stat-score .stat-icon {
            color: #ffc107;
        }

        .stat-average .stat-icon {
            color: #17a2b8;
        }

        .stat-number {
            display: block;
            font-size: 1.2rem;
            font-weight: 600;
            color: #495057;
        }

        .stat-label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.2rem;
        }
        }

        .stat-negative .stat-icon {
            color: #dc3545;
        }

        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: bold;
            line-height: 1;
        }

        .stat-label {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .section-title {
            color: #495057;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .history-item {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background-color: white;
        }

        .history-item.positive {
            border-left: 4px solid #28a745;
            background-color: #f8fff9;
        }

        .history-item.negative {
            border-left: 4px solid #dc3545;
            background-color: #fff8f8;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .history-date {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .history-points {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            background-color: rgba(0,0,0,0.1);
        }

        .history-item.positive .history-points {
            color: #28a745;
            background-color: rgba(40, 167, 69, 0.1);
        }

        .history-item.negative .history-points {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .history-violation {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #495057;
        }

        .history-description {
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 0.5rem;
            font-style: italic;
        }

        .history-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: #6c757d;
        }

        .history-teacher {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .history-year {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar-content {
                max-width: 100%;
                width: 100%;
            }

            .sidebar-content.sidebar-detail {
                max-width: 100%;
            }
        }

        @media (max-width: 576px) {
            .sidebar-body {
                padding: 1rem;
            }

            .filter-section {
                padding: 0.75rem;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar (Desktop) -->
        <div class="sidebar d-none d-lg-flex">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียน" class="logo"
                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iIzE2M0FENyIvPgo8cGF0aCBkPSJNMjAgMTBMMjUgMTcuNU0yMCAxMEwxNSAxNy41TTIwIDEwVjI1TTIwIDI1SDI1VjMwSDIwVjI1Wk0yMCAyNUgxNVYzMEgyMFYyNVoiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo='">
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
                <a href="#" onclick="openArchivedStudentsSidebar()" class="menu-item">
                    <i class="fas fa-archive"></i>
                    <span>ประวัติการเก็บข้อมูล</span>
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#profileModal" class="menu-item">
                    <i class="fas fa-user-circle"></i>
                    <span>โปรไฟล์</span>
                </a>
                <a href="javascript:void(0);" onclick="document.getElementById('logout-form').submit();"
                    class="menu-item mt-auto">
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
                        <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียน" class="logo"
                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iIzE2M0FENyIvPgo8cGF0aCBkPSJNMjAgMTBMMjUgMTcuNU0yMCAxMEwxNSAxNy41TTIwIDEwVjI1TTIwIDI1SDI1VjMwSDIwVjI1Wk0yMCAyNUgxNVYzMEgyMFYyNVoiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo='">
                        <h5 class="mb-0 ms-2">ระบบจัดการพฤติกรรม</h5>
                    </div>
                    <div class="dropdown">
                        <img src="https://ui-avatars.com/api/?name=ครูใจดี&background=1020AD&color=fff"
                            class="rounded-circle" width="40" height="40" data-bs-toggle="dropdown">
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                data-bs-target="#profileModal">โปรไฟล์</a>
                            <a class="dropdown-item" href="javascript:void(0);"
                                onclick="document.getElementById('logout-form').submit();">ออกจากระบบ</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Academic Year Info & Notifications -->
                    <!-- Welcome Section -->
                    <div class="welcome-section d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="fw-bold">สวัสดี, {{ $user->users_name_prefix }}{{ $user->users_first_name }}
                                {{ $user->users_last_name }}
                            </h1>
                            <p class="text-muted">วันนี้คือวันที่ <span class="current-date">{{ date('d F Y') }}</span>
                            </p>
                        </div>
                        <div class="d-none d-md-flex align-items-center">
                            <button class="btn btn-primary-app me-3 shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#newViolationModal">
                                <i class="fas fa-plus me-2"></i> บันทึกพฤติกรรม
                            </button>
                            @if(auth()->user()->users_role === 'admin')
                            <div class="btn-group">
                                <button class="btn btn-secondary dropdown-toggle shadow-sm" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false"
                                        style="border-radius: 8px; background-color:#6c757d !important; border-color:#6c757d !important; color:#fff !important;">
                                    <i class="fas fa-sync-alt me-2"></i>เครื่องมือผู้ดูแล
                                </button>
                                <ul class="dropdown-menu shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                                    <li>
                                        <a class="dropdown-item py-3 px-4" href="#" id="btnSyncStudentStatus">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-gradient rounded-circle p-2 me-3">
                                                    <i class="fas fa-rotate text-white"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">ซิงค์สถานะนักเรียน</div>
                                                    <small class="text-muted">อัปเดตจาก Google Sheet</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-2"></li>
                                    <li>
                                        <a class="dropdown-item py-3 px-4" href="#" data-bs-toggle="modal" data-bs-target="#googleSheetsImportModal">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success bg-gradient rounded-circle p-2 me-3">
                                                    <i class="fab fa-google-drive text-white"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">นำเข้าข้อมูล</div>
                                                    <small class="text-muted">จาก Google Sheets</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </div>
                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const btn = document.getElementById('btnSyncStudentStatus');
                                    
                                    if (!btn) return;
                                    
                                    btn.addEventListener('click', function(e){
                                        e.preventDefault();
                                        if (this.dataset.loading === '1') return;
                                        
                                        // Show loading state with beautiful animation
                                        this.dataset.loading = '1';
                                        const originalContent = this.querySelector('div').innerHTML;
                                        this.querySelector('div').innerHTML = `
                                            <div class="d-flex align-items-center">
                                                <div class="bg-warning bg-gradient rounded-circle p-2 me-3">
                                                    <i class="fas fa-spinner fa-spin text-white"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">กำลังซิงค์...</div>
                                                    <small class="text-muted">กรุณารอสักครู่</small>
                                                </div>
                                            </div>
                                        `;
                                        
                                        Swal.fire({
                                            title: 'กำลังซิงค์สถานะ...',
                                            html: '<div class="py-2 text-muted small">ดึงข้อมูลจาก Google Sheet และตรวจสอบความแตกต่าง</div>',
                                            allowOutsideClick: false,
                                            didOpen: () => { Swal.showLoading(); }
                                        });

                                        fetch('/api/students/status-sync', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                                'Accept': 'application/json'
                                            }
                                        }).then(r=>r.json()).then(data=>{
                                            if (data.success) {
                                                const s = data.summary;
                                                const details = data.details || {};
                                                const updatedRows = (details.updated_details || []).map((u,i)=>`<tr>
                                                    <td class='text-center'>${i+1}</td>
                                                    <td><code>${u.code}</code></td>
                                                    <td>${u.name || '-'}</td>
                                                    <td><span class='badge bg-secondary'>${u.old ?? '-'}</span></td>
                                                    <td><span class='badge bg-success'>${u.new}</span></td>
                                                </tr>`).join('');
                                                const updatedTable = updatedRows ? `
                                                    <div class='mt-3 text-start'>
                                                        <h6 class='fw-semibold mb-2'>รายการที่อัปเดต (${details.updated_details.length})</h6>
                                                        <div class='table-responsive' style='max-height:240px; overflow:auto; border:1px solid #eee; border-radius:6px;'>
                                                            <table class='table table-sm table-hover mb-0'>
                                                                <thead class='table-light position-sticky top-0'>
                                                                    <tr>
                                                                        <th style='width:40px'>#</th>
                                                                        <th>รหัส</th>
                                                                        <th>ชื่อ</th>
                                                                        <th>เดิม</th>
                                                                        <th>ใหม่</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>${updatedRows}</tbody>
                                                            </table>
                                                        </div>
                                                    </div>` : '<div class="mt-3 text-muted small">ไม่มีรายการที่ต้องอัปเดต</div>';

                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'ซิงค์สำเร็จ',
                                                    width: 760,
                                                    html: `
                                                        <div class='row g-2 text-start small'>
                                                            <div class='col-6 col-lg-3'><div class='p-2 bg-light rounded border'><div class='text-muted'>อัปเดต</div><div class='fs-5 fw-semibold text-success'>${s.updated}</div></div></div>
                                                            <div class='col-6 col-lg-3'><div class='p-2 bg-light rounded border'><div class='text-muted'>ไม่เปลี่ยน</div><div class='fs-5 fw-semibold'>${s.unchanged}</div></div></div>
                                                            <div class='col-6 col-lg-3'><div class='p-2 bg-light rounded border'><div class='text-muted'>ไม่พบ</div><div class='fs-5 fw-semibold text-warning'>${s.not_found}</div></div></div>
                                                            <div class='col-6 col-lg-3'><div class='p-2 bg-light rounded border'><div class='text-muted'>ไม่ถูกต้อง</div><div class='fs-5 fw-semibold text-danger'>${s.invalid_status}</div></div></div>
                                                        </div>
                                                        ${updatedTable}
                                                    `,
                                                    confirmButtonText: 'ปิด',
                                                });

                                                this.querySelector('div').innerHTML = originalContent;
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'ซิงค์ไม่สำเร็จ',
                                                    html: `<div class='text-danger small'>${data.message || 'ไม่สามารถซิงค์ได้'}</div>`,
                                                });
                                                this.querySelector('div').innerHTML = originalContent;
                                            }
                                        }).catch(err=>{
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'เกิดข้อผิดพลาด',
                                                html: `<div class='text-danger small'>${err.message}</div>`
                                            });
                                            this.querySelector('div').innerHTML = originalContent;
                                        }).finally(()=>{
                                            this.dataset.loading='0';
                                        });
                                    });
                                });
                            </script>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Overview -->
                    <div class="row mb-4" id="overview">
                        <div class="col-12">
                            <h5 class="section-title">ภาพรวมประจำเดือน
                                {{ now()->locale('th')->translatedFormat('F Y') }}
                            </h5>
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
                                            <input type="text" class="form-control form-control-sm"
                                                placeholder="ค้นหานักเรียน...">
                                            <button class="btn btn-sm btn-primary-app"><i
                                                    class="fas fa-search"></i></button>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                            data-bs-target="#studentFilterModal">
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
                                                                            <img src="{{ $avatarUrl }}" class="rounded-circle me-2"
                                                                                width="32" height="32" alt="{{ $studentName }}">
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
                                                                            <div class="progress"
                                                                                style="height: 8px; width: 100px; position: relative; margin-top: 10px;">
                                                                                <div class="progress-bar {{ $progressClass }}"
                                                                                    role="progressbar" style="width: {{ $score }}%">
                                                                                </div>
                                                                                @if($score >= 90)
                                                                                    <div
                                                                                        style="position: absolute; left: {{ $score }}%; top: -10px; transform: translateX(-50%); 
                                                                                                                    background-color: white; width: 24px; height: 24px; 
                                                                                                                    border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.3); 
                                                                                                                    display: flex; align-items: center; justify-content: center; 
                                                                                                                    border: 2px solid white; z-index: 10;">
                                                                                        <img src="{{ asset('images/smile.png') }}"
                                                                                            style="height: 16px; width: 16px;" alt="👍">
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
                                                        <button class="btn btn-sm btn-primary-app" data-bs-toggle="modal"
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
                    <a href="#" class="nav-link text-center" onclick="openArchivedStudentsSidebar()">
                        <i class="fas fa-archive"></i>
                        <span>ประวัติ</span>
                    </a>
                </div>
                <div class="col">
                    <a href="#" class="nav-link text-center" data-bs-toggle="modal" data-bs-target="#newViolationModal">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>บันทึก</span>
                    </a>
                </div>
                <div class="col">
                    <a href="#" class="nav-link text-center" data-bs-toggle="modal"
                        data-bs-target="#violationTypesModal">
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
                                            <img src="{{ asset('storage/' . $user->users_profile_image) }}"
                                                class="rounded-circle" width="100" height="100" id="profile-preview">
                                        @else
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->users_first_name) }}&background=1020AD&color=fff"
                                                class="rounded-circle" width="100" height="100" id="profile-preview">
                                        @endif
                                        <label for="profile_image"
                                            class="btn btn-sm btn-primary-app position-absolute bottom-0 end-0 rounded-circle"
                                            style="cursor: pointer;">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                        <input type="file" name="profile_image" id="profile_image"
                                            style="display: none;" accept="image/*">
                                    </div>
                                    <h5 class="mt-3 mb-1">{{ $user->users_name_prefix }}{{ $user->users_first_name }}
                                        {{ $user->users_last_name }}
                                    </h5>
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
                                    <input type="email" class="form-control" name="users_email"
                                        value="{{ $user->users_email }}" disabled>
                                    <div class="form-text">อีเมลไม่สามารถแก้ไขได้</div>
                                </div>
                            </div>

                            <!-- คอลัมน์ขวา: แท็บข้อมูลและการตั้งค่า -->
                            <div class="col-md-8">
                                <ul class="nav nav-tabs mb-3" id="profileTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="personal-tab" data-bs-toggle="tab"
                                            data-bs-target="#personal" type="button" role="tab"
                                            aria-selected="true">ข้อมูลส่วนตัว</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="work-tab" data-bs-toggle="tab"
                                            data-bs-target="#work" type="button" role="tab"
                                            aria-selected="false">ข้อมูลการทำงาน</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="password-tab" data-bs-toggle="tab"
                                            data-bs-target="#password" type="button" role="tab"
                                            aria-selected="false">รหัสผ่าน</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="profileTabContent">
                                    <!-- แท็บข้อมูลส่วนตัว -->
                                    <div class="tab-pane fade show active" id="personal" role="tabpanel"
                                        aria-labelledby="personal-tab">
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
                                                <input type="text" class="form-control" name="users_first_name"
                                                    value="{{ $user->users_first_name }}">
                                            </div>
                                            <div class="col-4 mb-3">
                                                <label class="form-label">นามสกุล</label>
                                                <input type="text" class="form-control" name="users_last_name"
                                                    value="{{ $user->users_last_name }}">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">เบอร์โทรศัพท์</label>
                                                <input type="tel" class="form-control" name="users_phone_number"
                                                    value="{{ $user->users_phone_number }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">วันเกิด</label>
                                                <input type="date" class="form-control" name="users_birthdate"
                                                    value="{{ \Carbon\Carbon::parse($user->users_birthdate)->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- แท็บข้อมูลการทำงาน -->
                                    <div class="tab-pane fade" id="work" role="tabpanel" aria-labelledby="work-tab">
                                        @if($user->teacher)
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">ตำแหน่ง</label>
                                                    <input type="text" class="form-control" name="teachers_position"
                                                        value="{{ $user->teacher->teachers_position }}"
                                                        autocomplete="organization-title">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">รหัสประจำตัวครู</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $user->teacher->teachers_employee_code }}" disabled>
                                                    <div class="form-text">รหัสประจำตัวไม่สามารถแก้ไขได้</div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">กลุ่มสาระ/ฝ่าย</label>
                                                    <input type="text" class="form-control" name="teachers_department"
                                                        value="{{ $user->teacher->teachers_department }}"
                                                        autocomplete="organization">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">วิชาที่สอน</label>
                                                    <input type="text" class="form-control" name="teachers_major"
                                                        value="คอมพิวเตอร์" autocomplete="off">
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info">ไม่พบข้อมูลการทำงาน</div>
                                        @endif
                                    </div>

                                    <!-- แท็บเปลี่ยนรหัสผ่าน -->
                                    <div class="tab-pane fade" id="password" role="tabpanel"
                                        aria-labelledby="password-tab">
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

    <!-- Google Sheets Import Modal (Admin Only) -->
    @if(auth()->user()->users_role === 'admin')
        <div class="modal fade" id="googleSheetsImportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg"
                    style="border-radius: 16px; background: linear-gradient(135deg, #f8fafb 0%, #ffffff 100%);">
                    <div class="modal-header border-0 pb-2" style="background: #fff; border-radius: 16px 16px 0 0;">
                        <h5 class="modal-title text-dark fw-bold">
                            <i class="fab fa-google-drive me-2"></i> นำเข้าข้อมูลจาก Google Sheets
                        </h5>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 py-4">
                        <!-- Sheet Selection - Compact Design -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2"
                                    style="width: 32px; height: 32px;">
                                    <i class="fas fa-file-alt text-primary" style="font-size: 14px;"></i>
                                </div>
                                <h6 class="mb-0 text-dark">เลือกแผ่นข้อมูล</h6>
                            </div>
                            <div id="sheetSelectionContainer" class="ms-4">
                                <div class="d-flex justify-content-center py-3">
                                    <div class="spinner-border text-primary" role="status"
                                        style="width: 1.5rem; height: 1.5rem;">
                                        <span class="visually-hidden">กำลังโหลด...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Sheet Info - Minimalist -->
                        <div id="selectedSheetInfo" class="d-none mb-3">
                            <div class="card border-0 bg-light bg-opacity-50" style="border-radius: 12px;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-info me-2"></i>
                                        <div class="flex-grow-1">
                                            <div id="sheetDescription" class="text-dark mb-1"></div>
                                            <div class="d-flex align-items-center">
                                                <small class="text-muted me-2">คอลัมน์ที่คาดหวัง:</small>
                                                <span id="expectedColumns"
                                                    class="badge bg-secondary bg-opacity-75 text-dark"
                                                    style="font-size: 10px;"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Google Sheets URL Info - Compact -->
                        <div class="card border-0 bg-gradient mb-3"
                            style="background: linear-gradient(135deg, #e0f2fe 0%, #f3e5f5 100%); border-radius: 12px;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <i class="fab fa-google text-success me-2"></i>
                                    <div>
                                        <small class="text-dark d-block">ข้อมูลจาก:</small>
                                        <a href="https://docs.google.com/spreadsheets/d/1L3O0f5HdX_7cPw2jrQT4IaPsjw_jFD3O0aeH9ZQ499c/edit"
                                            target="_blank" class="text-primary text-decoration-none fw-medium"
                                            style="font-size: 13px;">
                                            Google Sheets ระบบพฤติกรรมนักเรียน
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button - Modern Style -->
                        <div class="text-center mb-3">
                            <button id="previewGoogleSheetsBtn" class="btn btn-primary px-4 py-2 fw-medium"
                                style="border-radius: 10px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                                <i class="fas fa-eye me-2"></i> ดูตัวอย่างข้อมูล
                            </button>
                            <div id="googleSheetsLoading" class="d-none mt-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="spinner-border text-primary me-2" role="status"
                                        style="width: 1.25rem; height: 1.25rem;">
                                        <span class="visually-hidden">กำลังโหลด...</span>
                                    </div>
                                    <small class="text-muted">กำลังดึงข้อมูลจาก Google Sheets...</small>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Container - Compact Design -->
                        <div id="googleSheetsPreviewContainer" class="d-none">
                            <!-- Summary Cards - Minimalist Grid -->
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="card border-0 h-100"
                                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px;">
                                        <div class="card-body p-3 text-white text-center">
                                            <i class="fas fa-check-circle mb-2" style="font-size: 1.25rem;"></i>
                                            <div class="h5 mb-1" id="googleSheetsValidCount">0</div>
                                            <small style="font-size: 11px;">ข้อมูลถูกต้อง</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card border-0 h-100"
                                        style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px;">
                                        <div class="card-body p-3 text-white text-center">
                                            <i class="fas fa-exclamation-triangle mb-2" style="font-size: 1.25rem;"></i>
                                            <div class="h5 mb-1" id="googleSheetsDuplicateCount">0</div>
                                            <small style="font-size: 11px;">ข้อมูลซ้ำ</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card border-0 h-100"
                                        style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 12px;">
                                        <div class="card-body p-3 text-white text-center">
                                            <i class="fas fa-times-circle mb-2" style="font-size: 1.25rem;"></i>
                                            <div class="h5 mb-1" id="googleSheetsErrorCount">0</div>
                                            <small style="font-size: 11px;">ข้อมูลผิดพลาด</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card border-0 h-100"
                                        style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 12px;">
                                        <div class="card-body p-3 text-white text-center">
                                            <i class="fas fa-list mb-2" style="font-size: 1.25rem;"></i>
                                            <div class="h5 mb-1" id="googleSheetsTotalCount">0</div>
                                            <small style="font-size: 11px;">รวมทั้งหมด</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Tabs - Modern Design -->
                            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                                <div class="card-header bg-white border-0 pt-3" style="border-radius: 16px 16px 0 0;">
                                    <ul class="nav nav-pills nav-fill" id="googleSheetsDataTabs" role="tablist"
                                        style="background: #f8fafc; border-radius: 10px; padding: 4px;">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="valid-tab" data-bs-toggle="tab"
                                                data-bs-target="#valid" type="button" role="tab"
                                                style="border-radius: 8px; font-size: 13px; padding: 8px 12px; border: none; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                                <i class="fas fa-check-circle me-1"></i> ข้อมูลถูกต้อง
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="duplicate-tab" data-bs-toggle="tab"
                                                data-bs-target="#duplicate" type="button" role="tab"
                                                style="border-radius: 8px; font-size: 13px; padding: 8px 12px; border: none; color: #6b7280; background: transparent;">
                                                <i class="fas fa-exclamation-triangle me-1"></i> ข้อมูลซ้ำ
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="error-tab" data-bs-toggle="tab"
                                                data-bs-target="#error" type="button" role="tab"
                                                style="border-radius: 8px; font-size: 13px; padding: 8px 12px; border: none; color: #6b7280; background: transparent;">
                                                <i class="fas fa-times-circle me-1"></i> ข้อมูลผิดพลาด
                                            </button>
                                        </li>
                                    </ul>
                                    <style>
                                        #googleSheetsDataTabs .nav-link:not(.active):hover {
                                            background: rgba(16, 185, 129, 0.1) !important;
                                            color: #059669 !important;
                                        }

                                        #googleSheetsDataTabs .nav-link.active {
                                            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
                                            color: white !important;
                                        }
                                    </style>
                                </div>
                                <div class="card-body p-3">
                                    <div class="tab-content" id="googleSheetsDataTabsContent">
                                        <!-- Valid Data Tab -->
                                        <div class="tab-pane fade show active" id="valid" role="tabpanel">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0 text-dark" style="font-size: 14px;">ข้อมูลที่พร้อมนำเข้า
                                                </h6>
                                                <div>
                                                    <button id="selectAllGoogleSheetsValid"
                                                        class="btn btn-sm btn-outline-primary"
                                                        style="font-size: 11px; padding: 4px 8px; border-radius: 6px;">เลือกทั้งหมด</button>
                                                    <button id="deselectAllGoogleSheetsValid"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        style="font-size: 11px; padding: 4px 8px; border-radius: 6px;">ยกเลิกทั้งหมด</button>
                                                </div>
                                            </div>
                                            <div style="max-height: 300px; overflow-y: auto; border-radius: 8px;">
                                                <table class="table table-sm mb-0" id="googleSheetsValidTable"
                                                    style="font-size: 12px;">
                                                    <thead class="sticky-top"
                                                        style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);">
                                                        <tr>
                                                            <th style="width: 40px; padding: 8px;"><input type="checkbox"
                                                                    id="checkAllGoogleSheetsValid"
                                                                    style="transform: scale(0.9);"></th>
                                                            <th style="padding: 8px; color: #374151;">แถว</th>
                                                            <th style="padding: 8px; color: #374151;">ชื่อ</th>
                                                            <th style="padding: 8px; color: #374151;">นามสกุล</th>
                                                            <th style="padding: 8px; color: #374151;">อีเมล</th>
                                                            <th style="padding: 8px; color: #374151;">บทบาท</th>
                                                            <th style="padding: 8px; color: #374151;">รหัสนักเรียน</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Duplicate Data Tab -->
                                        <div class="tab-pane fade" id="duplicate" role="tabpanel">
                                            <h6 class="mb-2 text-dark" style="font-size: 14px;">ข้อมูลที่ซ้ำกับฐานข้อมูล
                                            </h6>
                                            <div style="max-height: 300px; overflow-y: auto; border-radius: 8px;">
                                                <table class="table table-sm mb-0" id="googleSheetsDuplicateTable"
                                                    style="font-size: 12px;">
                                                    <thead class="sticky-top"
                                                        style="background: linear-gradient(135deg, #fefcbf 0%, #fef3c7 100%);">
                                                        <tr>
                                                            <th style="padding: 8px; color: #374151;">แถว</th>
                                                            <th style="padding: 8px; color: #374151;">ชื่อ</th>
                                                            <th style="padding: 8px; color: #374151;">นามสกุล</th>
                                                            <th style="padding: 8px; color: #374151;">อีเมล</th>
                                                            <th style="padding: 8px; color: #374151;">บทบาท</th>
                                                            <th style="padding: 8px; color: #374151;">ฟิลด์ที่ซ้ำ</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Error Data Tab -->
                                        <div class="tab-pane fade" id="error" role="tabpanel">
                                            <h6 class="mb-2 text-dark" style="font-size: 14px;">ข้อมูลที่มีข้อผิดพลาด</h6>
                                            <div style="max-height: 300px; overflow-y: auto; border-radius: 8px;">
                                                <table class="table table-sm mb-0" id="googleSheetsErrorTable"
                                                    style="font-size: 12px;">
                                                    <thead class="sticky-top"
                                                        style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);">
                                                        <tr>
                                                            <th style="padding: 8px; color: #374151;">แถว</th>
                                                            <th style="padding: 8px; color: #374151;">ชื่อ</th>
                                                            <th style="padding: 8px; color: #374151;">นามสกุล</th>
                                                            <th style="padding: 8px; color: #374151;">อีเมล</th>
                                                            <th style="padding: 8px; color: #374151;">บทบาท</th>
                                                            <th style="padding: 8px; color: #374151;">ข้อผิดพลาด</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-2 pb-3" style="background: #f8fafb;">
                        <button type="button" class="btn btn-light px-3 py-2" data-bs-dismiss="modal"
                            style="border-radius: 8px; color: #6b7280; font-weight: 500;">ปิด</button>
                        <button id="importGoogleSheetsBtn" class="btn px-4 py-2 fw-medium" disabled
                            style="border-radius: 8px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; color: white;">
                            <i class="fas fa-download me-2"></i> นำเข้าข้อมูลที่เลือก
                        </button>
                        <div id="googleSheetsImportLoading" class="d-none ms-3">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-success me-2" role="status"
                                    style="width: 1rem; height: 1rem;">
                                    <span class="visually-hidden">กำลังนำเข้า...</span>
                                </div>
                                <small class="text-muted">กำลังนำเข้าข้อมูล...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                                <input type="text" class="form-control" id="behaviorStudentSearch"
                                    placeholder="พิมพ์ชื่อหรือรหัสนักเรียน..." autocomplete="off">
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
                                <select class="form-select" id="violationType" name="violation_id" data-violation-select
                                    required>
                                    <option value="">เลือกประเภทพฤติกรรม</option>
                                    <!-- ตัวเลือกจะถูกเพิ่มด้วย JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">คะแนนที่หัก <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="pointsDeducted" min="0" max="100"
                                    value="0" readonly>
                                <div class="form-text">คะแนนจะกำหนดตามประเภทที่เลือก</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label">วันที่เกิดเหตุการณ์ <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="violationDate" name="violation_date"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เวลาที่เกิดเหตุการณ์ <span
                                        class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="violationTime" name="violation_time"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รายละเอียดเพิ่มเติม</label>
                            <textarea class="form-control" id="violationDescription" name="description" rows="3"
                                placeholder="อธิบายรายละเอียดของเหตุการณ์..."></textarea>
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
                    <div class="d-flex justify-content-between mb-3 animate__animated animate__fadeIn"
                        style="animation-delay: 0.1s">
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control" id="violationTypeSearch"
                                placeholder="ค้นหาประเภทพฤติกรรม...">
                            <button class="btn btn-primary-app" type="button"><i class="fas fa-search"></i></button>
                        </div>
                        <button class="btn btn-primary-app" id="btnShowAddViolationType">
                            <i class="fas fa-plus me-2"></i>เพิ่มประเภทพฤติกรรมใหม่
                        </button>
                    </div>

                    <!-- ส่วนแสดงรายการประเภทพฤติกรรม -->
                    <div id="violationTypesList" class="mb-4 animate__animated animate__fadeIn"
                        style="animation-delay: 0.2s">
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
                            <label for="violation_name" class="form-label">ชื่อพฤติกรรม <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="violation_name" name="name"
                                placeholder="ระบุชื่อพฤติกรรม เช่น มาสาย, ไม่ทำการบ้าน" required>
                            <div class="invalid-feedback">กรุณาระบุชื่อพฤติกรรม</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="violation_category" class="form-label">ระดับความรุนแรง <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="violation_category" name="category" required>
                                    <option value="" selected disabled>เลือกระดับความรุนแรง</option>
                                    <option value="light">เบา</option>
                                    <option value="medium">ปานกลาง</option>
                                    <option value="severe">หนัก</option>
                                </select>
                                <div class="invalid-feedback">กรุณาเลือกระดับความรุนแรง</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="violation_points" class="form-label">คะแนนที่หัก <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="violation_points" name="points_deducted"
                                    min="0" max="100" required placeholder="ระบุคะแนนที่หัก">
                                <div class="invalid-feedback">กรุณาระบุคะแนนที่หัก (0-100)</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="violation_description" class="form-label">รายละเอียด</label>
                            <textarea class="form-control" id="violation_description" name="description" rows="3"
                                placeholder="อธิบายรายละเอียดเพิ่มเติม (ถ้ามี)"></textarea>
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
                                <button
                                    class="btn btn-outline-primary d-flex justify-content-between align-items-center"
                                    id="generateMonthlyReport" onclick="generateMonthlyReport()">
                                    <span>รายงานพฤติกรรมประจำเดือน</span>
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button
                                    class="btn btn-outline-primary d-flex justify-content-between align-items-center"
                                    id="generateRiskStudentsReport" onclick="generateRiskStudentsReport()">
                                    <span>รายงานสรุปนักเรียนที่มีความเสี่ยง</span>
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button
                                    class="btn btn-outline-primary d-flex justify-content-between align-items-center"
                                    id="generateAllBehaviorDataReport" onclick="generateAllBehaviorDataReport()">
                                    <span>ส่งออกข้อมูลพฤติกรรมทั้งหมด</span>
                                    <i class="fas fa-file-pdf"></i>
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
                        <button class="btn btn-outline-primary btn-sm"
                            onclick="retryLoadStudentDetail()">ลองใหม่</button>
                    </div>

                    <!-- Content -->
                    <div id="studentDetailContent" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <img id="studentProfileImage" class="rounded-circle" width="100" height="100"
                                        alt="รูปโปรไฟล์">
                                    <h5 id="studentFullName" class="mt-3 mb-1"></h5>
                                    <span id="studentClassBadge" class="badge bg-primary-app"></span>
                                    <hr>
                                    <div class="d-grid gap-2 mt-3">
                                        <button class="btn btn-primary-app"
                                            onclick="openNewViolationModal()">บันทึกพฤติกรรม</button>
                                        <button id="printReportBtn" class="btn btn-outline-secondary"
                                            onclick="printStudentReport(event)">พิมพ์รายงาน</button>
                                        <button id="notifyParentBtn" class="btn btn-warning" style="display: none;">
                                            <i class="fas fa-bell me-1"></i> แจ้งเตือนผู้ปกครอง
                                        </button>
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
                                        <img src="{{ asset('images/smile.png') }}" style="height: 30px; width: 30px;"
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
                    <button type="button" class="btn btn-outline-danger me-auto" id="deleteReportBtn"
                        style="display: none;">
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
    <div class="modal fade" id="classManagementModal" tabindex="-1" aria-labelledby="classManagementModalLabel"
        role="dialog">
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
                            <input type="text" class="form-control" id="classroomSearch" placeholder="ค้นหาห้องเรียน..."
                                autocomplete="off">
                            <button class="btn btn-primary-app" type="button" id="btnSearchClass"><i
                                    class="fas fa-search"></i></button>
                        </div>
                        <button class="btn btn-primary-app" id="btnShowAddClass">
                            <i class="fas fa-plus me-2"></i>เพิ่มห้องเรียนใหม่
                        </button>
                    </div>

                    <!-- ตัวกรองข้อมูล -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-6">
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
                                        <th style="width: 20%">ชั้นเรียน</th>
                                        <th style="width: 35%">ครูประจำชั้น</th>
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
                                        <label for="classes_level" class="form-label">ระดับชั้น <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="classes_level" name="classes_level" required
                                            autocomplete="off">
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
                                        <label for="classes_room_number" class="form-label">ห้อง <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="classes_room_number"
                                            name="classes_room_number" placeholder="ระบุเลขห้อง เช่น 1, 2, 3, ..."
                                            required maxlength="5" autocomplete="off">
                                        <div class="invalid-feedback">กรุณาระบุเลขห้อง</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="teacher_id" class="form-label">ครูประจำชั้น <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="teacher_id" name="teacher_id" required
                                            autocomplete="off">
                                            <option value="" selected disabled>เลือกครูประจำชั้น</option>
                                            <!-- จะถูกเติมโดย JavaScript -->
                                        </select>
                                        <div class="invalid-feedback">กรุณาเลือกครูประจำชั้น</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-secondary me-2"
                                        id="btnCancelClass">ยกเลิก</button>
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
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                            id="class-avg-score-bar" style="width: 0%"></div>
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
                                        <div id="chart-container"
                                            class="d-flex justify-content-center align-items-center"
                                            style="height: 200px;">
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
                                    <span class="badge bg-primary-app rounded-pill ms-2"
                                        id="student-count-badge">0</span>
                                </h6>
                                <div class="d-flex">
                                    <div class="input-group input-group-sm" style="width: 250px;">
                                        <input type="text" class="form-control" id="studentSearch"
                                            placeholder="ค้นหานักเรียน...">
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
                                    <ul class="pagination pagination-sm justify-content-end mb-0"
                                        id="student-pagination">
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
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        รายงานพฤติกรรมประจำเดือน
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="monthlyReportForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="report_month" class="form-label">เดือน</label>
                                <select class="form-select" id="report_month" required>
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
                            <div class="col-md-6">
                                <label for="report_year" class="form-label">ปี (พ.ศ.)</label>
                                <select class="form-select" id="report_year" required>
                                    @for($y = date('Y') + 543; $y >= date('Y') + 540; $y--)
                                        <option value="{{ $y - 543 }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="report_class_id" class="form-label">ชั้นเรียน (เฉพาะ)</label>
                            <select class="form-select" id="report_class_id">
                                <option value="">ทุกชั้นเรียน</option>
                                <!-- จะถูกเติมด้วย JavaScript หรือ Blade -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="downloadMonthlyReport()">
                        <i class="fas fa-file-pdf me-1"></i> สร้างรายงาน PDF
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
                            <input type="text" class="form-control" id="filter_name"
                                placeholder="พิมพ์ชื่อหรือรหัสนักเรียน...">
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
                                <input type="number" class="form-control" id="filter_score_value" min="0" max="100"
                                    value="75" disabled>
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
                                <input type="number" class="form-control" id="filter_violation_value" min="0" value="5"
                                    disabled>
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
                        <p class="mb-0">นักเรียนมีคะแนนความประพฤติต่ำมาก จำเป็นต้องได้รับการดูแลและติดตามอย่างใกล้ชิด
                        </p>
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
                                <span id="message-suggestion" class="text-primary cursor-pointer d-none"
                                    onclick="applyMessageSuggestion()">
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
                    <button type="button" class="btn btn-primary" id="send-notification-btn"
                        onclick="sendParentNotification()">
                        <i class="fas fa-paper-plane me-1"></i> ส่งการแจ้งเตือน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Students Report Modal -->
    <div class="modal fade" id="riskStudentsReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        รายงานสรุปนักเรียนที่มีความเสี่ยง
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="riskStudentsReportForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="risk_report_month" class="form-label">เดือน</label>
                                <select class="form-select" id="risk_report_month" required>
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
                            <div class="col-md-6">
                                <label for="risk_report_year" class="form-label">ปี (พ.ศ.)</label>
                                <select class="form-select" id="risk_report_year" required>
                                    @for($y = date('Y') + 543; $y >= date('Y') + 540; $y--)
                                        <option value="{{ $y - 543 }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="risk_report_level" class="form-label">ระดับความเสี่ยง</label>
                                <select class="form-select" id="risk_report_level">
                                    <option value="all">ทุกระดับ</option>
                                    <option value="high">ความเสี่ยงสูง</option>
                                    <option value="medium">ความเสี่ยงปานกลาง</option>
                                    <option value="low">ความเสี่ยงต่ำ</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="risk_report_class_id" class="form-label">ชั้นเรียน (เฉพาะ)</label>
                                <select class="form-select" id="risk_report_class_id">
                                    <option value="">ทุกชั้นเรียน</option>
                                    <!-- จะถูกเติมด้วย JavaScript หรือ Blade -->
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>หมายเหตุ:</strong>
                            รายงานนี้จะแสดงเฉพาะนักเรียนที่มีพฤติกรรมผิดระเบียบหรือมีคะแนนความประพฤติต่ำกว่า 90 คะแนน
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-warning" onclick="downloadRiskStudentsReport()">
                        <i class="fas fa-file-pdf me-1"></i> สร้างรายงาน PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- All Behavior Data Report Modal -->
    <div class="modal fade" id="allBehaviorDataReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        รายงานข้อมูลพฤติกรรมทั้งหมด
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="allBehaviorDataReportForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="all_data_report_month" class="form-label">เดือน</label>
                                <select class="form-select" id="all_data_report_month" required>
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
                            <div class="col-md-6">
                                <label for="all_data_report_year" class="form-label">ปี พ.ศ.</label>
                                <select class="form-select" id="all_data_report_year" required>
                                    <option value="2023">2566</option>
                                    <option value="2024">2567</option>
                                    <option value="2025">2568</option>
                                    <option value="2026">2569</option>
                                    <option value="2027">2570</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="all_data_report_class_id" class="form-label">ชั้นเรียน (เฉพาะ)</label>
                            <select class="form-select" id="all_data_report_class_id">
                                <option value="">ทุกชั้นเรียน</option>
                                <!-- จะถูกเติมด้วย JavaScript หรือ Blade -->
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            รายงานนี้จะแสดงข้อมูลพฤติกรรมนักเรียนทั้งหมดในเดือนที่เลือก
                            รวมถึงรายละเอียดการบันทึกแต่ละครั้ง และสถิติสรุป
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="downloadAllBehaviorDataReport()">
                        <i class="fas fa-file-pdf me-1"></i> สร้างรายงาน PDF
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
    <!-- Risk Students Report Modal -->
    <div class="modal fade" id="riskStudentsReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        รายงานสรุปนักเรียนที่มีความเสี่ยง
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="riskStudentsReportForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="risk_report_month" class="form-label">เดือน</label>
                                <select class="form-select" id="risk_report_month" required>
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
                            <div class="col-md-6">
                                <label for="risk_report_year" class="form-label">ปี (พ.ศ.)</label>
                                <select class="form-select" id="risk_report_year" required>
                                    @for($y = date('Y') + 543; $y >= date('Y') + 540; $y--)
                                        <option value="{{ $y - 543 }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="risk_report_level" class="form-label">ระดับความเสี่ยง</label>
                                <select class="form-select" id="risk_report_level">
                                    <option value="all">ทุกระดับ</option>
                                    <option value="high">ความเสี่ยงสูง</option>
                                    <option value="medium">ความเสี่ยงปานกลาง</option>
                                    <option value="low">ความเสี่ยงต่ำ</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="risk_report_class_id" class="form-label">ชั้นเรียน (เฉพาะ)</label>
                                <select class="form-select" id="risk_report_class_id">
                                    <option value="">ทุกชั้นเรียน</option>
                                    <!-- จะถูกเติมด้วย JavaScript หรือ Blade -->
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>หมายเหตุ:</strong>
                            รายงานนี้จะแสดงเฉพาะนักเรียนที่มีพฤติกรรมผิดระเบียบหรือมีคะแนนความประพฤติต่ำกว่า 90 คะแนน
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-warning" onclick="downloadRiskStudentsReport()">
                        <i class="fas fa-file-pdf me-1"></i> สร้างรายงาน PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Report Modal -->
    <div class="modal fade" id="monthlyReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        รายงานพฤติกรรมประจำเดือน
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="monthlyReportForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="report_month" class="form-label">เดือน</label>
                                <select class="form-select" id="report_month" required>
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
                            <div class="col-md-6">
                                <label for="report_year" class="form-label">ปี (พ.ศ.)</label>
                                <select class="form-select" id="report_year" required>
                                    @for($y = date('Y') + 543; $y >= date('Y') + 540; $y--)
                                        <option value="{{ $y - 543 }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="report_class_id" class="form-label">ชั้นเรียน (เฉพาะ)</label>
                            <select class="form-select" id="report_class_id">
                                <option value="">ทุกชั้นเรียน</option>
                                <!-- จะถูกเติมด้วย JavaScript หรือ Blade -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="downloadMonthlyReport()">
                        <i class="fas fa-file-pdf me-1"></i> สร้างรายงาน PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

    <script src="/js/class-detail.js"></script>
    <!-- เพิ่ม behavior report script -->
    <script src="/js/behavior-report.js"></script>
    <!-- Reports JS -->
    <script src="/js/reports.js"></script>
    <script src="/js/student-filter.js"></script>
    <script src="/js/parent-notification.js"></script>
    <!-- Archived Students JS -->
    <script src="/js/archived-students.js"></script>

    <!-- Google Sheets Import JavaScript (Admin Only) -->
    @if(auth()->user()->users_role === 'admin')
        <script>
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

                // Global Variables (already declared above)
                selectedSheetType = 'students'; // default

                // Load Available Sheets when modal opens
                $('#googleSheetsImportModal').on('show.bs.modal', function () {
                    loadAvailableSheets();
                });

                // Load Available Sheets
                function loadAvailableSheets() {
                    $.ajax({
                        url: '{{ route("admin.google-sheets.sheets") }}',
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
                        url: '{{ route("admin.google-sheets.preview") }}',
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
                        url: '{{ route("admin.google-sheets.import") }}',
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
                        beforeSend: function(xhr) {
                            // Request starting...
                        },
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
        </script>
    @endif

    <!-- Archived Students Sidebar -->
    <div id="archivedStudentsSidebar" class="sidebar-overlay">
        <div class="sidebar-content">
            <div class="sidebar-header">
                <h5 class="sidebar-title">
                    <i class="fas fa-archive me-2"></i>ประวัติการเก็บข้อมูลนักเรียน
                </h5>
                <button type="button" class="btn-close-sidebar" onclick="closeArchivedStudentsSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="sidebar-body">
                <!-- Filter Section -->
                <div class="filter-section mb-3">
                    <h6 class="filter-title">
                        <i class="fas fa-filter me-1"></i>ตัวกรองข้อมูล
                    </h6>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label for="statusFilter" class="form-label">สถานะ</label>
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">ทั้งหมด</option>
                                <option value="graduated">จบการศึกษา</option>
                                <option value="transferred">ย้ายโรงเรียน</option>
                                <option value="suspended">พักการเรียน</option>
                                <option value="expelled">ถูกไล่ออก</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="levelFilter" class="form-label">ชั้น</label>
                            <select id="levelFilter" class="form-select form-select-sm">
                                <option value="">ทั้งหมด</option>
                                <option value="ม.1">ม.1</option>
                                <option value="ม.2">ม.2</option>
                                <option value="ม.3">ม.3</option>
                                <option value="ม.4">ม.4</option>
                                <option value="ม.5">ม.5</option>
                                <option value="ม.6">ม.6</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="roomFilter" class="form-label">ห้อง</label>
                            <select id="roomFilter" class="form-select form-select-sm">
                                <option value="">ทั้งหมด</option>
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
                                <option value="11">11</option>
                                <option value="12">12</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label for="scoreFilter" class="form-label">คะแนน</label>
                            <select id="scoreFilter" class="form-select form-select-sm">
                                <option value="">ทั้งหมด</option>
                                <option value="90-100">90-100 คะแนน</option>
                                <option value="75-89">75-89 คะแนน</option>
                                <option value="50-74">50-74 คะแนน</option>
                                <option value="0-49">0-49 คะแนน</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="searchInput" class="form-label">ค้นหา</label>
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control form-control-sm"
                                    placeholder="รหัสนักเรียนหรือชื่อ...">
                                <button class="btn btn-primary-app btn-sm" type="button"
                                    onclick="searchArchivedStudents()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between mb-3">
                        <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
                            <i class="fas fa-times me-1"></i>ล้างตัวกรอง
                        </button>
                        <button class="btn btn-success btn-sm" onclick="exportArchivedData()">
                            <i class="fas fa-download me-1"></i>ส่งออกข้อมูล
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="archivedDataLoading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-2 text-muted small">กำลังโหลดข้อมูล...</p>
                </div>

                <!-- Students List -->
                <div id="archivedDataContainer">
                    <div id="archivedStudentsList">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>กรุณาคลิกค้นหาเพื่อโหลดข้อมูล</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div id="archivedPagination" class="d-flex justify-content-center mt-3">
                    <!-- Pagination will be dynamically generated -->
                </div>
            </div>
        </div>
    </div>

    <!-- Student History Detail Sidebar -->
    <div id="studentHistorySidebar" class="sidebar-overlay">
        <div class="sidebar-content sidebar-detail">
            <div class="sidebar-header">
                <h5 class="sidebar-title">
                    <i class="fas fa-history me-2"></i>ประวัติการบันทึกพฤติกรรม
                </h5>
                <div class="sidebar-actions">
                    <button type="button" class="btn-back-sidebar me-2" onclick="backToArchivedStudents()">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <button type="button" class="btn-close-sidebar" onclick="closeStudentHistorySidebar()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="sidebar-body">
                <!-- Student Info Card -->
                <div id="studentInfoSection" class="student-info-card mb-3">
                    <div class="student-info-header">
                        <div class="student-details">
                            <h6 class="student-name mb-2" id="studentName">กำลังโหลด...</h6>
                            
                            <!-- Student Meta Information -->
                            <div class="student-meta-grid">
                                <div class="meta-item">
                                    <div class="meta-label">
                                        <i class="fas fa-id-card me-1"></i>รหัสนักเรียน
                                    </div>
                                    <div class="meta-value" id="studentCodeView">-</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">
                                        <i class="fas fa-graduation-cap me-1"></i>ชั้นเรียน
                                    </div>
                                    <div class="meta-value" id="studentClassView">-</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">
                                        <i class="fas fa-user-check me-1"></i>สถานะ
                                    </div>
                                    <div class="meta-value">
                                        <span class="badge" id="studentStatus">-</span>
                                    </div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">
                                        <i class="fas fa-star me-1"></i>คะแนนปัจจุบัน
                                    </div>
                                    <div class="meta-value">
                                        <span class="badge" id="studentScore">-/100</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Behavior Statistics -->
                <div class="behavior-stats mb-3">
                    <div class="row">
                        <div class="col-4">
                            <div class="stat-card stat-violations">
                                <div class="stat-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-number" id="totalViolations">0</span>
                                    <span class="stat-label">การทำผิด</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-card stat-score">
                                <div class="stat-icon">
                                    <i class="fas fa-minus-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-number" id="totalScoreDeducted">0</span>
                                    <span class="stat-label">คะแนนหัก</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-card stat-average">
                                <div class="stat-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-number" id="averageScore">0</span>
                                    <span class="stat-label">เฉลี่ย/ปี</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Loading -->
                <div id="historyLoading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-2 text-muted small">กำลังโหลดประวัติ...</p>
                </div>

                <!-- History List -->
                <div id="historyContainer">
                    <h6 class="section-title">ประวัติการบันทึกพฤติกรรม</h6>
                    <div id="behaviorHistoryList">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-history fa-2x mb-3"></i>
                            <p>ไม่มีประวัติการบันทึกพฤติกรรม</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>