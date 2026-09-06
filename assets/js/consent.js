/* Cookie consent banner + gated Google Analytics loading.
   GA is never requested at all until a visitor explicitly accepts —
   declining (or ignoring the banner) means zero tracking scripts load. */
(function () {
    var STORAGE_KEY = 'scot-cookie-consent';

    function loadGoogleAnalytics(measurementId) {
        if (!measurementId || window.__scotGaLoaded) return;
        window.__scotGaLoaded = true;
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', measurementId, { anonymize_ip: true });

        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(measurementId);
        document.head.appendChild(script);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var banner = document.getElementById('cookie-consent');
        if (!banner) return;

        var gaId = document.body.dataset.gaId || '';
        var stored = null;
        try { stored = localStorage.getItem(STORAGE_KEY); } catch (err) { /* storage blocked — treat as not-yet-decided */ }

        if (stored === 'accepted') {
            loadGoogleAnalytics(gaId);
            return; // already decided, no need to show the banner again
        }
        if (stored === 'declined') {
            return;
        }

        requestAnimationFrame(function () { banner.classList.add('visible'); });

        function decide(value) {
            try { localStorage.setItem(STORAGE_KEY, value); } catch (err) { /* ignore — banner just reappears next visit */ }
            banner.classList.remove('visible');
            if (value === 'accepted') loadGoogleAnalytics(gaId);
        }

        var acceptBtn = banner.querySelector('.cookie-accept');
        var declineBtn = banner.querySelector('.cookie-decline');
        if (acceptBtn) acceptBtn.addEventListener('click', function () { decide('accepted'); });
        if (declineBtn) declineBtn.addEventListener('click', function () { decide('declined'); });
    });
})();
