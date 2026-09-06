/* Applies the saved theme before first paint (prevents a flash of the
   wrong theme). Must load synchronously in <head>, before any CSS that
   depends on [data-theme]/.dark — an external file (not an inline
   <script>) so it runs under the site's CSP without needing
   'unsafe-inline' in script-src.

   Defaults to light unless the visitor has explicitly picked dark via
   the toggle (stored in localStorage) — deliberately ignores the OS/
   browser's prefers-color-scheme, so a visitor whose device happens to
   be set to dark mode still sees the site's light design by default. */
(function () {
    var t = localStorage.getItem('scotsa-theme') || 'light';
    document.documentElement.setAttribute('data-theme', t);
    if (t === 'dark') document.documentElement.classList.add('dark');
})();
