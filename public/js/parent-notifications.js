/**
 * JavaScript สำหรับจัดการการแจ้งเตือนในหน้า Parent Dashboard
 */

// ฟังก์ชันสำหรับดึง CSRF Token อย่างปลอดภัย
function getCSRFToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // หาจาก hidden input field (fallback)
    const hiddenInput = document.querySelector('input[name="_token"]');
    if (hiddenInput) {
        return hiddenInput.value;
    }
    
    // ถ้าไม่เจอเลยให้แสดงข้อความแจ้งเตือน
    console.warn('CSRF token not found. Please add <meta name="csrf-token" content="{{ csrf_token() }}"> to your HTML head.');
    return '';
}

// ฟังก์ชันสำหรับทำเครื่องหมายการแจ้งเตือนว่าอ่านแล้ว
function markAsRead(notificationId) {
    const csrfToken = getCSRFToken();
    
    if (!csrfToken) {
        console.error('Cannot perform action: CSRF token is missing');
        alert('เกิดข้อผิดพลาดในการยืนยันตัวตน กรุณาโหลดหน้าใหม่');
        return;
    }

    fetch(`/api/parent/notifications/${notificationId}/read`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // อัปเดต UI
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('notification-unread');
                
                // อัปเดต badge
                const badge = notificationElement.querySelector('.badge');
                if (badge) {
                    badge.className = 'badge bg-secondary';
                    badge.textContent = 'อ่านแล้ว';
                }
                
                // ลบจุดแจ้งเตือน
                const dot = notificationElement.querySelector('.notification-dot');
                if (dot) {
                    dot.remove();
                }
            }
            
            // อัปเดตจำนวนการแจ้งเตือนที่ยังไม่อ่าน
            updateUnreadCount();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        alert('เกิดข้อผิดพลาดในการอัปเดตสถานะการแจ้งเตือน');
    });
}

// ฟังก์ชันสำหรับแสดงการแจ้งเตือนทั้งหมด
function showAllNotifications() {
    // สร้าง modal หรือเปิดหน้าใหม่สำหรับแสดงการแจ้งเตือนทั้งหมด
    const modal = new bootstrap.Modal(document.getElementById('allNotificationsModal') || createNotificationsModal());
    loadAllNotifications();
    modal.show();
}

// ฟังก์ชันสร้าง modal สำหรับแสดงการแจ้งเตือนทั้งหมด
function createNotificationsModal() {
    const modalHTML = `
        <div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title">การแจ้งเตือนทั้งหมด</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body notification-modal">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary active" data-filter="all">ทั้งหมด</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-filter="unread">ยังไม่อ่าน</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-filter="read">อ่านแล้ว</button>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="markAllAsRead()">
                                <i class="fas fa-check-double me-1"></i> อ่านทั้งหมด
                            </button>
                        </div>
                        <div id="allNotificationsList">
                            <!-- Notifications will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    return document.getElementById('allNotificationsModal');
}

// ฟังก์ชันโหลดการแจ้งเตือนทั้งหมด
function loadAllNotifications(filter = 'all') {
    const container = document.getElementById('allNotificationsList');
    if (!container) return;
    
    // แสดง loading
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">กำลังโหลด...</span>
            </div>
            <p class="mt-2 text-muted">กำลังโหลดการแจ้งเตือน...</p>
        </div>
    `;
    
    fetch(`/api/parent/notifications?filter=${filter}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderAllNotifications(data.notifications);
            } else {
                container.innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            container.innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-wifi fa-2x mb-2"></i>
                    <p>ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้</p>
                </div>
            `;
        });
}

// ฟังก์ชันแสดงการแจ้งเตือนทั้งหมด
function renderAllNotifications(notifications) {
    const container = document.getElementById('allNotificationsList');
    if (!container) return;
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                <p class="text-muted">ไม่มีการแจ้งเตือน</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = notifications.map(notification => `
        <div class="notification-detail ${!notification.is_read ? 'notification-unread' : ''}" 
             data-notification-id="${notification.id}">
            <div class="notification-meta">
                <div class="d-flex align-items-center">
                    <div class="bg-${notification.type} rounded-circle me-2 d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px;">
                        <i class="${notification.icon} text-white"></i>
                    </div>
                    <h6 class="mb-0">${notification.title}</h6>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge ${notification.badge_class} me-2">${notification.badge_text}</span>
                    <small class="text-muted">${notification.date}</small>
                </div>
            </div>
            <p class="mb-2">${notification.message}</p>
            ${!notification.is_read ? `
                <button class="btn btn-sm btn-outline-primary" onclick="markAsRead(${notification.id})">
                    <i class="fas fa-eye me-1"></i> ทำเครื่องหมายว่าอ่านแล้ว
                </button>
            ` : ''}
        </div>
    `).join('');
}

// ฟังก์ชันทำเครื่องหมายอ่านทั้งหมด
function markAllAsRead() {
    const csrfToken = getCSRFToken();
    
    if (!csrfToken) {
        console.error('Cannot perform action: CSRF token is missing');
        alert('เกิดข้อผิดพลาดในการยืนยันตัวตน กรุณาโหลดหน้าใหม่');
        return;
    }

    fetch('/api/parent/notifications/mark-all-read', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // โหลดการแจ้งเตือนใหม่
            loadAllNotifications();
            updateUnreadCount();
            
            // อัปเดต UI ในหน้าหลัก
            document.querySelectorAll('.notification-unread').forEach(item => {
                item.classList.remove('notification-unread');
                const badge = item.querySelector('.badge');
                if (badge) {
                    badge.className = 'badge bg-secondary';
                    badge.textContent = 'อ่านแล้ว';
                }
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.remove();
            });
        }
    })
    .catch(error => {
        console.error('Error marking all as read:', error);
        alert('เกิดข้อผิดพลาดในการอัปเดตสถานะการแจ้งเตือน');
    });
}

// ฟังก์ชันอัปเดตจำนวนการแจ้งเตือนที่ยังไม่อ่าน
function updateUnreadCount() {
    fetch('/api/parent/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.querySelector('.notification-badge .badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error updating unread count:', error);
        });
}

// เพิ่มฟังก์ชันนี้ลงในไฟล์
function loadMoreNotifications() {
    // ฟังก์ชันสำหรับโหลดการแจ้งเตือนเพิ่มเติม
    const modal = new bootstrap.Modal(document.getElementById('allNotificationsModal') || createNotificationsModal());
    loadAllNotifications();
    modal.show();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // ตรวจสอบว่ามี CSRF token หรือไม่
    const csrfToken = getCSRFToken();
    if (!csrfToken) {
        console.warn('CSRF token not found. Some features may not work properly.');
    }

    // Filter buttons in modal
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-filter]')) {
            // อัปเดต active state
            document.querySelectorAll('[data-filter]').forEach(btn => {
                btn.classList.remove('active');
            });
            e.target.classList.add('active');
            
            // โหลดข้อมูลตาม filter
            loadAllNotifications(e.target.dataset.filter);
        }
    });
    
    // โหลดจำนวนการแจ้งเตือนที่ยังไม่อ่าน
    updateUnreadCount();
    
    // ตั้งค่าการอัปเดตอัตโนมัติทุก 30 วินาที
    setInterval(updateUnreadCount, 30000);
});