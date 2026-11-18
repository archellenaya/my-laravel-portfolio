<?php

namespace App\Services;

class PortfolioPresentation
{
    public static function skills(array $snapshot, array $contract): array
    {
        $rows = array_map(fn ($row) => $row + ['description' => ''], $snapshot['expertise']);
        foreach ($snapshot['skills'] as $row) {
            $split = $contract['skill_splits'][$row['title']] ?? null;
            if ($split) {
                $technologies = array_map('trim', explode(',', $row['description']));
                $allowed = array_map('strtolower', $split['technologies']);
                $matches = array_filter($technologies, fn ($name) => in_array(strtolower($name), $allowed, true));
                if ($matches) {
                    $rows[] = ['title' => $split['title'], 'description' => implode(', ', $matches)];
                    $row['description'] = implode(', ', array_diff_key($technologies, $matches));
                    if ($row['description'] === '') {
                        continue;
                    }
                }
            }
            $rows[] = $row;
        }

        $result = [];
        foreach ($contract['skill_groups'] as $title => $titles) {
            $items = array_filter($rows, fn ($row) => in_array($row['title'], $titles, true));
            if ($items) {
                $result[] = ['title' => $title, 'items' => $items];
                $rows = array_diff_key($rows, $items);
            }
        }
        if ($rows) {
            $result[] = ['title' => 'Additional Skills', 'items' => $rows];
        }

        return $result;
    }

    public static function caseStudies(array $records, array $contract): array
    {
        $studies = array_filter($records, fn ($study) => ($study['approved'] ?? false) === true);

        return array_map(function ($study) use ($contract) {
            $details = [];
            foreach ($contract['case_study_fields'] as $key => $label) {
                if (! empty($study[$key])) {
                    $details[] = ['label' => $label, 'text' => $study[$key]];
                }
            }
            $links = [];
            foreach (['live_url' => 'Live website', 'repository_url' => 'Public repository'] as $key => $label) {
                if ($url = PortfolioContract::projectWebsite($study[$key] ?? null)) {
                    $links[] = ['label' => $label, 'url' => $url];
                }
            }
            $screenshots = [];
            $path = $study['screenshot_path'] ?? '';
            if (self::publicScreenshot($path) && ! empty($study['screenshot_alt'])) {
                $screenshots[] = ['url' => asset($path), 'alt' => $study['screenshot_alt']];
            }

            return [
                'title' => $study['title'],
                'description' => $study['description'] ?? '',
                'technologies' => self::technologies($study['technologies'] ?? ''),
                'details' => $details,
                'links' => $links,
                'screenshots' => $screenshots,
            ];
        }, $studies);
    }

    public static function technologies(string $text): array
    {
        return array_filter(array_map('trim', explode(',', $text)), fn ($name) => $name !== '');
    }

    public static function publicScreenshot(string $path): bool
    {
        if (! preg_match('~^images/(?:[\w@ -]+/)*[\w@ .-]+\.(?:png|jpe?g|webp|gif|avif)$~iD', $path)) {
            return false;
        }
        $file = realpath(public_path($path));

        return $file !== false && str_starts_with($file, realpath(public_path('images')).DIRECTORY_SEPARATOR) && is_file($file);
    }
}
