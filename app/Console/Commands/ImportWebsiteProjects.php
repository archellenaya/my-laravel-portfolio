<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWebsiteProjects extends Command
{
    protected $signature = 'portfolio:import-projects {--refresh-images : Refresh existing project images from the reviewed catalogue}';

    protected $description = 'Add the reviewed website projects without overwriting existing CMS entries';

    public function handle(): int
    {
        $projects = json_decode(file_get_contents(database_path('seeders/website-projects.json')), true, 512, JSON_THROW_ON_ERROR);
        $added = DB::transaction(function () use ($projects) {
            $added = 0;
            foreach ($projects as $project) {
                $existing = DB::table('projects')->where('website_url', $project['website_url'])->orWhere('title', $project['title'])->first();
                if ($existing) {
                    if ($this->option('refresh-images') && is_file(public_path($project['image']))) {
                        DB::table('projects')->where('id', $existing->id)->update(['image' => $project['image'], 'updated_at' => now()]);
                    }
                    continue;
                }
                DB::table('projects')->insert($project + ['created_at' => now(), 'updated_at' => now()]);
                $added++;
            }

            return $added;
        });
        $this->info("Added {$added} website projects.");
        if ($this->option('refresh-images')) {
            $this->info('Existing project images refreshed from the reviewed catalogue.');
        }

        return self::SUCCESS;
    }
}
