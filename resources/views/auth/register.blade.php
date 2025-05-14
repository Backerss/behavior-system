<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - ระบบติดตามพฤติกรรมวินัยนักเรียน</title>
    <!-- Bootstrap 5.3.6 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Color Variables */
        :root {
            --primary-app: #1020AD;
            --primary-light: rgba(16, 32, 173, 0.1);
            --primary-dark: #0a1570;
            --secondary-app: #F6E200;
            --secondary-light: #fffbd3;
            --accent-app: #95A4D8;
            --accent-light: rgba(149, 164, 216, 0.2);
            --accent-dark: #7180c0;
            --light-gray: #f8f9fa;
            --border-radius-lg: 1rem;
            --box-shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --box-shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
            --box-shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
            --transition-base: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --transition-bounce: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        /* Base Layout */
        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--light-gray);
            overflow-x: hidden;
        }
        
        /* Particles Container */
        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        
        /* App Container */
        .app-container {
            position: relative;
            box-shadow: var(--box-shadow-lg);
            overflow: hidden;
        }
        
        @media (min-width: 992px) {
            .app-container {
                margin: 2rem auto;
                max-width: 1000px;
                border-radius: var(--border-radius-lg);
                /* แก้ไขจาก height: calc(100vh - 4rem); เป็น min-height เพื่อให้ขยายตามเนื้อหา */
                min-height: calc(100vh - 4rem);
                height: auto;
                display: grid;
                grid-template-rows: auto 1fr;
                /* เพิ่ม overflow-y: auto เพื่อให้สามารถเลื่อนได้ถ้าเนื้อหายาว */
                overflow-y: auto;
            }
        }
        
        /* Form Container */
        .app-form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 1.5rem;
            position: relative;
        }
        
        @media (min-width: 992px) {
            .app-form-container {
                padding: 2rem;
                max-width: 800px;
                padding-bottom: 4rem;
            }
            
            /* Left decoration for desktop */
            .app-form-container::before {
                content: '';
                position: absolute;
                left: -30px;
                top: 20%;
                height: 60%;
                width: 6px;
                background: linear-gradient(to bottom, var(--primary-app), var(--accent-app));
                border-radius: 3px;
                opacity: 0.7;
            }
        }
        
        /* Header/Nav Bar */
        .navbar {
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-app), var(--primary-dark)) !important;
        }
        
        @media (min-width: 992px) {
            .navbar {
                border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
                padding: 0.75rem 2rem;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
                font-weight: 600;
                letter-spacing: 0.5px;
            }
            
            .navbar-brand img {
                transform: scale(1.2);
                transition: var(--transition-base);
            }
            
            .navbar-brand:hover img {
                transform: scale(1.3) rotate(5deg);
            }
        }
        
        /* Back Button */
        .back-to-login {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 10;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-bounce);
            box-shadow: var(--box-shadow-sm);
        }
        
        .back-to-login:hover {
            transform: translateX(-3px);
            box-shadow: var(--box-shadow-md);
        }
        
        @media (min-width: 992px) {
            .back-to-login {
                top: 20px;
                left: 20px;
            }
        }
        
        /* Registration Progress */
        .register-progress-container {
            margin-bottom: 2rem;
        }
        
        .register-progress {
            height: 8px;
            background-color: var(--accent-light);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-bar {
            transition: width 0.6s ease-in-out;
            background: linear-gradient(90deg, var(--primary-app), var(--accent-app));
        }
        
        @media (min-width: 992px) {
            .register-progress-container {
                padding: 0 1rem;
            }
            
            #progress-text, #progress-percentage {
                font-size: 0.9rem;
            }
        }
        
        /* Form Title */
        .form-title {
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }
        
        .form-title h2 {
            color: var(--primary-app);
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }
        
        .form-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-app), var(--accent-app));
            border-radius: 2px;
        }
        
        .form-title p {
            margin-top: 1.5rem;
            color: #6c757d;
        }
        
        @media (min-width: 992px) {
            .form-title h2 {
                font-size: 2.2rem;
            }
            
            .form-title p {
                font-size: 1.1rem;
                max-width: 80%;
                margin-left: auto;
                margin-right: auto;
            }
        }
        
        /* Form Steps */
        .form-step {
            opacity: 0;
            display: none;
        }
        
        .form-step.active {
            opacity: 1;
            display: block;
            padding-bottom: 2rem;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Section heading */
        .section-heading {
            position: relative;
            padding-bottom: 12px;
            margin-bottom: 24px;
            font-weight: 600;
            color: var(--primary-app);
        }
        
        .section-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-app), var(--primary-app));
            border-radius: 2px;
        }
        
        @media (min-width: 992px) {
            .section-heading {
                font-size: 1.5rem;
                margin-bottom: 2rem;
            }
        }
        
        /* Role Cards */
        .role-cards-container {
            margin: 2rem 0;
        }
        
        .role-card {
            cursor: pointer;
            transition: var(--transition-bounce);
            border: 2px solid transparent;
            height: 100%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--box-shadow-sm);
        }
        
        .role-card .card-body {
            padding: 2rem 1.5rem;
            position: relative;
            z-index: 1;
            background-color: white;
            transition: var(--transition-base);
        }
        
        .role-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-light), var(--accent-light));
            opacity: 0;
            transition: var(--transition-base);
            z-index: 0;
        }
        
        .role-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--box-shadow-md);
        }
        
        .role-card:hover::before {
            opacity: 1;
        }
        
        .role-card.selected {
            border-color: var(--primary-app);
            box-shadow: 0 0 0 4px var(--primary-light);
        }
        
        .role-card.selected .role-icon {
            transform: scale(1.15);
        }
        
        /* เพิ่ม style สำหรับรูปภาพ */
        .role-image {
            max-width: 80%;
            height: auto;
            transition: var(--transition-bounce);
        }

        .role-icon {
            width: 90px;
            height: 90px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            margin: 0 auto 20px;
            transition: var(--transition-bounce);
            box-shadow: var(--box-shadow-sm);
            background-color: white; /* เปลี่ยนจากสีพื้นหลังที่กำหนดไว้ก่อนหน้า */
            overflow: hidden;
            padding: 0.5rem;
        }

        @media (min-width: 992px) {
            .role-icon {
                width: 120px;
                height: 120px;
            }
        }

        /* เพิ่ม animation สำหรับรูปภาพเมื่อ hover */
        .role-card:hover .role-image {
            transform: scale(1.15);
        }

        /* เพิ่ม animation สำหรับรูปภาพเมื่อการ์ดถูกเลือก */
        .role-card.selected .role-image {
            transform: scale(1.15);
        }
        
        .role-card h5 {
            font-weight: 600;
            margin-top: 1rem;
            transition: var(--transition-base);
        }
        
        .role-card:hover h5 {
            color: var(--primary-app);
        }
        
        @media (min-width: 992px) {
            .role-card {
                border-radius: 20px;
            }
            
            .role-icon {
                width: 100px;
                height: 100px;
            }
            
            .role-card .card-body {
                padding: 2.5rem;
            }
            
            .role-card h5 {
                font-size: 1.5rem;
            }
        }
        
        /* Form Controls */
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #ced4da;
            padding: 1rem 0.75rem;
            transition: var(--transition-base);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-app);
            box-shadow: 0 0 0 0.25rem var(--primary-light);
        }
        
        .form-floating > label {
            padding: 1rem 0.75rem;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label,
        .form-floating > .form-select ~ label {
            color: var(--primary-app);
            font-weight: 500;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-app);
            border-color: var(--primary-app);
        }
        
        .form-switch .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem var(--primary-light);
        }
        
        @media (min-width: 992px) {
            .form-control, .form-select {
                font-size: 1rem;
            }
        }
        
        /* Gender Radio Group */
        .gender-group {
            display: flex;
            gap: 1.5rem;
            margin: 1rem 0;
        }
        
        .gender-option {
            flex: 1;
            position: relative;
        }
        
        .gender-option input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .gender-option label {
            display: block;
            text-align: center;
            padding: 0.75rem;
            border: 2px solid #ced4da;
            border-radius: 10px;
            cursor: pointer;
            transition: var(--transition-base);
        }
        
        .gender-option input:checked + label {
            border-color: var(--primary-app);
            background-color: var(--primary-light);
            color: var(--primary-app);
            font-weight: 500;
        }
        
        .gender-option:hover label {
            border-color: var(--accent-app);
        }
        
        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: var(--transition-bounce);
        }
        
        .btn-lg {
            padding: 1rem 2rem;
        }
        
        .btn-accent-app {
            background: linear-gradient(135deg, var(--accent-app), var(--accent-dark));
            border: none;
            color: white;
        }
        
        .btn-accent-app:hover {
            background: linear-gradient(135deg, var(--accent-dark), var(--primary-app));
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-md);
            color: white;
        }
        
        .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
        }
        
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: #495057;
        }
        
        .btn i {
            transition: var(--transition-base);
        }
        
        .next-step:hover i {
            transform: translateX(4px);
        }
        
        .prev-step:hover i {
            transform: translateX(-4px);
        }
        
        .btn-link {
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition-base);
        }
        
        .btn-link:hover {
            color: var(--primary-app) !important;
            transform: translateY(-2px);
        }
        
        /* Submit button animation */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(149, 164, 216, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(149, 164, 216, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(149, 164, 216, 0);
            }
        }
        
        button[type="submit"] {
            position: relative;
            overflow: hidden;
        }
        
        button[type="submit"]:hover {
            animation: pulse 1.5s infinite;
        }
        
        button[type="submit"]::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(45deg);
            z-index: 1;
            transition: all 0.6s ease;
            opacity: 0;
        }
        
        button[type="submit"]:hover::before {
            opacity: 1;
            left: 100%;
        }
        
        /* Toast Notifications */
        .toast {
            border-radius: 10px;
            box-shadow: var(--box-shadow-md);
        }
        
        .toast-header {
            border-radius: 10px 10px 0 0;
        }
        
        /* Role fields animation */
        .role-fields {
            display: none;
            animation: fadeSlideIn 0.5s ease forwards;
        }
        
        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Desktop-specific responsive design */
        @media (min-width: 1200px) {
            .app-form-container {
                max-width: 900px;
            }
            
            .form-step {
                padding: 1rem;
            }
            
            .row-cols-xl-3 > * {
                flex: 0 0 auto;
                width: 33.3333%;
            }
            
            /* Two-column layout for role-specific fields */
            #step2 .role-fields {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }
            
            #step2 .section-heading {
                grid-column: 1 / -1;
            }
            
            #step2 .mt-4 {
                grid-column: 1 / -1;
            }
        }
        
        /* Form validation styling */
        .was-validated .form-control:invalid:focus,
        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }
        
        .was-validated .form-control:valid:focus,
        .form-control.is-valid:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        
        /* Terms checkbox styling */
        .form-check-input:checked {
            background-color: var(--primary-app);
            border-color: var(--primary-app);
        }
        
        .form-check-input:focus {
            border-color: var(--accent-app);
            box-shadow: 0 0 0 0.25rem var(--primary-light);
        }
        
        /* Password strength indicator */
        .password-strength {
            height: 5px;
            margin-top: 0.5rem;
            border-radius: 3px;
            display: none;
        }
        
        /* Loading indicator */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition-base);
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--accent-light);
            border-top-color: var(--primary-app);
            border-radius: 50%;
            animation: spinner 1s linear infinite;
        }
        
        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Additional animations */
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }
    </style>
</head>
<body class="bg-light">
    <div id="particles-js" class="particles-container"></div>
    
    <div class="app-container bg-white min-vh-100 d-flex flex-column">
        <!-- Back button -->
        <a href="/" class="back-to-login btn btn-sm rounded-circle bg-white shadow-sm text-primary-app">
            <i class="fas fa-arrow-left"></i>
        </a>
        
        <!-- Header/Nav Bar (Simplified) -->
        <nav class="navbar bg-primary-app shadow-sm">
            <div class="container">
                <a class="navbar-brand text-white d-flex align-items-center mx-auto" href="#">
                    <img src="https://placehold.co/40x40" alt="Logo" class="rounded-circle me-2"> 
                    <span>ระบบติดตามพฤติกรรม</span>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container py-4 flex-grow-1 d-flex flex-column">
            <div class="app-form-container">
                <!-- เพิ่มการแสดงข้อความผิดพลาด -->
                @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- แสดงข้อผิดพลาดของระบบ -->
                @if ($errors->has('system_error'))
                <div class="alert alert-danger mb-4">
                    {{ $errors->first('system_error') }}
                </div>
                @endif

                <!-- Registration Progress -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted" id="progress-text">ขั้นตอนที่ 1 จาก 3</span>
                        <span class="small text-muted" id="progress-percentage">33%</span>
                    </div>
                    <div class="progress register-progress">
                        <div class="progress-bar bg-primary-app" role="progressbar" style="width: 33%" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                
                <!-- Form Title -->
                <div class="form-title">
                    <h2 class="text-primary-app fw-bold">สมัครใช้งานระบบ</h2>
                    <p class="text-muted">กรอกข้อมูลเพื่อสร้างบัญชีใหม่สำหรับระบบติดตามพฤติกรรมวินัยนักเรียน</p>
                </div>
                
                <!-- Registration Form -->
                <form id="registrationForm" class="needs-validation" method="POST" action="{{ route('register') }}" novalidate>
                    @csrf
                    <!-- Step 1: Select Role -->
                    <div class="form-step active" id="step1">
                        <h5 class="section-heading fw-semibold mb-4">เลือกประเภทผู้ใช้งาน</h5>
                        
                        <div class="row g-3 mb-4 role-cards-container">
                            <!-- Teacher Role -->
                            <div class="col-md-4">
                                <div class="card role-card" data-role="teacher">
                                    <div class="card-body text-center p-4">
                                        <div class="role-icon bg-white">
                                            <img src="{{ asset('images/teacher.png') }}" alt="ครู" class="img-fluid role-image">
                                        </div>
                                        <h5 class="card-title mb-0">ครู</h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Student Role -->
                            <div class="col-md-4">
                                <div class="card role-card" data-role="student">
                                    <div class="card-body text-center p-4">
                                        <div class="role-icon bg-white">
                                            <img src="{{ asset('images/student.png') }}" alt="นักเรียน" class="img-fluid role-image">
                                        </div>
                                        <h5 class="card-title mb-0">นักเรียน</h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Parent Role -->
                            <div class="col-md-4">
                                <div class="card role-card" data-role="parent">
                                    <div class="card-body text-center p-4">
                                        <div class="role-icon bg-white">
                                            <img src="{{ asset('images/parental.png') }}" alt="ผู้ปกครอง" class="img-fluid role-image">
                                        </div>
                                        <h5 class="card-title mb-0">ผู้ปกครอง</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" id="selectedRole" name="role" required>
                        <div class="invalid-feedback text-center mb-3" id="roleError">
                            กรุณาเลือกประเภทผู้ใช้งาน
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-accent-app btn-lg rounded-pill fw-medium next-step">
                                ถัดไป <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                            <a href="/" class="btn btn-link text-muted">มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</a>
                        </div>
                    </div>
                    
                    <!-- Step 2: Role-Specific Information -->
                    <div class="form-step" id="step2">
                        <!-- Teacher-specific fields -->
                        <div class="role-fields" id="teacher-fields">
                            <h5 class="section-heading fw-semibold">ข้อมูลครู</h5>
                            
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="employee_code" name="employee_code" placeholder="รหัสพนักงาน" required>
                                    <label for="employee_code">รหัสพนักงาน</label>
                                    <div class="invalid-feedback">กรุณากรอกรหัสพนักงาน</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="position" name="position" placeholder="ตำแหน่ง">
                                    <label for="position">ตำแหน่ง</label>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="department" name="department" required>
                                            <option value="" selected disabled>เลือกกลุ่มสาระ</option>
                                            <option value="ภาษาไทย">ภาษาไทย</option>
                                            <option value="คณิตศาสตร์">คณิตศาสตร์</option>
                                            <option value="วิทยาศาสตร์">วิทยาศาสตร์</option>
                                            <option value="สังคมศึกษา">สังคมศึกษา</option>
                                            <option value="ภาษาต่างประเทศ">ภาษาต่างประเทศ</option>
                                            <option value="สุขศึกษาและพลศึกษา">สุขศึกษาและพลศึกษา</option>
                                            <option value="ศิลปะ">ศิลปะ</option>
                                            <option value="การงานอาชีพ">การงานอาชีพ</option>
                                        </select>
                                        <label for="department">กลุ่มสาระการเรียนรู้</label>
                                        <div class="invalid-feedback">กรุณาเลือกกลุ่มสาระ</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="major" name="major" placeholder="วิชาเอก">
                                        <label for="major">วิชาเอก</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_homeroom_teacher" name="is_homeroom_teacher">
                                    <label class="form-check-label" for="is_homeroom_teacher">เป็นครูประจำชั้น</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Student-specific fields -->
                        <div class="role-fields" id="student-fields">
                            <h5 class="section-heading fw-semibold">ข้อมูลนักเรียน</h5>
                            
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="student_code" name="student_code" placeholder="รหัสนักเรียน" data-required="true">
                                    <label for="student_code">รหัสนักเรียน</label>
                                    <div class="invalid-feedback">กรุณากรอกรหัสนักเรียน</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="class_id" name="class_id" required>
                                            <option value="" selected disabled>เลือกระดับชั้น</option>
                                            <option value="ม.1/1">ม.1/1</option>
                                            <option value="ม.1/2">ม.1/2</option>
                                            <option value="ม.1/3">ม.1/3</option>
                                            <option value="ม.2/1">ม.2/1</option>
                                            <option value="ม.2/2">ม.2/2</option>
                                            <option value="ม.2/3">ม.2/3</option>
                                            <!-- เพิ่มตัวเลือกระดับชั้น/ห้องอื่นๆ -->
                                        </select>
                                        <label for="class_id">ระดับชั้น/ห้อง</label>
                                        <div class="invalid-feedback">กรุณาเลือกระดับชั้น/ห้อง</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="academic_year" name="academic_year" required>
                                            <option value="" selected disabled>เลือกปีการศึกษา</option>
                                            <option value="2566">2566</option>
                                            <option value="2567">2567</option>
                                            <option value="2568">2568</option>
                                        </select>
                                        <label for="academic_year">ปีการศึกษา</label>
                                        <div class="invalid-feedback">กรุณาเลือกปีการศึกษา</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label d-block">เพศ</label>
                                <div class="gender-group">
                                    <div class="gender-option">
                                        <input class="form-check-input" type="radio" name="gender" id="male" value="male" required>
                                        <label for="male">ชาย</label>
                                    </div>
                                    <div class="gender-option">
                                        <input class="form-check-input" type="radio" name="gender" id="female" value="female" required>
                                        <label for="female">หญิง</label>
                                    </div>
                                    <div class="gender-option">
                                        <input class="form-check-input" type="radio" name="gender" id="other" value="other" required>
                                        <label for="other">อื่นๆ</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback">กรุณาเลือกเพศ</div>
                            </div>
                        </div>
                        
                        <!-- Parent-specific fields -->
                        <div class="role-fields" id="parent-fields">
                            <h5 class="section-heading fw-semibold">ข้อมูลผู้ปกครอง</h5>
                            
                            <div class="mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="relationship_to_student" name="relationship_to_student" data-required="true">
                                        <option value="" selected disabled>เลือกความสัมพันธ์</option>
                                        <option value="บิดา">บิดา</option>
                                        <option value="มารดา">มารดา</option>
                                        <option value="พี่">พี่</option>
                                        <option value="ลุง/อา">ลุง/อา</option>
                                        <option value="ป้า/น้า">ป้า/น้า</option>
                                        <option value="ปู่/ตา">ปู่/ตา</option>
                                        <option value="ย่า/ยาย">ย่า/ยาย</option>
                                        <option value="อื่นๆ">อื่นๆ</option>
                                    </select>
                                    <label for="relationship_to_student">ความสัมพันธ์กับนักเรียน</label>
                                    <div class="invalid-feedback">กรุณาเลือกความสัมพันธ์</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="parent_phone" name="parent_phone" placeholder="เบอร์โทรศัพท์" required>
                                    <label for="parent_phone">เบอร์โทรศัพท์</label>
                                    <div class="invalid-feedback">กรุณากรอกเบอร์โทรศัพท์</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="line_id" name="line_id" placeholder="Line ID">
                                    <label for="line_id">Line ID (ถ้ามี)</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">วิธีติดต่อที่สะดวก</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="contact_phone" name="preferred_contact_method[]" value="phone">
                                    <label class="form-check-label" for="contact_phone">โทรศัพท์</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="contact_line" name="preferred_contact_method[]" value="line">
                                    <label class="form-check-label" for="contact_line">Line</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="contact_email" name="preferred_contact_method[]" value="email">
                                    <label class="form-check-label" for="contact_email">อีเมล</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="button" class="btn btn-accent-app btn-lg rounded-pill fw-medium next-step">
                                ถัดไป <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary rounded-pill fw-medium prev-step">
                                <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
                            </button>
                        </div>
                    </div>
                    
                    <!-- Step 3: General User Information -->
                    <div class="form-step" id="step3">
                        <h5 class="section-heading fw-semibold">ข้อมูลผู้ใช้ทั่วไป</h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="name_prefix" name="name_prefix" required>
                                        <option value="" selected disabled>เลือกคำนำหน้า</option>
                                        <option value="นาย">นาย</option>
                                        <option value="นาง">นาง</option>
                                        <option value="นางสาว">นางสาว</option>
                                        <option value="ด.ช.">ด.ช.</option>
                                        <option value="ด.ญ.">ด.ญ.</option>
                                        <option value="อื่นๆ">อื่นๆ</option>
                                    </select>
                                    <label for="name_prefix">คำนำหน้า</label>
                                    <div class="invalid-feedback">กรุณาเลือกคำนำหน้า</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="ชื่อจริง" required>
                                    <label for="first_name">ชื่อจริง</label>
                                    <div class="invalid-feedback">กรุณากรอกชื่อจริง</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="นามสกุล" required>
                                    <label for="last_name">นามสกุล</label>
                                    <div class="invalid-feedback">กรุณากรอกนามสกุล</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="อีเมล" required>
                                <label for="email">อีเมล</label>
                                <div class="invalid-feedback">กรุณากรอกอีเมลให้ถูกต้อง</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="phone" name="phone_number" placeholder="เบอร์โทรศัพท์" required>
                                <label for="phone">เบอร์โทรศัพท์</label>
                                <div class="invalid-feedback">กรุณากรอกเบอร์โทรศัพท์</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                                <label for="birthdate">วันเกิด</label>
                                <div class="invalid-feedback">กรุณาเลือกวันเกิด</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" name="password" placeholder="รหัสผ่าน" required minlength="8">
                                <label for="password">รหัสผ่าน (อย่างน้อย 8 ตัวอักษร)</label>
                                <div class="invalid-feedback">กรุณากรอกรหัสผ่านอย่างน้อย 8 ตัวอักษร</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="ยืนยันรหัสผ่าน" required minlength="8">
                                <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
                                <div class="invalid-feedback">กรุณายืนยันรหัสผ่าน</div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                ฉันยอมรับ <a href="#" class="text-primary-app">ข้อกำหนดและเงื่อนไขการใช้งาน</a>
                            </label>
                            <div class="invalid-feedback">คุณต้องยอมรับข้อกำหนดก่อนสมัครสมาชิก</div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-accent-app btn-lg rounded-pill fw-medium">
                                <i class="fas fa-user-plus me-2"></i> สร้างบัญชีผู้ใช้
                            </button>
                            <button type="button" class="btn btn-outline-secondary rounded-pill fw-medium prev-step">
                                <i class="fas fa-arrow-left me-1"></i> ย้อนกลับ
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast for notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">แจ้งเตือน</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                กรุณาตรวจสอบข้อมูลที่กรอก
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Bootstrap JS bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize particles background for desktop only
            if (window.innerWidth > 992) {
                particlesJS("particles-js", {
                    particles: {
                        number: { value: 80, density: { enable: true, value_area: 800 } },
                        color: { value: "#95A4D8" },
                        shape: { type: "circle" },
                        opacity: { value: 0.5, random: true },
                        size: { value: 3, random: true },
                        line_linked: { enable: true, distance: 150, color: "#1020AD", opacity: 0.2, width: 1 },
                        move: { enable: true, speed: 1, direction: "none", random: true, straight: false, out_mode: "out" }
                    },
                    interactivity: {
                        detect_on: "canvas",
                        events: { onhover: { enable: true, mode: "grab" }, onclick: { enable: true, mode: "push" } },
                        modes: { grab: { distance: 140, line_linked: { opacity: 0.3 } }, push: { particles_nb: 3 } }
                    },
                    retina_detect: true
                });
            }
            
            // Form multi-step management
            const form = document.getElementById('registrationForm');
            const steps = document.querySelectorAll('.form-step');
            const roleCards = document.querySelectorAll('.role-card');
            const roleFields = document.querySelectorAll('.role-fields');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            const selectedRoleInput = document.getElementById('selectedRole');
            const progressBar = document.querySelector('.progress-bar');
            const progressText = document.getElementById('progress-text');
            const progressPercentage = document.getElementById('progress-percentage');
            const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
            
            let currentStep = 0;
            
            // Handle role card selection
            roleCards.forEach(card => {
                card.addEventListener('click', () => {
                    // Remove selected class from all cards
                    roleCards.forEach(c => c.classList.remove('selected'));
                    
                    // Add selected class to current card
                    card.classList.add('selected');
                    
                    // Update hidden input value
                    selectedRoleInput.value = card.dataset.role;
                    
                    // Hide role error if any
                    document.getElementById('roleError').style.display = 'none';
                });
            });
            
            // เพิ่มอนิเมชั่นการเปลี่ยนสเต็ป
            function goToStep(newStep) {
                // บันทึกการอ้างอิงสเต็ปปัจจุบัน
                const currentStepElement = steps[currentStep];
                
                // บันทึกสเต็ปใหม่
                const newStepElement = steps[newStep];
                
                // ลบคลาส active จากสเต็ปปัจจุบันและใส่อนิเมชั่นออก
                currentStepElement.classList.add('fade-out');
                
                // รอให้อนิเมชั่นออกทำงานเสร็จก่อนที่จะเปลี่ยนสเต็ป
                setTimeout(() => {
                    currentStepElement.classList.remove('active', 'fade-out');
                    
                    // อัพเดทค่าสเต็ปปัจจุบัน
                    currentStep = newStep;
                    
                    // อัพเดทแถบความคืบหน้า
                    updateProgress();
                    
                    // แสดงสเต็ปใหม่และใส่อนิเมชั่นเข้า
                    newStepElement.classList.add('active');
                }, 300);
            }
            
            // แทนที่การจัดการคลิกปุ่ม next
            nextButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // ตรวจสอบการเลือก role ในสเต็ปแรก
                    if (currentStep === 0 && !selectedRoleInput.value) {
                        document.getElementById('roleError').style.display = 'block';
                        return;
                    }
                    
                    // จัดการสเต็ปที่ 2 แสดงฟิลด์ตาม role
                    if (currentStep === 0) {
                        const role = selectedRoleInput.value;
                        
                        roleFields.forEach(field => {
                            field.style.display = 'none';
                        });
                        
                        // แสดงฟิลด์สำหรับ role ที่เลือก
                        document.getElementById(`${role}-fields`).style.display = 'block';
                    }
                    
                    // ไปยังสเต็ปถัดไป
                    goToStep(currentStep + 1);
                });
            });
            
            // แทนที่การจัดการคลิกปุ่ม prev
            prevButtons.forEach(button => {
                button.addEventListener('click', () => {
                    goToStep(currentStep - 1);
                });
            });
            
            // Update progress bar and text
            function updateProgress() {
                const totalSteps = steps.length;
                const progress = Math.round(((currentStep + 1) / totalSteps) * 100);
                
                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);
                progressText.textContent = `ขั้นตอนที่ ${currentStep + 1} จาก ${totalSteps}`;
                progressPercentage.textContent = `${progress}%`;
            }
            
            // Form validation
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                // ดึงบทบาทที่เลือกไว้
                const selectedRole = selectedRoleInput.value;
                
                // ยกเลิกการตรวจสอบความถูกต้องในฟิลด์ที่ไม่เกี่ยวข้องกับบทบาท
                disableIrrelevantFields(selectedRole);
                
                if (!form.checkValidity()) {
                    event.stopPropagation();
                    errorToast.show();
                    
                    // Log validation errors to console
                    const invalidFields = form.querySelectorAll(':invalid');
                    console.group('Form Validation Errors:');
                    invalidFields.forEach(field => {
                        console.error(`Field: ${field.name || field.id} - Error: ${field.validationMessage}`);
                    });
                    console.groupEnd();
                } else {
                    // แสดง loading overlay
                    document.getElementById('loadingOverlay').classList.add('show');
                    
                    // ส่งฟอร์มจริง
                    form.submit();
                }
                
                form.classList.add('was-validated');
            });
            
            // ฟังก์ชันยกเลิกการตรวจสอบความถูกต้องในฟิลด์ที่ไม่เกี่ยวข้อง
            function disableIrrelevantFields(role) {
                // เริ่มต้นโดยยกเลิกการตรวจสอบทุกฟิลด์เฉพาะของแต่ละบทบาท
                document.querySelectorAll('#teacher-fields input, #teacher-fields select, #teacher-fields textarea').forEach(field => {
                    field.disabled = true;
                    field.required = false;
                });
                
                document.querySelectorAll('#student-fields input, #student-fields select, #student-fields textarea').forEach(field => {
                    field.disabled = true;
                    field.required = false;
                });
                
                document.querySelectorAll('#parent-fields input, #parent-fields select, #parent-fields textarea').forEach(field => {
                    field.disabled = true;
                    field.required = false;
                });
                
                // เปิดใช้งานเฉพาะฟิลด์ที่เกี่ยวข้องกับบทบาทที่เลือกและฟิลด์ทั่วไป
                if (role === 'teacher') {
                    document.querySelectorAll('#teacher-fields input, #teacher-fields select, #teacher-fields textarea').forEach(field => {
                        field.disabled = false;
                        if (field.hasAttribute('data-required')) {
                            field.required = true;
                        }
                    });
                } else if (role === 'student') {
                    document.querySelectorAll('#student-fields input, #student-fields select, #student-fields textarea').forEach(field => {
                        field.disabled = false;
                        if (field.hasAttribute('data-required')) {
                            field.required = true;
                        }
                    });
                } else if (role === 'parent') {
                    document.querySelectorAll('#parent-fields input, #parent-fields select, #parent-fields textarea').forEach(field => {
                        field.disabled = false;
                        if (field.hasAttribute('data-required')) {
                            field.required = true;
                        }
                    });
                }
            }
            
            // Password confirmation validation
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');
            
            passwordConfirmation.addEventListener('input', function() {
                if (password.value !== passwordConfirmation.value) {
                    passwordConfirmation.setCustomValidity('รหัสผ่านไม่ตรงกัน');
                } else {
                    passwordConfirmation.setCustomValidity('');
                }
            });
            
            // เพิ่มตัวแสดงความแข็งแรงของรหัสผ่าน
            const passwordField = document.getElementById('password');
            const passwordStrength = document.createElement('div');
            passwordStrength.className = 'password-strength';
            passwordField.parentNode.appendChild(passwordStrength);
            
            passwordField.addEventListener('input', function() {
                const val = this.value;
                const strength = calculatePasswordStrength(val);
                
                passwordStrength.style.display = 'block';
                passwordStrength.style.width = `${strength}%`;
                
                if (strength < 40) {
                    passwordStrength.style.backgroundColor = '#dc3545'; // อ่อน
                } else if (strength < 70) {
                    passwordStrength.style.backgroundColor = '#ffc107'; // ปานกลาง
                } else {
                    passwordStrength.style.backgroundColor = '#198754'; // แข็งแรง
                }
            });
            
            function calculatePasswordStrength(password) {
                let strength = 0;
                
                // ความยาว
                if (password.length >= 8) strength += 20;
                if (password.length >= 12) strength += 10;
                
                // ความซับซ้อน
                if (/[a-z]/.test(password)) strength += 10;
                if (/[A-Z]/.test(password)) strength += 15;
                if (/[0-9]/.test(password)) strength += 15;
                if (/[^a-zA-Z0-9]/.test(password)) strength += 20;
                
                // ป้องกันการเกิน 100%
                return Math.min(100, strength);
            }
            
            // เพิ่มเอฟเฟกต์ hover สำหรับ role cards
            roleCards.forEach(card => {
                const icon = card.querySelector('.role-icon');
                
                card.addEventListener('mouseenter', () => {
                    icon.classList.add('animate-float');
                });
                
                card.addEventListener('mouseleave', () => {
                    icon.classList.remove('animate-float');
                });
            });

            // เพิ่มเอฟเฟกต์ hover สำหรับ role cards
            roleCards.forEach(card => {
                const imageElement = card.querySelector('.role-image');
                
                card.addEventListener('mouseenter', () => {
                    imageElement && imageElement.classList.add('animate-float');
                });
                
                card.addEventListener('mouseleave', () => {
                    imageElement && imageElement.classList.remove('animate-float');
                });
            });
        });
    </script>
</body>
</html>