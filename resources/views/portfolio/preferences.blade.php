<div class="row portfolio-preferences" hidden>
    <div class="column">
        <label for="portfolio-template">Portfolio template</label>
        <select id="portfolio-template" autocomplete="off">
            @foreach ($templatePreferences['options'] as $template)
                <option value="{{ $template['id'] }}" @selected($template['id'] === $templatePreferences['default_template'])>{{ $template['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>
