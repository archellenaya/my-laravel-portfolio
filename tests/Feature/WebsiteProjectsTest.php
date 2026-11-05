<?php

namespace Tests\Feature;

use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebsiteProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_adds_all_websites_once_and_preserves_existing_edits(): void
    {
        DB::table('projects')->insert(['title' => 'Existing project', 'sort_order' => 0]);
        $this->artisan('portfolio:import-projects')->assertSuccessful();
        $this->assertSame(23, DB::table('projects')->count());
        DB::table('projects')->where('title', 'Juniors')->update(['description' => 'My specific contribution']);
        $this->artisan('portfolio:import-projects')->assertSuccessful();
        $this->assertSame(23, DB::table('projects')->count());
        $this->assertSame('My specific contribution', DB::table('projects')->where('title', 'Juniors')->value('description'));
    }

    public function test_image_refresh_changes_only_the_image_of_existing_projects(): void
    {
        $this->artisan('portfolio:import-projects')->assertSuccessful();
        DB::table('projects')->where('title', 'Juniors')->update(['image' => 'old-thumbnail.jpg', 'description' => 'Preserve my contribution']);
        $this->artisan('portfolio:import-projects', ['--refresh-images' => true])->assertSuccessful();
        $project = DB::table('projects')->where('title', 'Juniors')->first();
        $catalogue = json_decode(file_get_contents(database_path('seeders/website-projects.json')), true);
        $expected = collect($catalogue)->firstWhere('title', 'Juniors');
        $this->assertSame($expected['image'], $project->image);
        $this->assertSame('Preserve my contribution', $project->description);
        $this->assertSame(22, DB::table('projects')->count());
    }

    public function test_seeded_home_includes_all_websites_and_rejects_unsafe_project_links(): void
    {
        config(['portfolio.static' => false]);
        $this->seed(PortfolioSeeder::class);
        $response = $this->get('/')->assertOk();
        $websites = json_decode(file_get_contents(database_path('seeders/website-projects.json')), true);
        $this->assertCount(22, $websites);
        foreach ($websites as $project) {
            $response->assertSee($project['title'])->assertSee('href="'.$project['website_url'].'"', false);
            $this->assertFileExists(public_path($project['image']));
        }
        DB::table('projects')->where('title', 'Juniors')->update(['website_url' => 'javascript:alert(1)']);
        $this->get('/')->assertOk()->assertDontSee('href="javascript:alert(1)"', false);
    }
}
