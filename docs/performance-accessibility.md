# Performance and accessibility improvements

The supplied deployed-site screenshot showed Performance 68, Accessibility 80, and LCP 37.3 seconds. The main page previously waited for window.load (including large images) and a multi-step animation before revealing the content.

Changes remove that loading gate and the 200 KB animation/plugin script, replace it with progressive enhancement using native browser features, lazy-load below-the-fold images, and replace the default 3023-pixel PNG portraits with resized JPEGs. The regular portrait is 46,188 bytes and the high-resolution portrait is 95,649 bytes, compared with roughly 6.6 MB for each original PNG. Existing PNG files are retained for compatibility but are no longer referenced by the default profile. Font loading no longer relies on a CSS @import, uses fewer weights and early preconnect hints, and retains font-display:swap.

Both Laravel and static templates include a skip link, a labeled navigation button with expanded state, visible keyboard focus, named project links, descriptive image text, improved heading order and contrast, and native dialogs with close controls, Escape support and focus restoration. Mobile navigation is excluded from keyboard focus while collapsed. Content remains visible without JavaScript, and smooth scrolling respects reduced-motion preferences.

## Validation

- Local mobile Lighthouse: **Performance 99, Accessibility 100**. FCP 1.5 s; LCP 1.5 s; TBT 0 ms; CLS 0.048; Speed Index 1.5 s. See [measurement summary](lighthouse-after.json).
- Chromium + axe checks found zero WCAG 2 A/AA and WCAG 2.1 AA violations on the page and with a project dialog open.
- Browser checks passed for mobile menu state, Escape dismissal, dialog focus restoration and visible content without JavaScript. Mobile and desktop layouts were visually inspected.
- 32 PHP tests and 5 static-build tests pass.

These are measurements of the generated site served locally with compression and the configured static security headers. They are not a rerun of the deployed site's original test under identical network conditions. Commit the templates, scripts, CSS, optimized JPEGs and updated JSON, redeploy Vercel, and rerun mobile Lighthouse against the public domain. Automated accessibility checks do not substitute for a full manual accessibility review. Remaining Lighthouse opportunities include CSS minification, cache lifetime tuning and further image/font optimization.
