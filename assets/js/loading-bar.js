/* Thin top-of-page progress indicator — gives the page a "loading"
   state instead of just sitting blank/half-styled while fonts, the
   hero photo, and the stylesheet finish loading. */
(function () {
    var bar = document.getElementById('loading-bar');
    if (!bar) return;

    // Jump to ~75% almost immediately (perceived progress), then let the
    // real 'load' event (all images/fonts/css settled) finish it to 100%
    // and fade it out. Never gets stuck: 'load' has usually already fired
    // by the time this script runs on a fast connection, in which case it
    // completes on the very next frame instead of waiting.
    requestAnimationFrame(function () { bar.style.width = '75%'; });

    function finish() {
        bar.classList.add('done');
        setTimeout(function () { bar.remove(); }, 700);
    }

    if (document.readyState === 'complete') {
        finish();
    } else {
        window.addEventListener('load', finish, { once: true });
    }
})();
