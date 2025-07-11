@extends('layouts.app')

@section('title', 'ตั้งค่าบัญชีผู้ใช้')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle bg-primary-app me-3">
                            <i class="fas fa-user-cog text-white"></i>
                        </div>
                        <div>
                            <h4 class="card-title mb-1">ตั้งค่าบัญชีผู้ใช้</h4>
                            <p class="text-muted mb-0">จัดการข้อมูลส่วนตัวและความปลอดภัยของบัญชี</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <!-- Profile Information -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2 text-primary-app"></i>
                                ข้อมูลส่วนตัว
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="profile-info">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted">ชื่อ-นามสกุล</label>
                                    <p class="mb-0">{{ $user->users_name_prefix }} {{ $user->users_first_name }} {{ $user->users_last_name }}</p>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted">อีเมล</label>
                                    <p class="mb-0">{{ $user->users_email }}</p>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted">เบอร์โทรศัพท์</label>
                                    <p class="mb-0">{{ $user->users_phone_number }}</p>
                                </div>
                                
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted">วันเกิด</label>
                                    <p class="mb-0">{{ $user->users_birthdate ? $user->users_birthdate->format('d/m/Y') : '-' }}</p>
                                </div>
                                
                                @if($user->student)
                                <div class="info-item">
                                    <label class="form-label text-muted">รหัสนักเรียน</label>
                                    <p class="mb-0">{{ $user->student->students_student_code }}</p>
                                </div>
                                @endif
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>หากต้องการแก้ไขข้อมูลส่วนตัว กรุณาติดต่อครูประจำชั้นหรือเจ้าหน้าที่</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-lock me-2 text-warning"></i>
                                เปลี่ยนรหัสผ่าน
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="changePasswordForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">รหัสผ่านปัจจุบัน</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">รหัสผ่านใหม่</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร</div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" minlength="8" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Password Strength Indicator -->
                                <div class="password-strength-container mb-3" style="display: none;">
                                    <label class="form-label text-muted">ความแข็งแรงของรหัสผ่าน</label>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" id="passwordStrengthBar" role="progressbar"></div>
                                    </div>
                                    <small class="text-muted" id="passwordStrengthText"></small>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <small>
                                        <strong>ข้อแนะนำสำหรับรหัสผ่านที่ปลอดภัย:</strong><br>
                                        • ใช้ตัวอักษรพิมพ์ใหญ่และพิมพ์เล็ก<br>
                                        • ใส่ตัวเลขและอักขระพิเศษ<br>
                                        • ไม่ใช้ข้อมูลส่วนตัวที่เดาได้ง่าย
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-primary-app w-100" id="changePasswordBtn">
                                    <i class="fas fa-key me-2"></i>
                                    เปลี่ยนรหัสผ่าน
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2 text-success"></i>
                        ข้อมูลความปลอดภัย
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="security-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-success me-3">
                                        <i class="fas fa-check text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">บัญชีได้รับการยืนยันแล้ว</h6>
                                        <small class="text-muted">บัญชีของคุณได้รับการตรวจสอบโดยระบบ</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="security-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-info me-3">
                                        <i class="fas fa-clock text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">การเข้าสู่ระบบล่าสุด</h6>
                                        <small class="text-muted">{{ now()->format('d/m/Y เวลา H:i น.') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>หากคุณสงสัยว่ามีการเข้าถึงบัญชีโดยไม่ได้รับอนุญาต</strong><br>
                        กรุณาเปลี่ยนรหัสผ่านทันทีและแจ้งให้ครูประจำชั้นทราบ
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifications -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="successToast" class="toast" role="alert">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">สำเร็จ</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="successMessage"></div>
    </div>

    <div id="errorToast" class="toast" role="alert">
        <div class="toast-header bg-danger text-white">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong class="me-auto">ข้อผิดพลาด</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="errorMessage"></div>
    </div>
</div>

<style>
.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.profile-info .info-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.profile-info .info-item:last-child {
    border-bottom: none;
}

.security-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.password-strength-container .progress-bar {
    transition: all 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('new_password_confirmation');
    const strengthContainer = document.querySelector('.password-strength-container');
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');

    // Password strength checker
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        if (password.length > 0) {
            strengthContainer.style.display = 'block';
            updatePasswordStrength(strength);
        } else {
            strengthContainer.style.display = 'none';
        }
    });

    // Password confirmation validation
    confirmPasswordInput.addEventListener('input', function() {
        const password = newPasswordInput.value;
        const confirmation = this.value;
        
        if (confirmation.length > 0) {
            if (password !== confirmation) {
                this.classList.add('is-invalid');
                this.nextElementSibling.textContent = 'รหัสผ่านไม่ตรงกัน';
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        } else {
            this.classList.remove('is-invalid', 'is-valid');
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        changePassword();
    });
});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function calculatePasswordStrength(password) {
    let strength = 0;
    
    // Length
    if (password.length >= 8) strength += 25;
    if (password.length >= 12) strength += 15;
    
    // Character types
    if (/[a-z]/.test(password)) strength += 15;
    if (/[A-Z]/.test(password)) strength += 15;
    if (/[0-9]/.test(password)) strength += 15;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 15;
    
    return Math.min(100, strength);
}

function updatePasswordStrength(strength) {
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');
    
    strengthBar.style.width = strength + '%';
    
    if (strength < 40) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'อ่อน';
    } else if (strength < 70) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'ปานกลาง';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'แข็งแรง';
    }
}

function changePassword() {
    const form = document.getElementById('changePasswordForm');
    const formData = new FormData(form);
    const btn = document.getElementById('changePasswordBtn');
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังเปลี่ยนรหัสผ่าน...';
    
    // Clear previous validation
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    fetch('{{ route("student.password.change") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            form.reset();
            document.querySelector('.password-strength-container').style.display = 'none';
            
            // Redirect to login after 2 seconds
            setTimeout(() => {
                window.location.href = '{{ route("login") }}';
            }, 2000);
        } else {
            showToast('error', data.message);
            
            // Show field-specific errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.getElementById(field);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = data.errors[field][0];
                        }
                    }
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง');
    })
    .finally(() => {
        // Restore button
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-key me-2"></i>เปลี่ยนรหัสผ่าน';
    });
}

function showToast(type, message) {
    const toast = document.getElementById(type + 'Toast');
    const messageElement = document.getElementById(type + 'Message');
    
    messageElement.textContent = message;
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}
</script>
@endsection
