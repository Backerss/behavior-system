/**
 * Parent Dashboard JavaScript - Optimized Version
 * Enhanced performance, animations, and interaction
 */

// Use DOMContentLoaded for initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the application with proper loading sequence
    initializeApp();
});

/**
 * Core initialization function with proper sequence
 */
function initializeApp() {
    // Show loading animation
    const loadingOverlay = createLoadingOverlay();
    document.body.appendChild(loadingOverlay);
    
    // Initialize in sequence with proper timing
    setTimeout(() => {
        // Add page transition class before initializing components
        document.querySelector('.app-container')?.classList.add('page-transition');
        
        // Initialize core components in optimized order
        const components = [
            initializeEventListeners,
            initializeAnimations,
            initializeCharts,
            initializeThemeToggle,
            enhanceNavbar,
            setupAdvancedFeatures
        ];
        
        // Execute initialization functions in sequence
        components.forEach(initFn => {
            try {
                initFn();
            } catch(err) {
                console.warn(`Failed to initialize component: ${err.message}`);
            }
        });
        
        // Remove loading overlay with smooth fade transition
        fadeOutElement(loadingOverlay, 500, () => {
            loadingOverlay.remove();
        });
    }, 800);
}

/**
 * Creates a loading overlay with spinner
 */
function createLoadingOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="loading-spinner"></div>';
    return overlay;
}

/**
 * Utility function to fade out an element
 */
function fadeOutElement(element, duration = 300, callback = null) {
    if (!element) return;
    
    element.style.opacity = '0';
    element.style.transition = `opacity ${duration}ms ease`;
    
    setTimeout(() => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    }, duration);
}

/**
 * Initialize optimized event listeners with proper delegation
 */
function initializeEventListeners() {
    // Use event delegation for common parent elements
    setupStudentTabsEventListeners();
    setupViewDetailsEventListeners();
    setupBackToAllEventListeners();
    setupNavigationEventListeners();
    setupContactTeacherEventListeners();
}

/**
 * Setup student tabs event listeners with optimization
 */
function setupStudentTabsEventListeners() {
    const tabsContainer = document.querySelector('.student-tabs');
    if (!tabsContainer) return;
    
    // Use event delegation for better performance
    tabsContainer.addEventListener('click', function(e) {
        // Find closest tab if clicking on a child element
        const tab = e.target.closest('.student-tab');
        if (!tab) return;
        
        // Remove active class from all tabs
        document.querySelectorAll('.student-tab').forEach(t => 
            t.classList.remove('active'));
        
        // Add active class to clicked tab
        tab.classList.add('active');
        
        // Handle student view change
        const studentId = tab.getAttribute('data-student');
        studentId === 'all' ? showAllStudentsView() : showIndividualStudentView(studentId);
    });
}

/**
 * Setup view details event listeners with optimization
 */
function setupViewDetailsEventListeners() {
    // Use event delegation on parent container
    const container = document.querySelector('.desktop-grid-summary');
    if (!container) return;
    
    container.addEventListener('click', function(e) {
        const link = e.target.closest('.view-details-link');
        if (!link) return;
        
        const studentId = link.getAttribute('data-student');
        if (!studentId) return;
        
        showIndividualStudentView(studentId);
        
        // Activate corresponding tab
        document.querySelectorAll('.student-tab').forEach(t => 
            t.classList.remove('active'));
        
        const tab = document.querySelector(`.student-tab[data-student="${studentId}"]`);
        if (tab) tab.classList.add('active');
    });
}

/**
 * Setup back to all students button listeners
 */
function setupBackToAllEventListeners() {
    document.querySelectorAll('.back-to-all').forEach(button => {
        button.addEventListener('click', function() {
            showAllStudentsView();
            
            // Activate the "all" tab
            document.querySelectorAll('.student-tab').forEach(t => 
                t.classList.remove('active'));
            
            const allTab = document.querySelector('.student-tab[data-student="all"]');
            if (allTab) allTab.classList.add('active');
        });
    });
}

/**
 * Setup navigation event listeners
 */
function setupNavigationEventListeners() {
    // Bottom navbar (mobile)
    const bottomNavContainer = document.querySelector('.bottom-navbar');
    if (bottomNavContainer) {
        bottomNavContainer.addEventListener('click', function(e) {
            const link = e.target.closest('.nav-link');
            if (!link) return;
            
            document.querySelectorAll('.bottom-navbar .nav-link').forEach(l => {
                l.classList.remove('text-primary-app');
                l.classList.add('text-muted');
            });
            
            link.classList.remove('text-muted');
            link.classList.add('text-primary-app');
        });
    }
    
    // Desktop navigation
    const desktopNavContainer = document.querySelector('.desktop-navbar-menu');
    if (desktopNavContainer) {
        desktopNavContainer.addEventListener('click', function(e) {
            const link = e.target.closest('.desktop-nav-link');
            if (!link) return;
            
            document.querySelectorAll('.desktop-nav-link').forEach(l => 
                l.classList.remove('active'));
            
            link.classList.add('active');
        });
    }
}

/**
 * Setup contact teacher button listeners
 */
function setupContactTeacherEventListeners() {
    const contactBtn = document.querySelector('.contact-teacher-btn');
    if (contactBtn) {
        contactBtn.addEventListener('click', function() {
            showCommunicationModal();
        });
    }
}

/**
 * Show all students summary view with animation
 */
function showAllStudentsView() {
    const allView = document.getElementById('all-students-view');
    const individualView = document.getElementById('individual-student-view');
    
    if (!allView || !individualView) return;
    
    // Apply exit animation to individual view
    individualView.style.opacity = '0';
    individualView.style.transform = 'translateY(10px)';
    
    setTimeout(() => {
        individualView.classList.add('d-none');
        allView.classList.remove('d-none');
        
        // Apply entrance animation to all students view
        setTimeout(() => {
            allView.style.opacity = '1';
            allView.style.transform = 'translateY(0)';
        }, 50);
    }, 300);
    
    // Reset view styles for next transition
    allView.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    allView.style.opacity = '0';
    allView.style.transform = 'translateY(-10px)';
}

/**
 * Show individual student detailed view with animation
 */
function showIndividualStudentView(studentId) {
    const allView = document.getElementById('all-students-view');
    const individualView = document.getElementById('individual-student-view');
    
    if (!allView || !individualView) return;

    // Apply exit animation to all students view
    allView.style.opacity = '0';
    allView.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
        allView.classList.add('d-none');
        individualView.classList.remove('d-none');
        
        // Apply entrance animation to individual view
        setTimeout(() => {
            individualView.style.opacity = '1';
            individualView.style.transform = 'translateY(0)';
        }, 50);
        
        // Show the specific student detail view
        document.querySelectorAll('.student-detail-view').forEach(view => {
            view.classList.add('d-none');
        });
        
        const targetView = document.getElementById(`${studentId}-view`);
        if (targetView) {
            targetView.classList.remove('d-none');
        }
    }, 300);
    
    // Reset view styles for next transition
    individualView.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    individualView.style.opacity = '0';
    individualView.style.transform = 'translateY(10px)';
}

/**
 * Initialize and optimize animations
 */
function initializeAnimations() {
    // Animation queue for better performance
    const animationQueue = [
        {elements: document.querySelectorAll('.notification-item'), delay: 150},
        {elements: document.querySelectorAll('.event-item'), delay: 150},
        {elements: document.querySelectorAll('.student-summary-card'), delay: 200},
        {elements: document.querySelectorAll('.tip-item'), delay: 150}
    ];
    
    // Process animations sequentially for better performance
    let totalDelay = 300;
    
    animationQueue.forEach(item => {
        if (!item.elements.length) return;
        
        item.elements.forEach((element, index) => {
            setTimeout(() => {
                // Add animation class
                element.classList.add('animated');
                
                // Animate progress bars in student cards
                const progressBar = element.querySelector('.progress-bar');
                if (progressBar) {
                    const width = progressBar.getAttribute('aria-valuenow') + '%';
                    progressBar.style.width = width;
                }
            }, totalDelay + (index * item.delay));
        });
        
        totalDelay += item.elements.length * item.delay;
    });
}

/**
 * Initialize charts with optimization
 */
function initializeCharts() {
    // Charts will be initialized dynamically when needed
    // ลบการเรียก initStudentBehaviorChart และ initAttendanceChart ออก
}

/**
 * Initialize theme toggle with smooth transitions
 */
function initializeThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;
    
    themeToggle.addEventListener('click', function() {
        // Toggle dark theme class
        document.body.classList.toggle('dark-theme');
        
        // Save theme preference
        const isDarkTheme = document.body.classList.contains('dark-theme');
        localStorage.setItem('darkTheme', isDarkTheme);
        
        // Update toggle icon with animation
        const icon = this.querySelector('i');
        icon.style.transform = 'rotate(360deg) scale(0.5)';
        
        setTimeout(() => {
            icon.className = isDarkTheme ? 'fas fa-sun' : 'fas fa-moon';
            icon.style.transform = 'rotate(0) scale(1)';
        }, 150);
        
        // Update charts for theme
        updateChartsForTheme(isDarkTheme);
    });
    
    // Apply saved theme preference
    const savedTheme = localStorage.getItem('darkTheme');
    if (savedTheme === 'true') {
        document.body.classList.add('dark-theme');
        themeToggle.querySelector('i').className = 'fas fa-sun';
        updateChartsForTheme(true);
    }
}

/**
 * Update charts for current theme
 */
function updateChartsForTheme(isDarkTheme) {
    const textColor = isDarkTheme ? '#f0f0f0' : '#495057';
    
    // Update charts with new theme colors
    ['studentBehaviorChart', 'attendanceChart'].forEach(chartId => {
        const chart = Chart.getChart(chartId);
        if (!chart) return;
        
        chart.options.plugins.legend.labels.color = textColor;
        chart.update('none'); // Update without animation
    });
}

/**
 * Enhanced navbar with improved animations
 */
function enhanceNavbar() {
    // Add subtle animation to navbar on scroll with requestAnimationFrame
    let lastScrollTop = 0;
    let ticking = false;
    
    window.addEventListener('scroll', function() {
        lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (!ticking) {
            window.requestAnimationFrame(function() {
                handleNavbarScroll(lastScrollTop);
                ticking = false;
            });
            
            ticking = true;
        }
    }, { passive: true });
}

/**
 * Handle navbar scroll animation
 */
function handleNavbarScroll(scrollTop) {
    const navbar = document.querySelector('.desktop-navbar');
    if (!navbar) return;
    
    if (scrollTop > 70) {
        navbar.style.transform = 'translateY(-100%)';
    } else {
        navbar.style.transform = 'translateY(0)';
    }
}

/**
 * Setup advanced features - called after core initialization
 */
function setupAdvancedFeatures() {
    setTimeout(() => {
        // Add these features with a delay to prioritize core UI rendering
        highlightBestPerformingStudent();
        generateParentingTips();
        initializeCommunicationLog();
        addScrollToTopButton();
    }, 1000);
}

/**
 * Highlight the best performing student based on actual data from server
 */
function highlightBestPerformingStudent() {
    const studentCards = document.querySelectorAll('.student-summary-card');
    if (!studentCards.length) return;
    
    let bestScore = 0;
    let bestStudent = null;
    
    // Find highest scoring student from actual data
    studentCards.forEach(card => {
        const badge = card.querySelector('.badge');
        if (!badge) return;
        
        // Extract numeric score from badge text
        const scoreText = badge.textContent.trim();
        const score = parseInt(scoreText.replace(/[^\d]/g, ''));
        
        if (!isNaN(score) && score > bestScore) {
            bestScore = score;
            bestStudent = card;
        }
    });
    
    // Add highlight effect with animation
    if (bestStudent) {
        bestStudent.classList.add('best-performer');
        
        // Create and add crown badge
        const starBadge = document.createElement('div');
        starBadge.className = 'best-performer-badge';
        starBadge.innerHTML = '<i class="fas fa-crown text-warning"></i>';
        starBadge.style.cssText = `
            position: absolute;
            top: -5px;
            right: -5px;
            background: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        `;
        
        const container = bestStudent.querySelector('.d-flex');
        if (container) {
            container.style.position = 'relative';
            container.appendChild(starBadge);
        }
    }
}

/**
 * Generate parenting tips based on actual student data
 */
function generateParentingTips() {
    // ลบข้อมูลตัวอย่าง ให้สร้างคำแนะนำจากข้อมูลจริง
    const studentCards = document.querySelectorAll('.student-summary-card');
    const tips = [];
    
    studentCards.forEach(card => {
        const nameElement = card.querySelector('h4');
        const scoreElement = card.querySelector('.badge');
        const changeElement = card.querySelector('.text-success, .text-danger');
        
        if (nameElement && scoreElement) {
            const name = nameElement.textContent.trim();
            const score = parseInt(scoreElement.textContent.replace(/[^\d]/g, ''));
            
            // สร้างคำแนะนำตามคะแนนจริง
            if (score >= 90) {
                tips.push({
                    icon: 'fas fa-star',
                    color: 'success',
                    message: `${name} มีผลการเรียนดีเยี่ยม ควรสนับสนุนให้เข้าร่วมกิจกรรมเพิ่มเติม`
                });
            } else if (score >= 80) {
                tips.push({
                    icon: 'fas fa-thumbs-up',
                    color: 'info',
                    message: `${name} มีความก้าวหน้าที่ดี ควรให้กำลังใจต่อไป`
                });
            } else if (score < 70) {
                tips.push({
                    icon: 'fas fa-bell',
                    color: 'warning',
                    message: `${name} ต้องการความช่วยเหลือเพิ่มเติม ควรติดตามอย่างใกล้ชิด`
                });
            }
        }
    });
    
    // ถ้าไม่มีข้อมูลให้แสดงเคล็ดลับทั่วไป
    if (tips.length === 0) {
        tips.push({
            icon: 'fas fa-lightbulb',
            color: 'info',
            message: 'สื่อสารกับบุตรหลานอย่างสม่ำเสมอเพื่อติดตามความเป็นไปในโรงเรียน'
        });
    }
    
    const tipsContainer = document.createElement('div');
    tipsContainer.className = 'app-card p-3 mt-4';
    tipsContainer.innerHTML = `
        <h3 class="h5 text-primary-app mb-3">คำแนะนำสำหรับผู้ปกครอง</h3>
        <div class="parenting-tips">
            ${tips.slice(0, 3).map((tip, index) => `
                <div class="tip-item d-flex py-2 ${index < tips.length - 1 ? 'border-bottom' : ''}">
                    <div class="me-3">
                        <div class="bg-${tip.color} rounded-circle tip-icon d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="${tip.icon} text-white"></i>
                        </div>
                    </div>
                    <div>
                        <p class="mb-0 fw-medium">${tip.message}</p>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    // Insert tips with proper positioning
    const notificationCard = document.querySelector('.notification-list')?.closest('.app-card');
    if (notificationCard) {
        notificationCard.parentNode.insertBefore(tipsContainer, notificationCard.nextSibling);
    } else {
        document.querySelector('#all-students-view')?.appendChild(tipsContainer);
    }
    
    // Animate tip items with stagger effect
    const tipItems = document.querySelectorAll('.tip-item');
    tipItems.forEach((item, index) => {
        setTimeout(() => {
            item.classList.add('animated');
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 600 + (index * 150));
    });
}

/**
 * Create and show communication modal with teacher
 */
function showCommunicationModal() {
    // ใช้ข้อมูลจริงของผู้ปกครองจาก element ที่มีอยู่
    const parentNameElement = document.querySelector('.parent-info-card h2');
    const parentName = parentNameElement ? 
        parentNameElement.textContent.replace('สวัสดี ', '').trim() : 
        'ผู้ปกครอง';
    
    // Create modal with Bootstrap structure
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'communicationModal';
    modal.setAttribute('tabindex', '-1');
    modal.setAttribute('aria-labelledby', 'communicationModalLabel');
    modal.setAttribute('aria-hidden', 'true');
    
    // Add enhanced chat interface
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="communicationModalLabel">
                        <i class="fas fa-comment-dots me-2"></i>
                        ติดต่อครูประจำชั้น
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="chat-container p-3" style="height: 300px; overflow-y: auto; border-radius: 0.5rem; background-color: #f8f9fa;">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-comments fa-2x mb-2"></i>
                            <p class="mb-0">เริ่มต้นการสนทนากับครูประจำชั้น</p>
                        </div>
                    </div>
                    <div class="message-input mt-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="พิมพ์ข้อความ...">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-paper-plane"></i> ส่ง
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to body
    document.body.appendChild(modal);
    
    // Initialize and show modal
    const bsModal = new bootstrap.Modal(document.getElementById('communicationModal'));
    bsModal.show();
    
    // Set up event handler for cleanup
    modal.addEventListener('hidden.bs.modal', function () {
        if (modal.parentNode === document.body) {
            document.body.removeChild(modal);
        }
    });
    
    // Set up send button handler
    const sendButton = modal.querySelector('.btn-primary');
    const inputField = modal.querySelector('.form-control');
    
    if (sendButton && inputField) {
        const handleSend = () => {
            const message = inputField.value.trim();
            if (!message) return;
            
            // Add message to chat
            addChatMessage(modal, message, parentName);
            inputField.value = '';
        };
        
        sendButton.addEventListener('click', handleSend);
        inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') handleSend();
        });
    }
}

/**
 * Add chat message to communication modal
 */
function addChatMessage(modal, message, senderName) {
    const chatContainer = modal.querySelector('.chat-container');
    if (!chatContainer) return;
    
    // Clear welcome message if it exists
    const welcomeMessage = chatContainer.querySelector('.text-center');
    if (welcomeMessage) {
        welcomeMessage.remove();
    }
    
    // Create new message element
    const messageElement = document.createElement('div');
    messageElement.className = 'message parent-message mb-3 text-end';
    
    // Get current time
    const now = new Date();
    const timeStr = `${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')} น.`;
    
    messageElement.innerHTML = `
        <div class="message-header d-flex justify-content-between">
            <small class="text-muted">${timeStr}</small>
            <span class="fw-bold"><i class="fas fa-user me-1"></i> ${senderName}</span>
        </div>
        <div class="message-body p-3 bg-primary text-white rounded mt-1" style="max-width: 70%; margin-left: auto;">
            ${message}
        </div>
    `;
    
    // Add message with animation
    messageElement.style.opacity = '0';
    messageElement.style.transform = 'translateY(10px)';
    chatContainer.appendChild(messageElement);
    
    // Scroll to bottom
    chatContainer.scrollTop = chatContainer.scrollHeight;
    
    // Apply animation
    setTimeout(() => {
        messageElement.style.transition = 'all 0.3s ease';
        messageElement.style.opacity = '1';
        messageElement.style.transform = 'translateY(0)';
    }, 10);
    
    // Add teacher response after delay
    setTimeout(() => {
        addTeacherResponse(chatContainer);
    }, 1500);
}

/**
 * Add teacher response to chat
 */
function addTeacherResponse(chatContainer) {
    // Teacher responses
    const responses = [
        'ขอบคุณที่ติดต่อมาครับ หากมีปัญหาอะไรเพิ่มเติมจะแจ้งให้ทราบนะครับ',
        'จะติดตามเรื่องนี้และแจ้งให้ผู้ปกครองทราบครับ',
        'ไม่เป็นไรครับ เราจะช่วยกันดูแลนักเรียนให้ดีที่สุดครับ'
    ];
    
    // Select random response
    const response = responses[Math.floor(Math.random() * responses.length)];
    
    // Create teacher response
    const messageElement = document.createElement('div');
    messageElement.className = 'message teacher-message mb-3';
    
    // Get current time
    const now = new Date();
    const timeStr = `${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')} น.`;
    
    messageElement.innerHTML = `
        <div class="message-header d-flex justify-content-between">
            <span class="fw-bold"><i class="fas fa-user-tie me-1"></i> ครูประจำชั้น</span>
            <small class="text-muted">${timeStr}</small>
        </div>
        <div class="message-body p-3 bg-white border rounded mt-1" style="max-width: 70%;">
            ${response}
        </div>
    `;
    
    // Add message with animation
    messageElement.style.opacity = '0';
    messageElement.style.transform = 'translateY(10px)';
    chatContainer.appendChild(messageElement);
    
    // Scroll to bottom
    chatContainer.scrollTop = chatContainer.scrollHeight;
    
    // Apply animation
    setTimeout(() => {
        messageElement.style.transition = 'all 0.3s ease';
        messageElement.style.opacity = '1';
        messageElement.style.transform = 'translateY(0)';
    }, 10);
}

/**
 * Add scroll to top button with smooth animation
 */
function addScrollToTopButton() {
    const button = document.createElement('button');
    button.className = 'scroll-top-btn';
    button.innerHTML = '<i class="fas fa-chevron-up"></i>';
    button.style.cssText = `
        position: fixed;
        bottom: 80px;
        right: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-app, #1020AD);
        color: white;
        border: none;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(button);
    
    // Optimize scroll listener with throttle
    let lastScrollTime = 0;
    const scrollThrottle = 100; // ms
    
    window.addEventListener('scroll', function() {
        const now = Date.now();
        if (now - lastScrollTime < scrollThrottle) return;
        lastScrollTime = now;
        
        const scrollPos = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollPos > 300) {
            button.style.opacity = '1';
            button.style.visibility = 'visible';
        } else {
            button.style.opacity = '0';
            button.style.visibility = 'hidden';
        }
    }, { passive: true });
    
    // Smooth scroll to top
    button.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Initialize communication log functionality
 */
function initializeCommunicationLog() {
    // Add communication log tab functionality
    const contactBtn = document.querySelector('.contact-teacher-btn');
    if (!contactBtn) return;
    
    contactBtn.addEventListener('click', function() {
        showCommunicationModal();
    });
}