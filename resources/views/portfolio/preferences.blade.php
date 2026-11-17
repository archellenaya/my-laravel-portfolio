<div class="row portfolio-preferences" hidden>
    <div class="column">
        <div>
            <label for="portfolio-template">Portfolio template</label>
            <select id="portfolio-template" autocomplete="off">
                @foreach ($templatePreferences['options'] as $template)
                    <option value="{{ $template['id'] }}" @selected($template['id'] === $templatePreferences['default_template'])>{{ $template['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div id="portfolio-theme-field" hidden>
            <label for="portfolio-theme">Color theme</label>
            <select id="portfolio-theme" autocomplete="off">
                <option value="system">System</option>
                <option value="light">Light</option>
                <option value="dark">Dark</option>
            </select>
        </div>
    </div>
</div>
