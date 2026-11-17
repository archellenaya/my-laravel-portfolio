(() => {
    const root = document.documentElement;
    const { template: defaultTemplate, defaultTheme, enabledTemplates, themedTemplates } = root.dataset;
    const enabled = enabledTemplates.split(' ');
    const themed = themedTemplates.split(' ');
    const themes = ['system', 'light', 'dark'];
    const templateKey = 'portfolio.template';
    const themeKey = 'portfolio.theme';

    function readPreference(key) {
        try {
            return window.localStorage.getItem(key);
        } catch {
            return null;
        }
    }

    function applyTemplate(requested) {
        const template = enabled.includes(requested) ? requested : defaultTemplate;
        root.dataset.template = template;
        root.dataset.theme = '';
        if (themed.includes(template)) {
            const theme = readPreference(themeKey);
            root.dataset.theme = themes.includes(theme) ? theme : defaultTheme;
        }
    }

    applyTemplate(readPreference(templateKey));

    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('portfolio-template');
        if (!select) return;
        select.value = root.dataset.template;
        select.addEventListener('change', () => {
            applyTemplate(select.value);
            select.value = root.dataset.template;
            try {
                window.localStorage.setItem(templateKey, select.value);
            } catch {
                // The selected template still applies for this page view.
            }
        });
        select.closest('.portfolio-preferences').hidden = false;
    });
})();
