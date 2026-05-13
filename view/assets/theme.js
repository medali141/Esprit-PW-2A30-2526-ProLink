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

    function bindMobileNav() {
        function closeAllNavs() {
            document.querySelectorAll('.fo-topnav.fo-nav-open').forEach(function (nav) {
                nav.classList.remove('fo-nav-open');
                var t = nav.querySelector('.js-nav-toggle');
                if (t) {
                    t.setAttribute('aria-expanded', 'false');
                }
            });
        }

        document.querySelectorAll('.js-nav-toggle').forEach(function (btn) {
            var nav = btn.closest('.fo-topnav');
            if (!nav) {
                return;
            }
            var panelId = btn.getAttribute('aria-controls');
            var panel = panelId ? document.getElementById(panelId) : null;

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var willOpen = !nav.classList.contains('fo-nav-open');
                closeAllNavs();
                if (willOpen) {
                    nav.classList.add('fo-nav-open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });

            if (panel) {
                panel.addEventListener('click', function (e) {
                    var a = e.target.closest('a');
                    if (a) {
                        closeAllNavs();
                    }
                });
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.fo-topnav')) {
                closeAllNavs();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllNavs();
            }
        });
        window.addEventListener('resize', function () {
            if (window.matchMedia('(min-width: 921px)').matches) {
                closeAllNavs();
            }
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
        bindMobileNav();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
