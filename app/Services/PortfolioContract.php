<?php

namespace App\Services;

class PortfolioContract
{
    public static function definition(): array
    {
        return json_decode(file_get_contents(config_path('portfolio-contract.json')), true, 512, JSON_THROW_ON_ERROR);
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
