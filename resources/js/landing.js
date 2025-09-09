// Landing Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize landing page animations
    initializeLandingAnimations();
    
    // Initialize trial form
    initializeTrialForm();
    
    // Initialize smooth scrolling
    initializeSmoothScrolling();
    
    // Initialize parallax effects
    initializeParallax();
    
    // Initialize statistics animations
    initializeStatsAnimations();
});

// Animation System
function initializeLandingAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);

    // Observe all elements with animation classes
    const animatedElements = document.querySelectorAll(
        '.fade-in, .slide-in-left, .slide-in-right, .scale-in, .bounce-in, .stagger-animation'
    );

    animatedElements.forEach(el => {
        observer.observe(el);
    });
}

function showTrialMessage(type, text) {
    const messageBox = document.getElementById('trialMessageBox');
    if (!messageBox) return;
    // Clear previous timer if any
    if (showTrialMessage._timeout) {
        clearTimeout(showTrialMessage._timeout);
        showTrialMessage._timeout = null;
    }
    // Set content and class
    messageBox.textContent = text;
    messageBox.className = `trial-message ${type}`;
    messageBox.style.display = 'block';
    messageBox.style.opacity = '0';
    messageBox.style.transition = 'opacity 0.4s';
    // Force reflow for transition
    void messageBox.offsetWidth;
    messageBox.style.opacity = '1';
    // Hide after 4s with fade out
    showTrialMessage._timeout = setTimeout(() => {
        messageBox.style.opacity = '0';
        setTimeout(() => {
            messageBox.style.display = 'none';
        }, 400);
    }, 4000);
}

// Trial Form Functionality
function initializeTrialForm() {
    const trialForm = document.getElementById('trialForm');
    const submitBtn = document.getElementById('submitBtn');
    const messageBox = document.getElementById('trialMessageBox');

    if (trialForm) {
        trialForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(trialForm);
            const contact = formData.get('contact').trim();
            const name = formData.get('name').trim();
            const eventType = formData.get('event_type').trim();

            // Clear previous messages and timer
            if (showTrialMessage._timeout) {
                clearTimeout(showTrialMessage._timeout);
                showTrialMessage._timeout = null;
            }
            if (messageBox) {
                messageBox.textContent = '';
                messageBox.className = '';
                messageBox.style.display = 'none';
                messageBox.style.opacity = '0';
            }

            // Button animation
            submitBtn.style.transform = 'scale(0.95)';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            // CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/trial', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    contact: contact,
                    name: name,
                    event_type: eventType
                })
            })
            .then(async response => {
                const data = await response.json();
                submitBtn.style.transform = '';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Sample Invitation';

                if (response.ok && data.success) {
                    showTrialMessage('success', data.message || 'Your trial request has been sent!');
                    trialForm.reset();
                } else {
                    let errorMsg = data.message || (data.errors && data.errors[0]) || 'An error occurred.';
                    showTrialMessage('error', errorMsg);
                }
            })
            .catch(error => {
                submitBtn.style.transform = '';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Sample Invitation';
                showTrialMessage('error', 'A network error occurred. Please try again.');
            });
        });
    }
}

// Smooth Scrolling
function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Parallax Effects
function initializeParallax() {
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.blob');
        
        parallaxElements.forEach((element, index) => {
            const speed = 0.5 + (index * 0.1);
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
}

// Statistics Animation System
function initializeStatsAnimations() {
    const statsObserverOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStatistics();
                statsObserver.unobserve(entry.target); // Only animate once
            }
        });
    }, statsObserverOptions);

    // Observe the stats section
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        statsObserver.observe(statsSection);
    }
}

function animateStatistics() {
    const statNumbers = document.querySelectorAll('.stat-number[data-stat]');
    
    statNumbers.forEach((element, index) => {
        const finalValue = element.textContent;
        const numericValue = parseFloat(finalValue.replace(/[^\d.]/g, ''));
        const suffix = finalValue.replace(/[\d.]/g, '');
        
        if (!isNaN(numericValue)) {
            animateNumber(element, 0, numericValue, suffix, 1500, index * 100);
        }
    });
}

function animateNumber(element, start, end, suffix, duration, delay) {
    setTimeout(() => {
        const startTime = performance.now();
        const isDecimal = end % 1 !== 0;
        
        function updateNumber(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = start + (end - start) * easeOutQuart;
            
            if (isDecimal) {
                element.textContent = current.toFixed(1) + suffix;
            } else {
                element.textContent = Math.floor(current) + suffix;
            }
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                element.textContent = end + suffix;
                element.classList.add('animate');
            }
        }
        
        requestAnimationFrame(updateNumber);
    }, delay);
}

// Refresh statistics periodically (optional)
function refreshStats() {
    fetch('/api/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatsDisplay(data.data);
            }
        })
        .catch(error => {
            console.log('Failed to refresh stats:', error);
        });
}

function updateStatsDisplay(stats) {
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            element.textContent = stats[key];
        }
    });
}

// Add loading animation for page elements
setTimeout(() => {
    document.body.classList.add('loaded');
}, 100); 
