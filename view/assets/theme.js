/**
 * Mode sombre ProLink — classe html.dark-mode + localStorage prolink-theme
 */
(function () {
    var KEY = 'prolink-theme';

    function syncButtons() {
        var dark = document.documentElement.classList.contains('dark-mode');
        document.querySelectorAll('.js-theme-toggle').forEach(function (btn) {
            btn.textContent = dark ? '☀️' : '🌙';
            btn.setAttribute('aria-label', dark ? 'Activer le mode clair' : 'Activer le mode sombre');
            btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
        });
    }

    function bind() {
        syncButtons();
        document.querySelectorAll('.js-theme-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.documentElement.classList.toggle('dark-mode');
                try {
                    localStorage.setItem(
                        KEY,
                        document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light'
                    );
                } catch (e) {}
                syncButtons();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
