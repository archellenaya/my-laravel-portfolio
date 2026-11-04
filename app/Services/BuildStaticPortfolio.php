<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

class BuildStaticPortfolio
{
    public function build(): void
    {
        $lock = Cache::lock('portfolio-static-build', 180);
        if (! $lock->get()) {
            throw new \RuntimeException('A static build is already running. Please wait and try again.');
        }

        try {
            if (Artisan::call('portfolio:export') !== 0) {
                throw new \RuntimeException('Export failed: '.Artisan::output());
            }
            $command = ['node', base_path('scripts/build-static.mjs'), '--stage-assets'];
            $result = Process::path(base_path())->timeout(60)->run($command);
            if (! $result->successful()) {
                throw new \RuntimeException('Static build failed: '.$result->errorOutput());
            }
        } finally {
            $lock->release();
        }
    }
}
