/**
 * AliStack Learner - Core Application JS
 * Vanilla ES6+
 */

document.addEventListener('DOMContentLoaded', () => {
    // CSRF Token Helper
    window.getCsrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    // User Profile Dropdown
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');
    if (userMenuBtn && userMenu) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('show');
            if (notifMenu) notifMenu.classList.remove('show');
        });
    }

    // Notifications Dropdown
    const notifBellBtn = document.getElementById('notifBellBtn');
    const notifMenu = document.getElementById('notifMenu');
    if (notifBellBtn && notifMenu) {
        notifBellBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifMenu.classList.toggle('show');
            if (userMenu) userMenu.classList.remove('show');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (userMenu && !userMenu.contains(e.target) && e.target !== userMenuBtn) {
            userMenu.classList.remove('show');
        }
        if (notifMenu && !notifMenu.contains(e.target) && e.target !== notifBellBtn) {
            notifMenu.classList.remove('show');
        }
    });

    // Mark All Notifications Read
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch(window.location.origin + '/api/notifications/mark-read.php', {
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
                }
            } catch (err) {
                console.error('Failed to mark notifications read', err);
            }
        });
    }

    // Modal Helpers
    window.openModal = (id) => {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('show');
    };

    window.closeModal = (id) => {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('show');
    };

    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Generic form confirm delete
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const msg = el.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
});
