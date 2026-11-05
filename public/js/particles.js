/* Decorative binary globe: bounded work, no third-party runtime. */
(() => {
    const canvas = document.querySelector('.hero-particles');
    if (!canvas) return;
    const context = canvas.getContext('2d');
    if (!context) return;
    const surface = canvas.closest('#main-content');
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = matchMedia('(pointer: fine)');
    let width = 0, height = 0, streams = [], points = [], frame = 0, last = 0, phase = 0;
    let visible = true, pointerX = 0, pointerY = 0, tiltX = 0, tiltY = 0;
    const compact = matchMedia('(max-width: 799px)');
    let glyphs = [], ready = false;
    let rippleStarted = -Infinity;

    function resize() {
        const bounds = canvas.getBoundingClientRect();
        width = bounds.width;
        height = bounds.height;
        const density = Math.min(devicePixelRatio || 1, compact.matches ? 1 : 1.25);
        canvas.width = Math.round(width * density);
        canvas.height = Math.round(height * density);
        context.setTransform(density, 0, 0, density, 0, 0);
        const count = compact.matches ? 120 : 320;
        points = Array.from({length: count}, (_, i) => {
            const y = 1 - 2 * (i + 0.5) / count;
            const radius = Math.sqrt(1 - y * y);
            const angle = i * Math.PI * (3 - Math.sqrt(5));
            return {x: Math.cos(angle) * radius, y, z: Math.sin(angle) * radius, digit: String(i % 2)};
        });
        // Rasterize the two globe glyphs once instead of shaping text every frame.
        glyphs = ['0', '1'].map(digit => {
            const image = document.createElement('canvas');
            image.width = image.height = 48;
            const ink = image.getContext('2d');
            ink.fillStyle = '#dfb66d';
            ink.font = '32px monospace';
            ink.textAlign = 'center';
            ink.textBaseline = 'middle';
            ink.fillText(digit, 24, 24);
            return image;
        });
        // Alternate direction and vary depth to give the rear layer a soft code-rain feel.
        const columns = Math.min(compact.matches ? 16 : 40, Math.ceil(width / 36));
        streams = Array.from({length: columns}, (_, i) => {
            const size = 12 + Math.random() * 12;
            return {
                x: (i + 0.5) * width / columns,
                size,
                spacing: size * 1.65,
                offset: Math.random() * (height + size * 2),
                speed: (14 + Math.random() * 24) * (i % 2 ? 1 : -1),
                alpha: 0.16 + Math.random() * 0.3,
                digits: Array.from({length: Math.ceil(height / (size * 1.65)) + 2}, () => Math.random() < 0.5 ? '0' : '1')
            };
        });
        for (const stream of streams) {
            const strip = document.createElement('canvas');
            strip.width = Math.ceil(stream.size * 1.5);
            strip.height = Math.ceil(stream.digits.length * stream.spacing);
            const ink = strip.getContext('2d');
            ink.font = `${stream.size}px monospace`;
            ink.textAlign = 'center';
            ink.textBaseline = 'middle';
            stream.digits.forEach((digit, row) => {
                ink.globalAlpha = stream.alpha * (row % 5 === 0 ? 1 : 0.55);
                ink.fillStyle = row % 5 === 0 ? '#b9dce0' : '#327f8c';
                ink.fillText(digit, strip.width / 2, (row + 0.5) * stream.spacing);
            });
            stream.image = strip;
        }
        draw();
    }
    function drawStreams() {
        // Each cached column wraps with just two image copies per frame.
        for (const stream of streams) {
            const span = stream.image.height;
            const offset = ((stream.offset + phase * stream.speed) % span + span) % span;
            const x = stream.x - stream.image.width / 2;
            context.drawImage(stream.image, x, offset - span);
            context.drawImage(stream.image, x, offset);
        }
    }

    function draw() {
        context.clearRect(0, 0, width, height);
        drawStreams();
        context.fillStyle = '#dfb66d';
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        const radius = Math.min(width, height) * 0.4;
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
            // Ripple around the fixed viewport center on click.
            const bounce = ripple > 0 ? 1 + ripple * 0.35 * Math.sin(rippleAge * 17 - point.y * 3 - depth * 2) : 1;
            const fontSize = (10 + (depth + 1) * 2) * Math.min(Math.max(width, height) / 900, 1.4);
            const size = fontSize * 1.5;
            context.drawImage(glyphs[Number(point.digit)],
                width / 2 + x * radius * wave * perspective * bounce - size / 2,
                height / 2 + y * radius * wave * perspective * bounce - size / 2,
                size, size);
        }
        context.globalAlpha = 1;
    }
    function tick(now) {
        frame = 0;
        if (now - last >= 1000 / (compact.matches ? 15 : 24)) {
            phase += Math.min((now - last) / 1000, 0.08);
            tiltX += (pointerX - tiltX) * 0.06;
            tiltY += (pointerY - tiltY) * 0.06;
            last = now;
            draw();
        }
        frame = requestAnimationFrame(tick);
    }
    function sync() {
        if (!ready) return;
        cancelAnimationFrame(frame);
        frame = 0;
        if (motion.matches) { phase = 0; tiltX = tiltY = 0; draw(); }
        if (!motion.matches && visible && !document.hidden) {
            last = performance.now();
            frame = requestAnimationFrame(tick);
        }
    }
    surface.addEventListener('pointermove', event => {
        if (!finePointer.matches || motion.matches) return;
        if (event.pointerType === 'touch') return;
        pointerX = (event.clientX / innerWidth - 0.5) * 0.35;
        pointerY = (event.clientY / innerHeight - 0.5) * 0.2;
    }, {passive: true});
    surface.addEventListener('pointerdown', event => {
        if (motion.matches || !finePointer.matches || event.pointerType === 'touch' || event.button !== 0) return;
        rippleStarted = performance.now();
    }, {passive: true});
    surface.addEventListener('pointerleave', () => { pointerX = pointerY = 0; });
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(entries => {
            visible = entries[0].isIntersecting;
            sync();
        }).observe(canvas);
    }

    motion.addEventListener('change', sync);
    document.addEventListener('visibilitychange', sync);
    function start() {
        ready = true;
        resize();
        if ('ResizeObserver' in window) new ResizeObserver(resize).observe(canvas);
        else addEventListener('resize', resize, {passive: true});
        sync();
    }
    function scheduleStart() {
        if ('requestIdleCallback' in window) requestIdleCallback(start, {timeout: 1500});
        else setTimeout(start, 100);
    }
    if (document.readyState === 'complete') scheduleStart();
    else addEventListener('load', scheduleStart, {once: true});
})();
