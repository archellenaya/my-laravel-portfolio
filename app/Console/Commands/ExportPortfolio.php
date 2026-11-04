<?php

namespace App\Console\Commands;

use App\Services\PortfolioContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportPortfolio extends Command
{
    protected $signature = 'portfolio:export';

    protected $description = 'Export current public CMS content to the static JSON snapshot';

    public function handle(PortfolioContent $content): int
    {
        $rows = $content->database();
        if (empty($rows['profiles'])) {
            $this->error('No profile found. The existing snapshot has not been overwritten.');

            return self::FAILURE;
        }
        $path = config('portfolio.snapshot');
        File::ensureDirectoryExists(dirname($path));
        File::replace($path, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL);
        $this->info('Exported public content to '.$path);
        foreach ($rows as $table => $records) {
            $this->line($table.': '.count($records));
        }

        return self::SUCCESS;
    }
}
