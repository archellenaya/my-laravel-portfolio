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

            return PortfolioContract::normalize($content);
        } catch (\Throwable $error) {
            report($error);
            abort(503, 'Static portfolio content is unavailable.');
        }
    }
}
