<?php

namespace App\Services;

class PortfolioContract
{
    public static function canonicalUrl(): string
    {
        return rtrim(self::definition()['public_origin'], '/').'/';
    }

    public static function definition(): array
    {
        return json_decode(file_get_contents(config_path('portfolio-contract.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public static function templatePreferences(array $contract): array
    {
        $settings = $contract['template_preferences'];
        $templates = array_values(array_filter($settings['templates'], fn ($template) => $template['enabled'] === true));
        if (! $templates) {
            throw new \RuntimeException('Enable at least one portfolio template.');
        }

        $ids = array_column($templates, 'id');
        $default = in_array($settings['default_template'], $ids, true) ? $settings['default_template'] : $ids[0];
        $themed = array_column(array_filter($templates, fn ($template) => $template['supports_theme'] === true), 'id');

        return [
            'options' => $templates,
            'default_template' => $default,
            'default_theme' => $settings['default_theme'],
            'enabled_templates' => implode(' ', $ids),
            'themed_templates' => implode(' ', $themed),
            'initial_theme' => in_array($default, $themed, true) ? $settings['default_theme'] : '',
        ];
    }

    public static function normalize(array $content): array
    {
        $contract = self::definition();
        if (array_key_exists('schema_version', $content) && $content['schema_version'] !== $contract['version']) {
            throw new \RuntimeException('Unsupported portfolio schema version.');
        }

        $normalized = ['schema_version' => $contract['version']];
        foreach ($contract['sections'] as $section => $rules) {
            $rows = $content[$section] ?? (($rules['optional'] ?? false) ? [] : null);
            if (! is_array($rows) || ! array_is_list($rows)) {
                throw new \RuntimeException("Invalid portfolio section: {$section}.");
            }

            $normalized[$section] = array_map(function ($row) use ($rules, $section) {
                if (! is_array($row) || array_is_list($row)) {
                    throw new \RuntimeException("Invalid portfolio record: {$section}.");
                }
                foreach ($rules['fields'] as $field) {
                    $row[$field] ??= $rules['defaults'][$field] ?? null;
                    if (! is_string($row[$field])) {
                        throw new \RuntimeException("Invalid portfolio field: {$section}.{$field}.");
                    }
                }

                return $row;
            }, $rows);
        }

        $studies = $content['case_studies'] ?? [];
        if (! is_array($studies) || ! array_is_list($studies)) {
            throw new \RuntimeException('Invalid portfolio case studies.');
        }
        $normalized['case_studies'] = $studies;

        return $normalized;
    }

    public static function projectWebsite(?string $website): ?string
    {
        if (! $website || ! preg_match('~^https?://~i', $website) || str_contains($website, '\\')) {
            return null;
        }

        return filter_var($website, FILTER_VALIDATE_URL) === false ? null : $website;
    }
}
