/**
 * Student Dashboard JavaScript
 * Handles charts and interactive features - simplified version without effects
 */

// Global chart instances
let behaviorChart = null;
let behaviorChartMobile = null;

// Wait until DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Show loading animation
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loadingOverlay);
    
    // Initialize components
    setTimeout(function() {
        if (window.chartData) {
            initCharts(window.chartData);
        }
        initializeEventListeners();
        handleMobileContent();
        
        // Remove loading overlay with fade effect
        loadingOverlay.style.opacity = '0';
        setTimeout(() => {
            loadingOverlay.remove();
        }, 500);
        
    }, 800);
});

/**
 * Initialize charts with provided data
 */
function initCharts(chartData) {
    // Desktop Chart
    const desktopCtx = document.getElementById('behaviorChart');
    if (desktopCtx) {
        // ทำลายกราฟเก่าก่อนสร้างใหม่
        if (behaviorChart) {
            behaviorChart.destroy();
        }
        
        behaviorChart = new Chart(desktopCtx.getContext('2d'), {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 50,
                        max: 100
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }
    
    // Mobile Chart
    const mobilectx = document.getElementById('behaviorChartMobile');
    if (mobilectx) {
        // ทำลายกราฟเก่าก่อนสร้างใหม่
        if (behaviorChartMobile) {
            behaviorChartMobile.destroy();
        }
        
        behaviorChartMobile = new Chart(mobilectx.getContext('2d'), {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 50,
                        max: 100
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}

/**
 * Handle mobile-specific content
 */
function handleMobileContent() {
    // Clone activity items for mobile view
    const activityItems = document.querySelectorAll('.activities-area .activity-item');
    const mobileActivitiesContainer = document.querySelector('.mobile-activities');

    if (mobileActivitiesContainer && activityItems.length > 0) {
        activityItems.forEach(item => {
            mobileActivitiesContainer.appendChild(item.cloneNode(true));
        });
    }
}

/**
 * Initialize all event listeners for interactive elements
 */
function initializeEventListeners() {
    // Bottom navbar active state (mobile)
    const mobileNavLinks = document.querySelectorAll('.bottom-navbar .nav-link');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all links
            mobileNavLinks.forEach(l => {
                l.classList.remove('text-primary-app');
                l.classList.add('text-muted');
            });
            
            // Add active class to clicked link
            this.classList.remove('text-muted');
            this.classList.add('text-primary-app');
        });
    });

    // Desktop navbar active state - แบบเรียบง่ายไม่มี effects
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            navItems.forEach(navItem => {
                navItem.classList.remove('active');
            });
            
            // Add active class to this item
            this.classList.add('active');
        });
    });
    
    // Add responsive handling for window resizing
    window.addEventListener('resize', function() {
        // Adjust charts if needed
        const desktopChart = Chart.getChart('behaviorChart');
        const mobileChart = Chart.getChart('behaviorChartMobile');
        
        if (desktopChart) desktopChart.resize();
        if (mobileChart) mobileChart.resize();
    });
}

/**
 * Toggle User menu dropdown
 */
function toggleUserMenu() {
    const userMenu = document.getElementById('userMenu');
    if (userMenu) {
        userMenu.classList.toggle('show');
        
        // Close menu when clicking outside
        if (userMenu.classList.contains('show')) {
            document.addEventListener('click', closeUserMenuOnClickOutside);
        } else {
            document.removeEventListener('click', closeUserMenuOnClickOutside);
        }
    }
}

/**
 * Close user menu when clicking outside
 */
function closeUserMenuOnClickOutside(event) {
    const userMenu = document.getElementById('userMenu');
    const userProfile = document.querySelector('.user-profile');
    
    if (userMenu && !userMenu.contains(event.target) && !userProfile.contains(event.target)) {
        userMenu.classList.remove('show');
        document.removeEventListener('click', closeUserMenuOnClickOutside);
    }
}

// เพิ่มฟังก์ชันนี้ให้ window เพื่อให้เรียกใช้จาก onclick ได้
window.toggleUserMenu = toggleUserMenu;