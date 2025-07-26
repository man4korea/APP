// BPR Hub Homepage JavaScript

// DOM Elements
const loginModal = document.getElementById('loginModal');
const signupModal = document.getElementById('signupModal');
const loginForm = document.getElementById('loginForm');
const signupForm = document.getElementById('signupForm');

// Modal Functions
function showLoginModal() {
    loginModal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function showSignupModal() {
    signupModal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function switchToSignup() {
    closeModal('loginModal');
    showSignupModal();
}

function switchToLogin() {
    closeModal('signupModal');
    showLoginModal();
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == loginModal) {
        closeModal('loginModal');
    }
    if (event.target == signupModal) {
        closeModal('signupModal');
    }
}

// Form Handlers
loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        email: document.getElementById('loginEmail').value,
        password: document.getElementById('loginPassword').value
    };
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '로그인 중...';
    submitBtn.disabled = true;
    
    // Simulate API call (replace with actual authentication)
    setTimeout(() => {
        console.log('Login attempt:', formData);
        alert('로그인 기능은 개발 중입니다. 곧 이용 가능합니다!');
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        closeModal('loginModal');
    }, 1500);
});

signupForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('signupName').value,
        email: document.getElementById('signupEmail').value,
        password: document.getElementById('signupPassword').value,
        company: document.getElementById('signupCompany').value
    };
    
    // Basic validation
    if (formData.password.length < 6) {
        alert('비밀번호는 최소 6자 이상이어야 합니다.');
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '계정 생성 중...';
    submitBtn.disabled = true;
    
    // Simulate API call (replace with actual registration)
    setTimeout(() => {
        console.log('Signup attempt:', formData);
        alert('회원가입 기능은 개발 중입니다. 곧 이용 가능합니다!');
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        closeModal('signupModal');
    }, 1500);
});

// CTA Functions
function startFreeTrial() {
    showSignupModal();
}

function upgradeToPremium() {
    alert('프리미엄 플랜은 곧 출시됩니다! 무료 계정을 먼저 만들어 주세요.');
    showSignupModal();
}

function contactSales() {
    alert('영업팀 문의: business@bprhub.com\n전화: 02-1234-5678');
}

function showDemo() {
    alert('데모 영상은 준비 중입니다. 무료 계정을 만들어 직접 체험해보세요!');
    showSignupModal();
}

// Smooth scrolling for navigation links
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

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
        navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
    } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.boxShadow = 'none';
    }
});

// Feature cards animation on scroll
function animateOnScroll() {
    const cards = document.querySelectorAll('.feature-card, .pricing-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
}

// Initialize animations when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    animateOnScroll();
    
    // Add typing effect to hero title
    typewriterEffect();
});

// Typewriter effect for hero title
function typewriterEffect() {
    const heroTitle = document.querySelector('.hero-title');
    if (!heroTitle) return;
    
    const text = heroTitle.innerHTML;
    heroTitle.innerHTML = '';
    heroTitle.style.borderRight = '2px solid #2563eb';
    
    let i = 0;
    const timer = setInterval(() => {
        if (i < text.length) {
            heroTitle.innerHTML += text.charAt(i);
            i++;
        } else {
            clearInterval(timer);
            setTimeout(() => {
                heroTitle.style.borderRight = 'none';
            }, 500);
        }
    }, 50);
}

// Statistics counter animation
function animateCounters() {
    const stats = document.querySelectorAll('.stat-number');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const finalValue = target.textContent;
                const isNumber = !isNaN(finalValue);
                
                if (isNumber) {
                    let current = 0;
                    const increment = finalValue / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= finalValue) {
                            target.textContent = finalValue;
                            clearInterval(timer);
                        } else {
                            target.textContent = Math.floor(current);
                        }
                    }, 30);
                }
                observer.unobserve(target);
            }
        });
    });

    stats.forEach(stat => observer.observe(stat));
}

// Initialize counter animation
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(animateCounters, 1000);
});

// Newsletter subscription (placeholder)
function subscribeNewsletter(email) {
    console.log('Newsletter subscription:', email);
    alert('뉴스레터 구독이 완료되었습니다!');
}

// FAQ toggle functionality (if added later)
function toggleFAQ(element) {
    const answer = element.nextElementSibling;
    const isOpen = answer.classList.contains('open');
    
    // Close all other FAQs
    document.querySelectorAll('.faq-answer.open').forEach(item => {
        item.classList.remove('open');
    });
    
    // Toggle current FAQ
    if (!isOpen) {
        answer.classList.add('open');
    }
}

// Search functionality (placeholder)
function searchFeatures(query) {
    console.log('Search query:', query);
    // Implementation for searching features
}

// Performance monitoring
function trackEvent(eventName, eventData) {
    console.log('Event tracked:', eventName, eventData);
    // Implementation for analytics tracking
}

// Track button clicks for analytics
document.addEventListener('click', function(e) {
    if (e.target.matches('.btn-primary, .btn-secondary, .btn-signup, .btn-login')) {
        trackEvent('button_click', {
            button_text: e.target.textContent.trim(),
            button_class: e.target.className
        });
    }
});

// Error handling for forms
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        background: #fee2e2;
        color: #dc2626;
        padding: 12px;
        border-radius: 8px;
        margin: 10px 0;
        border: 1px solid #fecaca;
    `;
    
    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
    
    return errorDiv;
}

function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    successDiv.style.cssText = `
        background: #dcfce7;
        color: #166534;
        padding: 12px;
        border-radius: 8px;
        margin: 10px 0;
        border: 1px solid #bbf7d0;
    `;
    
    setTimeout(() => {
        successDiv.remove();
    }, 5000);
    
    return successDiv;
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // ESC to close modals
    if (e.key === 'Escape') {
        closeModal('loginModal');
        closeModal('signupModal');
    }
    
    // Ctrl/Cmd + K for search (placeholder)
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        console.log('Search shortcut triggered');
    }
});

// Initialize tooltips and other interactive elements
function initializeTooltips() {
    // Add tooltip functionality if needed
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            // Show tooltip
        });
        element.addEventListener('mouseleave', function() {
            // Hide tooltip
        });
    });
}

// Call initialization functions
document.addEventListener('DOMContentLoaded', initializeTooltips);