/**
 * Student Dashboard JavaScript
 * Handles animations, chart functionality and interactive features
 */

// Wait until DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Show loading animation
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loadingOverlay);
    
    // Initialize all components after a short delay to show loading animation
    setTimeout(function() {
        initCharts();
        animateActivityItems();
        initializeEventListeners();
        handleMobileContent();
        enhanceNavbar(); // Add the new navbar enhancements
        
        // Add page transition class to main container
        document.querySelector('.app-container').classList.add('page-transition');
        
        // Remove loading overlay with fade effect
        loadingOverlay.style.opacity = '0';
        setTimeout(() => {
            loadingOverlay.remove();
        }, 500);
        
    }, 800); // Short delay to show loading animation
});

/**
 * Initialize and animate the behavior charts for both desktop and mobile
 */
function initCharts() {
    const desktopCtx = document.getElementById('behaviorChart').getContext('2d');
    const mobileCtx = document.getElementById('behaviorChartMobile')?.getContext('2d');
    
    // Mark chart container as loaded
    document.querySelectorAll('.chart-container').forEach(container => {
        container.classList.add('loaded');
    });
    
    const chartOptions = {
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
                cutout: '65%'
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
                        }
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
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: {
                        family: "'Prompt', sans-serif",
                        size: 14
                    },
                    bodyFont: {
                        family: "'Prompt', sans-serif",
                        size: 13
                    }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    };
    
    // Create both charts if available
    const desktopChart = new Chart(desktopCtx, chartOptions);
    const mobileChart = mobileCtx ? new Chart(mobileCtx, chartOptions) : null;
    
    // Animate chart data from 0 to actual values
    setTimeout(function() {
        // Update desktop chart
        desktopChart.data.datasets[0].data = [75, 15, 10]; // Actual values
        desktopChart.update();
        
        // Update mobile chart if it exists
        if (mobileChart) {
            mobileChart.data.datasets[0].data = [75, 15, 10]; // Actual values
            mobileChart.update();
        }
    }, 400);
}

/**
 * Animate activity items with staggered effect
 */
function animateActivityItems() {
    const activityItems = document.querySelectorAll('.activity-item');
    
    activityItems.forEach((item, index) => {
        setTimeout(() => {
            item.classList.add('animated');
        }, 300 + (index * 150)); // Stagger effect
    });
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

    // Desktop navbar active state
    const desktopNavLinks = document.querySelectorAll('.desktop-nav-link');
    desktopNavLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all links
            desktopNavLinks.forEach(l => {
                l.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active');
        });
    });
    
    // Add theme toggle functionality
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-theme');
            
            // Save theme preference to local storage
            const isDarkTheme = document.body.classList.contains('dark-theme');
            localStorage.setItem('darkTheme', isDarkTheme);
            
            // Change icon based on theme
            const icon = this.querySelector('i');
            icon.className = isDarkTheme ? 'fas fa-sun' : 'fas fa-moon';
        });
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('darkTheme');
        if (savedTheme === 'true') {
            document.body.classList.add('dark-theme');
            themeToggle.querySelector('i').className = 'fas fa-sun';
        }
    }
    
    // Stats card hover effects
    const statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            // Add pulse animation to value
            const statsValue = this.querySelector('.stats-value');
            if (statsValue) {
                statsValue.style.transform = 'scale(1.08)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            // Remove pulse animation
            const statsValue = this.querySelector('.stats-value');
            if (statsValue) {
                statsValue.style.transform = 'scale(1)';
            }
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
    
    // Add pull-to-refresh functionality for mobile
    let touchStartY = 0;
    let touchEndY = 0;
    
    document.addEventListener('touchstart', function(e) {
        touchStartY = e.touches[0].clientY;
    }, false);
    
    document.addEventListener('touchend', function(e) {
        touchEndY = e.changedTouches[0].clientY;
        handleSwipeGesture();
    }, false);
    
    function handleSwipeGesture() {
        if (touchStartY < 50 && (touchEndY - touchStartY) > 100) {
            // Show refresh indicator
            const refreshIndicator = document.createElement('div');
            refreshIndicator.className = 'refresh-indicator';
            refreshIndicator.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> กำลังโหลดข้อมูล...';
            document.body.prepend(refreshIndicator);
            
            // Simulate refresh after delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    }
}

/**
 * Enhanced navbar interactivity
 */
function enhanceNavbar() {
    const desktopNavLinks = document.querySelectorAll('.desktop-nav-link');
    
    // Add hover state detection
    desktopNavLinks.forEach(link => {
        // Enhanced click effect
        link.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            ripple.classList.add('nav-ripple');
            this.appendChild(ripple);
            
            const rect = link.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            // Remove active class from all links
            desktopNavLinks.forEach(l => {
                l.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Remove ripple after animation completes
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Optional: Add subtle animation to navbar on scroll
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.desktop-navbar');
        if (!navbar) return;
        
        const st = window.pageYOffset || document.documentElement.scrollTop;
        if (st > lastScrollTop && st > 70) {
            // Scrolling down
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            navbar.style.transform = 'translateY(0)';
        }
        lastScrollTop = st <= 0 ? 0 : st;
    }, false);
}

/**
 * Updates the points summary with new data (for simulating real-time updates)
 */
function updatePointsSummary(points, rank) {
    const pointsElements = document.querySelectorAll('[id="behavior-points"]');
    const rankElements = document.querySelectorAll('[id="class-rank"]');
    const oldPoints = parseInt(pointsElements[0].innerText);
    
    // Update all instances of points display
    pointsElements.forEach(element => {
        animateValue(element, oldPoints, points, 1000);
    });
    
    // Update all instances of rank display if provided
    if (rank) {
        rankElements.forEach(element => {
            element.innerText = rank;
        });
    }
    
    // Update badge status based on points
    const badgeElements = document.querySelectorAll('.badge');
    badgeElements.forEach(badge => {
        if (points >= 90) {
            badge.className = 'badge bg-success';
            badge.innerText = 'ดีมาก';
        } else if (points >= 70) {
            badge.className = 'badge bg-primary';
            badge.innerText = 'ดี';
        } else if (points >= 50) {
            badge.className = 'badge bg-warning text-dark';
            badge.innerText = 'ปานกลาง';
        } else {
            badge.className = 'badge bg-danger';
            badge.innerText = 'ต้องปรับปรุง';
        }
    });
}

/**
 * Animate counting from start to end value
 */
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.innerText = value;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}