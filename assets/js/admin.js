/* SCOT Admin — themed SweetAlert2 wiring.
   Replaces native confirm()/alert() with modals styled to match the
   admin palette, and turns server-side flash messages into toasts. */
(function () {
    if (typeof Swal === 'undefined') return;

    var themed = Swal.mixin({
        buttonsStyling: false,
        reverseButtons: true,
        customClass: {
            popup: 'admin-swal-popup',
            title: 'admin-swal-title',
            htmlContainer: 'admin-swal-text',
            confirmButton: 'admin-btn admin-btn-danger',
            cancelButton: 'admin-btn admin-btn-secondary',
        },
    });

    // Any element with data-confirm="message" gets a themed confirm
    // dialog instead of the browser's native one. Works on <form> submits
    // (delete buttons) and on plain <button>/<a> clicks (e.g. removing a
    // repeater row) — the caller marks which via data-confirm-target.
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('form[data-confirm]');
        if (!form || form.dataset.confirmed === '1') return;
        e.preventDefault();
        themed.fire({
            title: form.dataset.confirm,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmLabel || 'Delete',
            cancelButtonText: 'Cancel',
        }).then(function (result) {
            if (result.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-confirm-click]');
        if (!btn) return;
        e.preventDefault();
        themed.fire({
            title: btn.dataset.confirmClick,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: btn.dataset.confirmLabel || 'Remove',
            cancelButtonText: 'Cancel',
        }).then(function (result) {
            if (result.isConfirmed) {
                var targetSel = btn.dataset.confirmTarget;
                var target = targetSel ? btn.closest(targetSel) : btn;
                if (target) target.remove();
            }
        });
    });

    // Server-side flash messages (.admin-flash, rendered once on page load)
    // become toasts instead of a static banner.
    document.querySelectorAll('.admin-flash').forEach(function (el) {
        var isError = el.classList.contains('admin-flash-error');
        Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: isError ? 6000 : 3800,
            timerProgressBar: true,
            customClass: { popup: 'admin-swal-toast' },
        }).fire({
            icon: isError ? 'error' : 'success',
            title: el.textContent.trim(),
        });
        el.remove();
    });
})();

/* Repeater fields (add/remove line) and the Programmes tag-select toggle —
   plain DOM manipulation with no SweetAlert2 dependency, so these still
   work even if that CDN script fails to load. Every row (server-rendered
   or added here) is removed via delegated [data-remove-row] clicks, so
   newly-inserted rows need no extra wiring. */
(function () {
    function makeRow(html) {
        var row = document.createElement('div');
        row.className = 'admin-repeater-row';
        row.innerHTML = html;
        return row;
    }
    function focusFirstInput(row) {
        var input = row.querySelector('input');
        if (input) input.focus();
    }
    var removeBtnHtml = '<button type="button" class="admin-repeater-remove" data-remove-row>&times;</button>';

    document.addEventListener('click', function (e) {
        var removeBtn = e.target.closest('[data-remove-row]');
        if (removeBtn) {
            removeBtn.parentElement.remove();
            return;
        }

        var addLine = e.target.closest('[data-add-repeater]');
        if (addLine) {
            var name = addLine.dataset.addRepeater;
            var wrap = document.querySelector('[data-repeater="' + name + '"] .admin-repeater-list');
            if (!wrap) return;
            var row = makeRow('<input class="admin-input" type="text" name="' + name + '[]" value="">' + removeBtnHtml);
            wrap.appendChild(row);
            focusFirstInput(row);
            return;
        }

        var addCourse = e.target.closest('[data-add-course-row]');
        if (addCourse) {
            var level = addCourse.dataset.level, sem = addCourse.dataset.sem;
            var courseWrap = document.querySelector('[data-course-repeater="' + level + '-' + sem + '"]');
            if (!courseWrap) return;
            var courseRow = makeRow(
                '<input class="admin-input" style="max-width:120px;" type="text" placeholder="Code" name="courses[' + level + '][' + sem + '][][code]" value="">' +
                '<input class="admin-input" type="text" placeholder="Course title" name="courses[' + level + '][' + sem + '][][title]" value="">' +
                removeBtnHtml
            );
            courseWrap.appendChild(courseRow);
            focusFirstInput(courseRow);
            return;
        }

        var addMsc = e.target.closest('[data-add-msc-row]');
        if (addMsc) {
            var group = addMsc.dataset.group;
            var mscWrap = document.querySelector('[data-msc-repeater="' + group + '"]');
            if (!mscWrap) return;
            var mscRow = makeRow('<input class="admin-input" type="text" placeholder="Course title" name="msc_courses[' + group + '][]" value="">' + removeBtnHtml);
            mscWrap.appendChild(mscRow);
            focusFirstInput(mscRow);
            return;
        }

        var addStack = e.target.closest('[data-add-stack-row]');
        if (addStack) {
            var stackWrap = document.querySelector('[data-repeater="stack"] .admin-repeater-list');
            if (!stackWrap) return;
            var stackRow = makeRow('<input class="admin-input" type="text" name="stack[]" value="">' + removeBtnHtml);
            stackWrap.appendChild(stackRow);
            focusFirstInput(stackRow);
        }
    });

    // Programmes screen: which course-structure section shows depends on
    // the selected programme type (BSc/Diploma use levels+semesters, MSc
    // uses core/elective, Short Courses has neither).
    var tagSelect = document.getElementById('tag-select');
    if (tagSelect) {
        var toggleCourseSections = function () {
            var tag = tagSelect.value;
            var levels = document.getElementById('section-levels');
            var msc = document.getElementById('section-msc');
            var short = document.getElementById('section-short');
            if (levels) levels.style.display = (tag === 'BSc' || tag === 'Diploma') ? '' : 'none';
            if (msc) msc.style.display = (tag === 'MSc') ? '' : 'none';
            if (short) short.style.display = (tag === 'Short Courses') ? '' : 'none';
        };
        tagSelect.addEventListener('change', toggleCourseSections);
        toggleCourseSections();
    }
})();
