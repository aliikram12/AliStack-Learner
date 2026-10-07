/**
 * AliStack Learner - Core Application Interaction Engine
 * Modern Vanilla JavaScript (ES6+) with GSAP & Lucide integration
 * Powered by AliStack
 */

(function () {
    'use strict';

    // =========================================================================
    // 1. Toast Notification System
    // =========================================================================
    class ToastManager {
        constructor() {
            this.container = null;
            this.initContainer();
        }

        initContainer() {
            if (!this.container) {
                this.container = document.querySelector('.toast-container');
                if (!this.container) {
                    this.container = document.createElement('div');
                    this.container.className = 'toast-container';
                    document.body.appendChild(this.container);
                }
            }
        }

        show(message, type = 'info', duration = 4000) {
            this.initContainer();

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            const iconMap = {
                success: 'bi-check-circle-fill',
                error: 'bi-exclamation-circle-fill',
                warning: 'bi-exclamation-triangle-fill',
                info: 'bi-info-circle-fill'
            };

            const iconClass = iconMap[type] || iconMap.info;

            toast.innerHTML = `
                <i class="bi ${iconClass} toast-icon"></i>
                <div class="toast-content">${this.escapeHtml(message)}</div>
                <button type="button" class="toast-close" aria-label="Close notification">&times;</button>
            `;

            this.container.appendChild(toast);

            const closeBtn = toast.querySelector('.toast-close');
            const removeToast = () => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(8px)';
                setTimeout(() => toast.remove(), 250);
            };

            closeBtn.addEventListener('click', removeToast);

            if (duration > 0) {
                setTimeout(removeToast, duration);
            }
        }

        success(msg, duration) { this.show(msg, 'success', duration); }
        error(msg, duration) { this.show(msg, 'error', duration); }
        warning(msg, duration) { this.show(msg, 'warning', duration); }
        info(msg, duration) { this.show(msg, 'info', duration); }

        escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    }

    window.AliToast = new ToastManager();

    // =========================================================================
    // 2. Modal Controller
    // =========================================================================
    window.AliModal = {
        open(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            // Animate with GSAP if available
            if (window.gsap) {
                const dialog = modal.querySelector('.modal-dialog');
                if (dialog) {
                    gsap.fromTo(dialog, 
                        { opacity: 0, scale: 0.94, y: 16 }, 
                        { opacity: 1, scale: 1, y: 0, duration: 0.28, ease: 'power2.out' }
                    );
                }
            }

            // Focus on first focusable element
            const focusable = modal.querySelector('button, [href], input, select, textarea');
            if (focusable) focusable.focus();
        },

        close(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    // Backward compatibility aliases
    window.openModal = (id) => window.AliModal.open(id);
    window.closeModal = (id) => window.AliModal.close(id);

    // =========================================================================
    // 3. Document Ready Initialization
    // =========================================================================
    document.addEventListener('DOMContentLoaded', () => {
        // CSRF Token Helper
        window.getCsrfToken = () => {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        };

        // Render Lucide Icons if loaded
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // Initialize Sticky Header Scroll State
        const siteHeader = document.querySelector('.site-header');
        if (siteHeader) {
            const handleScroll = () => {
                if (window.scrollY > 20) {
                    siteHeader.classList.add('scrolled');
                } else {
                    siteHeader.classList.remove('scrolled');
                }
            };
            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll();
        }

        // =====================================================================
        // 4. Student App Sidebar Controller (Desktop Collapse & Mobile Drawer)
        // =====================================================================
        const sidebar = document.querySelector('.app-sidebar');
        const sidebarCollapseBtn = document.querySelector('#sidebarCollapseBtn');
        const mobileMenuToggle = document.querySelector('#mobileSidebarToggle');
        const sidebarBackdrop = document.querySelector('#sidebarBackdrop');

        // Restore Desktop Collapsed State
        if (sidebar && window.innerWidth >= 1025) {
            const isCollapsed = localStorage.getItem('ali_sidebar_collapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            }
        }

        if (sidebarCollapseBtn && sidebar) {
            sidebarCollapseBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                const state = sidebar.classList.contains('collapsed');
                localStorage.setItem('ali_sidebar_collapsed', state ? 'true' : 'false');
            });
        }

        // Mobile Drawer Toggle
        if (mobileMenuToggle && sidebar) {
            mobileMenuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                if (sidebarBackdrop) sidebarBackdrop.classList.toggle('show');
            });
        }

        if (sidebarBackdrop && sidebar) {
            sidebarBackdrop.addEventListener('click', () => {
                sidebar.classList.remove('show');
                sidebarBackdrop.classList.remove('show');
            });
        }

        // =====================================================================
        // 5. Dropdown Controllers (Profile & Notifications)
        // =====================================================================
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenu = document.getElementById('userMenu');
        const notifBellBtn = document.getElementById('notifBellBtn');
        const notifMenu = document.getElementById('notifMenu');

        if (userMenuBtn && userMenu) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenu.classList.toggle('show');
                if (notifMenu) notifMenu.classList.remove('show');
            });
        }

        if (notifBellBtn && notifMenu) {
            notifBellBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notifMenu.classList.toggle('show');
                if (userMenu) userMenu.classList.remove('show');
            });
        }

        document.addEventListener('click', (e) => {
            if (userMenu && !userMenu.contains(e.target) && e.target !== userMenuBtn) {
                userMenu.classList.remove('show');
            }
            if (notifMenu && !notifMenu.contains(e.target) && e.target !== notifBellBtn) {
                notifMenu.classList.remove('show');
            }
        });

        // Close dropdowns & modals with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (userMenu) userMenu.classList.remove('show');
                if (notifMenu) notifMenu.classList.remove('show');
                document.querySelectorAll('.modal-backdrop.show').forEach(m => m.classList.remove('show'));
                document.body.style.overflow = '';
            }
        });

        // Modal backdrop click close
        document.querySelectorAll('.modal-backdrop').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });

        // =====================================================================
        // 6. Mark All Notifications Read API
        // =====================================================================
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    const apiUrl = (typeof window.getApiUrl === 'function') 
                        ? window.getApiUrl('api/notifications/mark-read.php') 
                        : '/api/notifications/mark-read.php';
                    const res = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.getCsrfToken()
                        },
                        body: JSON.stringify({ all: true })
                    });
                    const data = await res.json();
                    if (data.success) {
                        const badge = document.getElementById('notifBadge');
                        if (badge) badge.remove();
                        document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
                        markAllReadBtn.remove();
                        AliToast.success('All notifications marked as read.');
                    }
                } catch (err) {
                    console.error('Failed to mark notifications read', err);
                }
            });
        }

        // =====================================================================
        // 7. Generic Confirmation Handlers
        // =====================================================================
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', (e) => {
                const msg = el.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
                if (!confirm(msg)) {
                    e.preventDefault();
                }
            });
        });

        // =====================================================================
        // 8. GSAP Stagger & Entrance Animations
        // =====================================================================
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (window.gsap && !prefersReducedMotion) {
            // Register ScrollTrigger if available
            if (window.ScrollTrigger) {
                gsap.registerPlugin(ScrollTrigger);
            }

            // Hero section stagger
            const heroElements = document.querySelectorAll('.hero-anim');
            if (heroElements.length > 0) {
                gsap.from(heroElements, {
                    opacity: 0,
                    y: 28,
                    duration: 0.7,
                    stagger: 0.12,
                    ease: 'power2.out'
                });
            }

            // Stat Cards Viewport Entry
            const statCards = document.querySelectorAll('.stat-card');
            if (statCards.length > 0) {
                if (window.ScrollTrigger) {
                    gsap.from(statCards, {
                        scrollTrigger: {
                            trigger: statCards[0],
                            start: 'top 88%'
                        },
                        opacity: 0,
                        y: 20,
                        duration: 0.5,
                        stagger: 0.1,
                        ease: 'power2.out'
                    });
                } else {
                    gsap.from(statCards, {
                        opacity: 0,
                        y: 20,
                        duration: 0.5,
                        stagger: 0.1,
                        ease: 'power2.out'
                    });
                }
            }

            // Course Cards Stagger
            const courseCards = document.querySelectorAll('.course-card');
            if (courseCards.length > 0 && window.ScrollTrigger) {
                gsap.from(courseCards, {
                    scrollTrigger: {
                        trigger: courseCards[0],
                        start: 'top 88%'
                    },
                    opacity: 0,
                    y: 24,
                    duration: 0.55,
                    stagger: 0.1,
                    ease: 'power2.out'
                });
            }
        }
    });
})();
