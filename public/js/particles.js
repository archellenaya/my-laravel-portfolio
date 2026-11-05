/* Original decorative particle globe: bounded work, no third-party runtime. */
(() => {
    const canvas = document.querySelector('.hero-particles');
    if (!canvas) return;
    const context = canvas.getContext('2d');
    if (!context) return;
    const surface = canvas.closest('#main-content');
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = matchMedia('(pointer: fine)');
    let width = 0, height = 0, points = [], frame = 0, last = 0, phase = 0;
    let visible = true, pointerX = 0, pointerY = 0, tiltX = 0, tiltY = 0;
    let attracting = false, gather = 0, cursorX = 0, cursorY = 0, followX = 0, followY = 0;
    let rippleStarted = -Infinity;

    function resize() {
        const bounds = canvas.getBoundingClientRect();
        width = bounds.width;
        height = bounds.height;
        const density = Math.min(devicePixelRatio || 1, 1.5);
        canvas.width = Math.round(width * density);
        canvas.height = Math.round(height * density);
        context.setTransform(density, 0, 0, density, 0, 0);
        const count = innerWidth < 800 ? 180 : 480;
        points = Array.from({length: count}, (_, i) => {
            const y = 1 - 2 * (i + 0.5) / count;
            const radius = Math.sqrt(1 - y * y);
            const angle = i * Math.PI * (3 - Math.sqrt(5));
            return {x: Math.cos(angle) * radius, y, z: Math.sin(angle) * radius};
        });
        draw();
    }
    function draw() {
        context.clearRect(0, 0, width, height);
        context.fillStyle = '#dfb66d';
        const yaw = phase * 0.18 + tiltX, pitch = 0.3 + tiltY;
        const cy = Math.cos(yaw), sy = Math.sin(yaw), cp = Math.cos(pitch), sp = Math.sin(pitch);
        const rippleAge = (performance.now() - rippleStarted) / 1000;
        const ripple = rippleAge < 2 ? Math.exp(-rippleAge * 2.8) : 0;
        for (let i = 0; i < points.length; i++) {
            const point = points[i];
            const wave = 1 + 0.055 * Math.sin(phase * 0.7 + point.y * 5 + point.x * 3);
            const x = point.x * cy - point.z * sy;
            const z = point.x * sy + point.z * cy;
            const y = point.y * cp - z * sp;
            const depth = point.y * sp + z * cp;
            const perspective = 2.8 / (2.8 - depth * 0.35);
            context.globalAlpha = 0.2 + (depth + 1) * 0.32;
            const normalX = width / 2 + x * width * 0.59 * wave * perspective;
            const normalY = height / 2 + y * height * 0.59 * wave * perspective;
            const clusterRadius = Math.min(width, height) * 0.65;
            // A damped travelling wave creates a soft, liquid bounce on click.
            const bounce = ripple > 0 ? 1 + ripple * 0.35 * Math.sin(rippleAge * 17 - point.y * 3 - depth * 2) : 1;
            const gatheredX = followX + x * clusterRadius * perspective * bounce;
            const gatheredY = followY + y * clusterRadius * perspective * bounce;
            context.beginPath();
            context.arc(normalX + (gatheredX - normalX) * gather,
                normalY + (gatheredY - normalY) * gather,
                (0.8 + (depth + 1) * 0.45) * Math.min(Math.max(width, height) / 900, 1.4), 0, Math.PI * 2);
            context.fill();
        }
        context.globalAlpha = 1;
    }
    function tick(now) {
        frame = 0;
        if (now - last >= 1000 / 24) {
            phase += Math.min((now - last) / 1000, 0.08);
            tiltX += (pointerX - tiltX) * 0.06;
            tiltY += (pointerY - tiltY) * 0.06;
            gather += ((attracting ? 1 : 0) - gather) * 0.14;
            followX += (cursorX - followX) * 0.25;
            followY += (cursorY - followY) * 0.25;
            last = now;
            draw();
        }
        frame = requestAnimationFrame(tick);
    }
    function sync() {
        cancelAnimationFrame(frame);
        frame = 0;
        if (motion.matches) { phase = 0; tiltX = tiltY = gather = 0; attracting = false; draw(); }
        if (!motion.matches && visible && !document.hidden) {
            last = performance.now();
            frame = requestAnimationFrame(tick);
        }
    }
    surface.addEventListener('pointermove', event => {
        if (!finePointer.matches || motion.matches) return;
        if (event.pointerType === 'touch') return;
        const canvasBox = canvas.getBoundingClientRect();
        cursorX = event.clientX - canvasBox.left;
        cursorY = event.clientY - canvasBox.top;
        if (!attracting && gather < 0.01) { followX = cursorX; followY = cursorY; }
        attracting = true;
        const box = surface.getBoundingClientRect();
        pointerX = ((event.clientX - box.left) / box.width - 0.5) * 0.35;
        pointerY = (event.clientY / innerHeight - 0.5) * 0.2;
    }, {passive: true});
    surface.addEventListener('pointerdown', event => {
        if (motion.matches || !finePointer.matches || event.pointerType === 'touch' || event.button !== 0) return;
        const box = canvas.getBoundingClientRect();
        cursorX = event.clientX - box.left;
        cursorY = event.clientY - box.top;
        attracting = true;
        rippleStarted = performance.now();
    }, {passive: true});
    surface.addEventListener('pointerleave', () => { pointerX = pointerY = 0; attracting = false; });
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(entries => {
            visible = entries[0].isIntersecting;
            sync();
        }).observe(surface);
    }
    if ('ResizeObserver' in window) new ResizeObserver(resize).observe(canvas);
    else addEventListener('resize', resize, {passive: true});
    motion.addEventListener('change', sync);
    document.addEventListener('visibilitychange', sync);
    resize();
    sync();
})();
