        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
/* SCOTSA Student Portal: SweetAlert2 integration */
(function () {
    'use strict';

    const swal = Swal.mixin({
        customClass: {
            popup:         'swal-scotsa-popup',
            title:         'swal-scotsa-title',
            htmlContainer: 'swal-scotsa-body',
            confirmButton: 'swal-scotsa-btn swal-scotsa-btn--primary',
            cancelButton:  'swal-scotsa-btn swal-scotsa-btn--secondary',
            timerProgressBar: 'swal-scotsa-progress',
        },
        buttonsStyling: false,
        showClass: { popup: 'swal2-show swal-scotsa-in' },
        hideClass: { popup: 'swal2-hide swal-scotsa-out' },
    });

    /* Centered modal (not a corner toast) so it's impossible to miss
       after an action like an upload/save — auto-dismisses on its own
       timer, but a click also closes it immediately. */
    const flashEl = document.getElementById('scotsa-flash');
    if (flashEl) {
        try {
            const flashes = JSON.parse(flashEl.textContent || '[]');
            (async () => {
                for (const msg of flashes) {
                    await swal.fire({
                        icon:  msg.type === 'success' ? 'success'
                             : msg.type === 'warning'  ? 'warning'
                             : 'error',
                        title: msg.type === 'success' ? 'Success' : msg.type === 'warning' ? 'Heads up' : 'Something went wrong',
                        text:  msg.message,
                        showConfirmButton: false,
                        timer: 2800,
                        timerProgressBar: true,
                    });
                }
            })();
        } catch (_) { /* guard against malformed JSON */ }
    }

    document.querySelectorAll('[data-logout]').forEach(link => {
        link.addEventListener('click', async e => {
            e.preventDefault();
            const href = link.getAttribute('href') || 'logout.php';

            const result = await swal.fire({
                title: 'Sign out?',
                text: 'You will be returned to the login page.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, sign out',
                cancelButtonText: 'Stay logged in',
                reverseButtons: true,
                focusCancel: true,
            });

            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'post';
                form.action = href;
                form.style.display = 'none';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = 'csrf_token';
                csrf.value = link.dataset.csrf || '';
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            }
        });
    });
})();
</script>
</body>
</html>
