<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://ui-avatars.com https://placehold.co; font-src 'self' data: https: https://fonts.gstatic.com;">
    <title>@yield('title', 'ระบบจัดการคะแนนพฤติกรรม')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- App CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
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
        
        .card {
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .footer {
            background: var(--dark-app);
            color: white;
            margin-top: auto;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary-app" href="{{ route('dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>
                ระบบจัดการคะแนนพฤติกรรม
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        @if(Auth::user()->users_role === 'student')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('student.dashboard') }}">
                                    <i class="fas fa-home me-1"></i>แดชบอร์ด
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('student.settings') }}">
                                    <i class="fas fa-user-cog me-1"></i>ตั้งค่าบัญชี
                                </a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                {{ Auth::user()->users_first_name }}
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

    <!-- Main Content -->
    <main style="margin-top: 80px; margin-bottom: 60px;">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer py-3 mt-auto">
        <div class="container text-center">
            <small>&copy; {{ date('Y') }} ระบบจัดการคะแนนพฤติกรรม. สงวนลิขสิทธิ์.</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
