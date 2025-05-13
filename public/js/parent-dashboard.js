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
        
        // Update student data
        updateStudentInfo(studentId);
        updateStudentCharts(studentId);
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
    // Initialize charts if elements exist
    if (document.getElementById('studentBehaviorChart')) {
        initStudentBehaviorChart();
    }
    
    if (document.getElementById('attendanceChart')) {
        initAttendanceChart();
    }
}

/**
 * Initialize student behavior chart with optimizations
 */
function initStudentBehaviorChart() {
    const ctx = document.getElementById('studentBehaviorChart').getContext('2d');
    if (!ctx) return;
    
    // Chart configuration with optimized options
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['คะแนนบวก', 'คะแนนลบ', 'คะแนนคงเหลือ'],
            datasets: [{
                data: [0, 0, 0], // Start with zero for animation
                backgroundColor: [
                    '#1A91FF', // Light blue
                    '#FF5757', // Red
                    '#FFD747'  // Yellow
                ],
                borderWidth: 0,
                cutout: '65%',
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            family: "'Prompt', sans-serif",
                            size: 12
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value} คะแนน`;
                        }
                    },
                    padding: 12,
                    backgroundColor: 'rgba(0, 0, 0, 0.75)',
                    titleFont: {
                        family: "'Prompt', sans-serif",
                        size: 14
                    },
                    bodyFont: {
                        family: "'Prompt', sans-serif",
                        size: 13
                    },
                    cornerRadius: 6,
                    caretSize: 6
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Animate chart data from 0 to actual values with proper timing
    setTimeout(function() {
        chart.data.datasets[0].data = [75, 15, 10]; // Default values
        chart.update('default');
    }, 400);
}

/**
 * Initialize attendance chart with optimizations
 */
function initAttendanceChart() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    if (!ctx) return;
    
    // Chart configuration with optimized options
    const chart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['มาเรียน', 'มาสาย', 'ขาดเรียน'],
            datasets: [{
                data: [0, 0, 0], // Start with zero for animation
                backgroundColor: [
                    '#28a745', // Green
                    '#ffc107', // Yellow
                    '#dc3545'  // Red
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            family: "'Prompt', sans-serif",
                            size: 12
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value}%`;
                        }
                    },
                    padding: 12,
                    backgroundColor: 'rgba(0, 0, 0, 0.75)',
                    titleFont: {
                        family: "'Prompt', sans-serif",
                        size: 14
                    },
                    bodyFont: {
                        family: "'Prompt', sans-serif",
                        size: 13
                    },
                    cornerRadius: 6,
                    caretSize: 6
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Animate chart data from 0 to actual values with proper timing
    setTimeout(function() {
        chart.data.datasets[0].data = [84, 10, 6]; // Default values
        chart.update('default');
    }, 600);
}

/**
 * Update student information with smooth animations
 */
function updateStudentInfo(studentId) {
    // Get student data (in production this would come from an API)
    const studentData = getStudentData(studentId);
    if (!studentData) return;
    
    // Update text elements with animation if supported
    updateElementWithAnimation('.student-name', studentData.name);
    updateElementWithAnimation('.student-class', studentData.class);
    updateElementWithAnimation('.student-points', studentData.points);
    updateElementWithAnimation('.student-points-badge', `${studentData.points} คะแนน`);
    updateElementWithAnimation('.student-status', studentData.status);
    updateElementWithAnimation('.student-rank', studentData.rank, true);
    
    // Update badge classes based on points
    const badgeClass = getBadgeClassForPoints(studentData.points);
    updateElementClass('.student-points-badge', `badge ${badgeClass} student-points-badge`);
    updateElementClass('.student-status', `badge ${badgeClass} student-status`);
}

/**
 * Get student data (mock data - would be from API in production)
 */
function getStudentData(studentId) {
    const studentData = {
        student1: {
            name: 'ด.ช. กล้า ภูมิมาลา',
            class: 'ชั้น ม.2/3 เลขที่ 5',
            points: 95,
            status: 'ดีมาก',
            rank: '3<span class="fs-6">/30</span>'
        },
        student2: {
            name: 'ด.ญ. ใจดี ภูมิมาลา',
            class: 'ชั้น ม.3/1 เลขที่ 12',
            points: 75,
            status: 'ดี',
            rank: '8<span class="fs-6">/25</span>'
        },
        student3: {
            name: 'ด.ช. จริง ภูมิมาลา',
            class: 'ชั้น ม.1/2 เลขที่ 18',
            points: 85,
            status: 'ดี',
            rank: '4<span class="fs-6">/28</span>'
        }
    };
    
    return studentData[studentId];
}

/**
 * Get appropriate badge class based on points
 */
function getBadgeClassForPoints(points) {
    return points >= 90 ? 'bg-success' : 
           points >= 80 ? 'bg-primary' :
           points >= 70 ? 'bg-info' :
           points >= 60 ? 'bg-warning text-dark' : 'bg-danger';
}

/**
 * Update element text with animation
 */
function updateElementWithAnimation(selector, text, isHTML = false) {
    const element = document.querySelector(selector);
    if (!element) return;
    
    element.style.opacity = '0';
    element.style.transform = 'translateY(-5px)';
    
    setTimeout(() => {
        if (isHTML) {
            element.innerHTML = text;
        } else {
            element.textContent = text;
        }
        
        element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        element.style.opacity = '1';
        element.style.transform = 'translateY(0)';
    }, 300);
}

/**
 * Update element class
 */
function updateElementClass(selector, className) {
    const element = document.querySelector(selector);
    if (element) element.className = className;
}

/**
 * Update charts for the selected student with smooth transitions
 */
function updateStudentCharts(studentId) {
    const chartData = getChartData(studentId);
    if (!chartData) return;
    
    // Update behavior chart with animation
    const behaviorChart = Chart.getChart('studentBehaviorChart');
    if (behaviorChart) {
        behaviorChart.data.datasets[0].data = chartData.behavior;
        behaviorChart.update('default');
    }
    
    // Update attendance chart with animation
    const attendanceChart = Chart.getChart('attendanceChart');
    if (attendanceChart) {
        attendanceChart.data.datasets[0].data = [
            chartData.attendance.present,
            chartData.attendance.late,
            chartData.attendance.absent
        ];
        attendanceChart.update('default');
    }
}

/**
 * Get chart data for a student (mock data)
 */
function getChartData(studentId) {
    const chartData = {
        student1: {
            behavior: [75, 15, 10],
            attendance: {
                present: 84,
                late: 10,
                absent: 6
            }
        },
        student2: {
            behavior: [60, 25, 15],
            attendance: {
                present: 75,
                late: 15,
                absent: 10
            }
        },
        student3: {
            behavior: [65, 10, 25],
            attendance: {
                present: 90,
                late: 7,
                absent: 3
            }
        }
    };
    
    return chartData[studentId];
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
 * Highlight the best performing student
 */
function highlightBestPerformingStudent() {
    const studentCards = document.querySelectorAll('.student-summary-card');
    if (!studentCards.length) return;
    
    let bestScore = 0;
    let bestStudent = null;
    
    // Find highest scoring student
    studentCards.forEach(card => {
        const badge = card.querySelector('.badge');
        if (!badge) return;
        
        // Extract numeric score from badge text
        const scoreText = badge.textContent.trim();
        const score = parseInt(scoreText);
        
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
        
        const container = bestStudent.querySelector('.d-flex');
        if (container) container.appendChild(starBadge);
    }
}

/**
 * Generate parenting tips with contextual advice
 */
function generateParentingTips() {
    const tipsContainer = document.createElement('div');
    tipsContainer.className = 'app-card p-3 mt-4';
    tipsContainer.innerHTML = `
        <h3 class="h5 text-primary-app mb-3">คำแนะนำสำหรับผู้ปกครอง</h3>
        <div class="parenting-tips">
            <div class="tip-item d-flex py-2 border-bottom">
                <div class="me-3">
                    <div class="bg-info rounded-circle tip-icon d-flex align-items-center justify-content-center">
                        <i class="fas fa-lightbulb text-white"></i>
                    </div>
                </div>
                <div>
                    <p class="mb-0 fw-medium">สนับสนุนให้ ด.ช. กล้า ทำกิจกรรมจิตอาสาต่อไป เพื่อพัฒนาความเป็นผู้นำ</p>
                </div>
            </div>
            <div class="tip-item d-flex py-2 border-bottom">
                <div class="me-3">
                    <div class="bg-warning rounded-circle tip-icon d-flex align-items-center justify-content-center">
                        <i class="fas fa-bell text-white"></i>
                    </div>
                </div>
                <div>
                    <p class="mb-0 fw-medium">ควรติดตาม ด.ญ. ใจดี เรื่องการส่งการบ้านให้มากขึ้น</p>
                </div>
            </div>
            <div class="tip-item d-flex py-2">
                <div class="me-3">
                    <div class="bg-success rounded-circle tip-icon d-flex align-items-center justify-content-center">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                </div>
                <div>
                    <p class="mb-0 fw-medium">ช่วยให้เด็กๆ มาโรงเรียนตรงเวลามากขึ้นเพื่อลดการมาสาย</p>
                </div>
            </div>
        </div>
    `;
    
    // Insert tips with proper positioning
    const eventsCard = document.querySelector('.event-list')?.closest('.app-card');
    if (eventsCard) {
        eventsCard.parentNode.insertBefore(tipsContainer, eventsCard.nextSibling);
    } else {
        document.querySelector('#all-students-view')?.appendChild(tipsContainer);
    }
    
    // Animate tip items with stagger effect
    const tipItems = document.querySelectorAll('.tip-item');
    tipItems.forEach((item, index) => {
        setTimeout(() => {
            item.classList.add('animated');
        }, 600 + (index * 150));
    });
}

/**
 * Create and show communication modal with teacher
 */
function showCommunicationModal() {
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
                    <div class="chat-container p-3" style="height: 300px; overflow-y: auto; border-radius: 0.5rem;">
                        <div class="message teacher-message mb-3">
                            <div class="message-header d-flex justify-content-between">
                                <span class="fw-bold"><i class="fas fa-user-tie me-1"></i> อ.สมชาย รักดี</span>
                                <small class="text-muted">10:30 น.</small>
                            </div>
                            <div class="message-body p-3 bg-white rounded">
                                สวัสดีคุณผู้ปกครอง ด.ช. กล้า มีพัฒนาการที่ดีขึ้นในช่วงที่ผ่านมา แต่ยังมีปัญหาเรื่องการมาสายอยู่บ้าง
                            </div>
                        </div>
                        <div class="message parent-message mb-3 text-end">
                            <div class="message-header d-flex justify-content-between">
                                <span class="fw-bold"><i class="fas fa-user me-1"></i> คุณ ขจรศักดิ์ ภูมิมาลา</span>
                                <small class="text-muted">10:45 น.</small>
                            </div>
                            <div class="message-body p-3 bg-primary-app text-white rounded">
                                ขอบคุณสำหรับข้อมูลครับ ผมจะพยายามพาเขามาส่งให้เร็วขึ้น
                            </div>
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
        // Remove modal from DOM after hiding
        document.body.removeChild(modal);
    });
    
    // Set up send button handler
    const sendButton = modal.querySelector('.btn-primary');
    const inputField = modal.querySelector('.form-control');
    
    if (sendButton && inputField) {
        const handleSend = () => {
            const message = inputField.value.trim();
            if (!message) return;
            
            // Add message to chat
            addChatMessage(modal, message);
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
function addChatMessage(modal, message) {
    const chatContainer = modal.querySelector('.chat-container');
    if (!chatContainer) return;
    
    // Create new message element
    const messageElement = document.createElement('div');
    messageElement.className = 'message parent-message mb-3 text-end';
    
    // Get current time
    const now = new Date();
    const timeStr = `${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')} น.`;
    
    messageElement.innerHTML = `
        <div class="message-header d-flex justify-content-between">
            <span class="fw-bold"><i class="fas fa-user me-1"></i> คุณ ขจรศักดิ์ ภูมิมาลา</span>
            <small class="text-muted">${timeStr}</small>
        </div>
        <div class="message-body p-3 bg-primary-app text-white rounded">
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
        'ขอบคุณมากครับ หากมีปัญหาอะไรเพิ่มเติมจะแจ้งให้ทราบนะครับ',
        'สัปดาห์หน้ามีกิจกรรมจิตอาสา อยากให้น้องเข้าร่วมด้วยครับ',
        'ไม่เป็นไรครับ เราจะช่วยกันดูแลน้องให้ดีที่สุดครับ'
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
            <span class="fw-bold"><i class="fas fa-user-tie me-1"></i> อ.สมชาย รักดี</span>
            <small class="text-muted">${timeStr}</small>
        </div>
        <div class="message-body p-3 bg-white rounded">
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
    button.style.position = 'fixed';
    button.style.bottom = '80px';
    button.style.right = '20px';
    button.style.width = '40px';
    button.style.height = '40px';
    button.style.borderRadius = '50%';
    button.style.backgroundColor = 'var(--primary-app)';
    button.style.color = 'white';
    button.style.border = 'none';
    button.style.boxShadow = '0 3px 10px rgba(0, 0, 0, 0.2)';
    button.style.zIndex = '1000';
    button.style.cursor = 'pointer';
    
    document.body.appendChild(button);
    
    // Optimize scroll listener with throttle
    let lastScrollTime = 0;
    const scrollThrottle = 100; // ms
    
    window.addEventListener('scroll', function() {
        const now = Date.now();
        if (now - lastScrollTime < scrollThrottle) return;
        lastScrollTime = now;
        
        const scrollPos = window.pageYOffset || document.documentElement.scrollTop;
        button.classList.toggle('visible', scrollPos > 300);
    }, { passive: true });
    
    // Smooth scroll to top
    button.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}