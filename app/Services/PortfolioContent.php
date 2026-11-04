<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PortfolioContent
{
    public function database(): array
    {
        return DB::transaction(function () {
            $content = [];
            foreach (config('cms') as $table => $fields) {
                $content[$table] = DB::table($table)
                    ->select(array_merge(['id'], array_keys($fields), ['sort_order']))
                    ->orderBy('sort_order')->orderBy('id')->get()
                    ->map(fn ($row) => (array) $row)->all();
            }

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
            foreach (config('cms') as $table => $fields) {
                if (! isset($content[$table]) || ! is_array($content[$table]) || ! array_is_list($content[$table])) {
                    throw new \RuntimeException('Invalid portfolio section.');
                }
                foreach ($content[$table] as $row) {
                    if (! is_array($row)) {
                        throw new \RuntimeException('Invalid portfolio record.');
                    }
                    foreach (array_keys($fields) as $field) {
                        if (! isset($row[$field]) || ! is_string($row[$field])) {
                            throw new \RuntimeException('Invalid portfolio field.');
                        }
                    }
                }
            }

            return $content;
        } catch (\Throwable $error) {
            report($error);
            abort(503, 'Static portfolio content is unavailable.');
        }
    }
}
