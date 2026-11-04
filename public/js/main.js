/* Progressive enhancement: content is visible before JavaScript and images load. */
(() => {
    const html = document.documentElement;
    html.classList.replace('no-js', 'js');
    const toggle = document.querySelector('.mobile-menu-toggle');
    const navigation = document.querySelector('.main-nav-wrap');
    function menu(open) {
        toggle?.setAttribute('aria-expanded', String(open));
        toggle?.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        toggle?.classList.toggle('is-clicked', open);
        document.body.classList.toggle('menu-is-open', open);
        if (navigation) navigation.inert = matchMedia('(max-width: 800px)').matches && !open;
    }
    toggle?.addEventListener('click', () => menu(toggle.getAttribute('aria-expanded') !== 'true'));
    navigation?.addEventListener('click', event => { if (event.target.closest('a')) menu(false); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && document.body.classList.contains('menu-is-open')) {
            menu(false);
            toggle.focus();
        }
    });
    matchMedia('(max-width: 800px)').addEventListener('change', () => menu(false));
    menu(false);

    document.querySelectorAll('a[href^="#modal-"]').forEach(link => {
        const dialog = document.getElementById(link.hash.slice(1));
        if (!(dialog instanceof HTMLDialogElement)) return;
        link.addEventListener('click', event => {
            event.preventDefault();
            dialog.showModal();
            dialog.addEventListener('close', () => link.focus(), {once: true});
        });
    });
    document.querySelectorAll('.project-dialog').forEach(dialog => {
        dialog.querySelector('[data-close-dialog]')?.addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    });
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;
                document.querySelectorAll('.main-nav a[href^="#"]').forEach(link => {
                    const active = link.hash === '#' + entry.target.id;
                    link.parentElement.classList.toggle('current', active);
                    if (active) link.setAttribute('aria-current', 'location');
                    else link.removeAttribute('aria-current');
                });
            }
        }, { rootMargin: '-15% 0px -60% 0px' });
        document.querySelectorAll('.target-section').forEach(section => observer.observe(section));
    }
})();
