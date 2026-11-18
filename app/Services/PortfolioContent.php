<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PortfolioContent
{
    public function database(): array
    {
        return DB::transaction(function () {
            $content = [];
            foreach (PortfolioContract::definition()['sections'] as $table => $rules) {
                $fields = $rules['fields'];
                if ($table === 'projects') {
                    $fields = array_merge($fields, ['contribution', 'technologies', 'approved']);
                }
                $content[$table] = DB::table($table)
                    ->select(array_merge(['id'], $fields, ['sort_order']))
                    ->orderBy('sort_order')->orderBy('id')->get()
                    ->map(fn ($row) => (array) $row)->all();
            }

            $content['projects'] = array_map(function ($project) {
                if (! $project['approved']) {
                    unset($project['contribution'], $project['technologies']);
                }
                unset($project['approved']);

                return $project;
            }, $content['projects']);
            $content['case_studies'] = DB::table('case_studies')->where('approved', true)
                ->orderBy('sort_order')->orderBy('id')
                ->get(array_keys(config('cms.case_studies')))->map(function ($study) {
                    $record = (array) $study;
                    $record['approved'] = true;

                    return $record;
                })->all();

            return $content;
        });
    }

    public function load(): array
    {
        if (! config('portfolio.static')) {
            return $this->database();
        }

        try {
            $path = config('portfolio.snapshot');
            if (! is_readable($path)) {
                throw new \RuntimeException('Portfolio snapshot is unavailable.');
            }
            $content = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($content)) {
                throw new \RuntimeException('Invalid portfolio snapshot.');
            }

            return PortfolioContract::normalize($content);
        } catch (\Throwable $error) {
            report($error);
            abort(503, 'Static portfolio content is unavailable.');
        }
    }
}
