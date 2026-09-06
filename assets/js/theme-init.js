/* Applies the saved theme before first paint (prevents a flash of the
   wrong theme). Must load synchronously in <head>, before any CSS that
   depends on [data-theme]/.dark — an external file (not an inline
   <script>) so it runs under the site's CSP without needing
   'unsafe-inline' in script-src. */
(function () {
    var t = localStorage.getItem('scotsa-theme');
    if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', t);
    if (t === 'dark') document.documentElement.classList.add('dark');
})();
