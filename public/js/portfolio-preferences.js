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

    function savePreference(key, value) {
        try {
            window.localStorage.setItem(key, value);
        } catch {
            // Preferences still apply for this page view.
        }
    }

    const storedTheme = readPreference(themeKey);
    let themePreference = themes.includes(storedTheme) ? storedTheme : defaultTheme;

    function applyTemplate(requested) {
        const template = enabled.includes(requested) ? requested : defaultTemplate;
        root.dataset.template = template;
        root.dataset.theme = themed.includes(template) ? themePreference : '';
    }

    applyTemplate(readPreference(templateKey));

    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('portfolio-template');
        const themeSelect = document.getElementById('portfolio-theme');
        const themeField = document.getElementById('portfolio-theme-field');
        if (!select || !themeSelect || !themeField) return;

        function syncControls() {
            select.value = root.dataset.template;
            themeSelect.value = themePreference;
            themeField.hidden = !themed.includes(select.value);
        }

        syncControls();
        select.addEventListener('change', () => {
            const previous = root.dataset.template;
            applyTemplate(select.value);
            syncControls();
            savePreference(templateKey, select.value);
            if (select.value !== previous) window.portfolioTrack?.('template_choice', { template: select.value });
        });
        themeSelect.addEventListener('change', () => {
            themePreference = themes.includes(themeSelect.value) ? themeSelect.value : defaultTheme;
            applyTemplate(select.value);
            syncControls();
            savePreference(themeKey, themePreference);
        });
        select.closest('.portfolio-preferences').hidden = false;
    });
})();
