        </main>
    </div>
</div>

<?php require_once __DIR__ . '/pdf_modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
/* SCOTSA Admin: SweetAlert2 Integration */
(function () {
    'use strict';

    /* ── Branded base mixin ─────────────────────────────── */
    const swal = Swal.mixin({
        customClass: {
            popup:         'swal-scotsa-popup',
            title:         'swal-scotsa-title',
            htmlContainer: 'swal-scotsa-body',
            confirmButton: 'swal-scotsa-btn swal-scotsa-btn--primary',
            cancelButton:  'swal-scotsa-btn swal-scotsa-btn--secondary',
        },
        buttonsStyling: false,
        showClass: {
            popup: 'swal2-show swal-scotsa-in',
        },
        hideClass: {
            popup: 'swal2-hide swal-scotsa-out',
        },
    });

    /* ── Toast mixin ────────────────────────────────────── */
    const Toast = Swal.mixin({
        toast:             true,
        position:          'top-end',
        showConfirmButton: false,
        timer:             4500,
        timerProgressBar:  true,
        customClass: {
            popup:       'swal-scotsa-toast',
            timerProgressBar: 'swal-scotsa-progress',
        },
        showClass:  { popup: 'swal-scotsa-toast-in' },
        hideClass:  { popup: 'swal-scotsa-toast-out' },
        didOpen(toast) {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
    });

    /* ── Fire flash messages from PHP session ───────────── */
    const flashEl = document.getElementById('scotsa-flash');
    if (flashEl) {
        try {
            const flashes = JSON.parse(flashEl.textContent || '[]');
            flashes.forEach((msg, i) => {
                setTimeout(() => {
                    Toast.fire({
                        icon:  msg.type === 'success' ? 'success'
                             : msg.type === 'warning'  ? 'warning'
                             : 'error',
                        title: msg.message,
                    });
                }, i * 600);
            });
        } catch (_) { /* guard against malformed JSON */ }
    }

    /* ── Delete / dangerous action confirmations ────────── */
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.preventDefault();
            e.stopPropagation();

            const result = await swal.fire({
                title: 'Are you sure?',
                text:  btn.dataset.confirm || 'This action cannot be undone.',
                icon:  'warning',
                showCancelButton:  true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText:  'Cancel',
                reverseButtons:    true,
                focusCancel:       true,
                customClass: {
                    popup:         'swal-scotsa-popup',
                    title:         'swal-scotsa-title',
                    htmlContainer: 'swal-scotsa-body',
                    confirmButton: 'swal-scotsa-btn swal-scotsa-btn--danger',
                    cancelButton:  'swal-scotsa-btn swal-scotsa-btn--secondary',
                },
            });

            if (result.isConfirmed) {
                const form = btn.closest('form');
                if (form) form.submit();
            }
        });
    });

    /* ── Logout confirmation ────────────────────────────── */
    document.querySelectorAll('[data-logout]').forEach(link => {
        link.addEventListener('click', async e => {
            e.preventDefault();
            const href = link.getAttribute('href') || 'logout.php';

            const result = await swal.fire({
                title:             'Sign out?',
                text:              'You will be returned to the login page.',
                icon:              'question',
                showCancelButton:  true,
                confirmButtonText: 'Yes, sign out',
                cancelButtonText:  'Stay logged in',
                reverseButtons:    true,
                focusCancel:       true,
            });

            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'post';
                form.action = href;
                form.style.display = 'none';

                const csrf = document.createElement('input');
                csrf.type  = 'hidden';
                csrf.name  = 'csrf_token';
                csrf.value = link.dataset.csrf || '';
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    /* ── Expose toast helper globally for inline use ────── */
    window.ScotSA = {
        toast(type, message) {
            Toast.fire({ icon: type, title: message });
        },
        confirm(message, onConfirm, danger = true) {
            swal.fire({
                title: 'Are you sure?',
                text:  message,
                icon:  'warning',
                showCancelButton:  true,
                confirmButtonText: danger ? 'Yes, proceed' : 'Confirm',
                cancelButtonText:  'Cancel',
                reverseButtons:    true,
                focusCancel:       true,
                customClass: {
                    popup:         'swal-scotsa-popup',
                    title:         'swal-scotsa-title',
                    htmlContainer: 'swal-scotsa-body',
                    confirmButton: danger
                        ? 'swal-scotsa-btn swal-scotsa-btn--danger'
                        : 'swal-scotsa-btn swal-scotsa-btn--primary',
                    cancelButton: 'swal-scotsa-btn swal-scotsa-btn--secondary',
                },
            }).then(r => { if (r.isConfirmed && onConfirm) onConfirm(); });
        },
    };
})();
</script>
</body>
</html>
