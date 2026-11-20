/* Progressive enhancement: content is visible before JavaScript and images load. */
(() => {
    const html = document.documentElement;
    html.classList.replace('no-js', 'js');
    const toggle = document.querySelector('.mobile-menu-toggle');
    const navigation = document.querySelector('.main-nav-wrap');
    const mobile = matchMedia('(max-width: 800px)');
    function menu(open) {
        toggle?.setAttribute('aria-expanded', String(open));
        toggle?.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        toggle?.classList.toggle('is-clicked', open);
        document.body.classList.toggle('menu-is-open', open);
        if (navigation) navigation.inert = mobile.matches && !open;
    }
    toggle?.addEventListener('click', () => menu(toggle.getAttribute('aria-expanded') !== 'true'));
    navigation?.addEventListener('click', event => {
        const link = event.target.closest('a');
        if (!link || !mobile.matches || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const section = link.hash && document.getElementById(link.hash.slice(1));
        menu(false);
        if (!section) return;
        event.preventDefault();
        if (location.hash !== link.hash) history.pushState(null, '', link.hash);
        section.tabIndex = -1;
        section.focus({preventScroll: true});
        section.scrollIntoView();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && document.body.classList.contains('menu-is-open')) {
            menu(false);
            toggle.focus();
        }
    });
    mobile.addEventListener('change', () => menu(false));
    menu(false);

    document.querySelectorAll('a[href^="#modal-"]').forEach(link => {
        const dialog = document.getElementById(link.hash.slice(1));
        if (!(dialog instanceof HTMLDialogElement)) return;
        link.addEventListener('click', event => {
            event.preventDefault();
            if (dialog.open) return;
            dialog.showModal();
            if (/^\d+$/.test(dialog.dataset.projectId ?? '')) {
                window.portfolioTrack?.('project_view', { project_id: dialog.dataset.projectId });
            }
            dialog.addEventListener('close', () => link.focus(), {once: true});
        });
    });
    document.querySelectorAll('.project-dialog').forEach(dialog => {
        dialog.querySelector('[data-close-dialog]')?.addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    });
    function trackLink(event) {
        const link = event.target.closest('a');
        if (!link || event.defaultPrevented) return;
        if (link.getAttribute('href') === '/download-cv') {
            window.portfolioTrack?.('resume_download');
        } else if (link.dataset.contactCta) {
            window.portfolioTrack?.('contact_cta', { channel: link.dataset.contactCta });
        } else if (link.dataset.social) {
            window.portfolioTrack?.('social_click', { network: link.dataset.social });
        }
    }
    document.addEventListener('click', trackLink);
    document.addEventListener('auxclick', event => { if (event.button === 1) trackLink(event); });
    // Animate on entry without hiding content or delaying the initial heading.
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    const played = new WeakSet();
    const running = new Map();
    let revealObserver;
    const revealTargets = [...document.querySelectorAll('[data-animate-el], .contact-top, .contact-block')];
    function setupReveals() {
        revealObserver?.disconnect();
        if (motion.matches) {
            running.forEach(animation => animation.cancel());
            running.clear();
            return;
        }
        if (!('IntersectionObserver' in window) || !Element.prototype.animate) return;
        revealObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting || played.has(entry.target)) return;
                const element = entry.target;
                played.add(element);
                revealObserver.unobserve(element);
                // Focused controls must always be immediately visible and usable.
                if (element.contains(document.activeElement)) return;
                const block = element.closest('[data-animate-block]');
                const siblings = block ? [...block.querySelectorAll('[data-animate-el]')] : [];
                const delay = Math.max(0, siblings.indexOf(element) % 4) * 55;
                const animation = element.animate([
                    { opacity: 0.85, transform: 'translateY(20px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], { duration: 550, delay, easing: 'cubic-bezier(.2,.7,.2,1)' });
                running.set(element, animation);
                const cleanup = () => { if (running.get(element) === animation) running.delete(element); };
                animation.onfinish = cleanup;
                animation.oncancel = cleanup;
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });
        revealTargets.forEach(element => { if (!played.has(element)) revealObserver.observe(element); });
    }
    document.addEventListener('focusin', event => {
        running.forEach((animation, element) => {
            if (element.contains(event.target)) animation.cancel();
        });
    });
    motion.addEventListener('change', setupReveals);
    setupReveals();

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
