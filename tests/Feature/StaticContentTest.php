<?php

namespace Tests\Feature;

use App\Services\PortfolioContract;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaticContentTest extends TestCase
{
    use RefreshDatabase;

    private string $snapshot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshot = tempnam(sys_get_temp_dir(), 'portfolio-snapshot-');
        config(['portfolio.snapshot' => $this->snapshot]);
    }

    protected function tearDown(): void
    {
        @unlink($this->snapshot);
        parent::tearDown();
    }

    public function test_export_uses_current_database_content_and_excludes_authentication_data(): void
    {
        $this->seed(PortfolioSeeder::class);
        DB::table('profiles')->update(['fullname' => 'Current database name']);
        $this->artisan('portfolio:export')->assertSuccessful();
        $snapshot = json_decode(file_get_contents($this->snapshot), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Current database name', $snapshot['profiles'][0]['fullname']);
        $this->assertSame(1, $snapshot['schema_version']);
        unset($snapshot['schema_version']);
        $this->assertSame(array_keys(config('cms')), array_keys($snapshot));
        $sections = PortfolioContract::definition()['sections'];
        foreach (config('cms') as $section => $fields) {
            $this->assertSame(array_keys($fields), $sections[$section]['fields']);
        }
        $this->assertArrayNotHasKey('users', $snapshot);
        $this->assertArrayNotHasKey('password', $snapshot['profiles'][0]);
    }

    public function test_static_home_and_resume_work_without_database_queries(): void
    {
        $snapshot = json_decode(file_get_contents(database_path('seeders/portfolio.json')), true);
        $snapshot['profiles'][0]['display_name'] = 'Static-only name';
        unset($snapshot['profiles'][0]['headshot'], $snapshot['projects'][0]['website_url'], $snapshot['tools']);
        $snapshot['projects'][1]['website_url'] = 'https://example.test/?a=1&b=2';
        $snapshot['projects'][2]['website_url'] = 'https://example.test\\spoof.test';
        file_put_contents($this->snapshot, json_encode($snapshot));
        config(['portfolio.static' => true]);
        DB::shouldReceive('table')->never();
        DB::shouldReceive('transaction')->never();
        $response = $this->get('/')->assertOk()
            ->assertSee('Static-only name')
            ->assertSee('data-template="modern"', false)
            ->assertSee('data-theme="system"', false)
            ->assertSee('<link rel="canonical" href="https://www.archellenaya.com/">', false)
            ->assertSeeInOrder(['<script src="/js/portfolio-preferences.js"></script>', '<link rel="stylesheet"'], false)
            ->assertSee('class="row portfolio-preferences" hidden', false)
            ->assertSee('<label for="portfolio-template">Portfolio template</label>', false)
            ->assertSee('<label for="portfolio-theme">Color theme</label>', false)
            ->assertSee('id="portfolio-theme-field" hidden', false)
            ->assertSee('/css/portfolio-templates.css', false)
            ->assertSee('<option value="modern" selected>Modern Developer</option>', false)
            ->assertSee('Terminal Developer')
            ->assertSee('href="https://example.test/?a=1&amp;b=2"', false)
            ->assertSee('href="#modal-01"', false)
            ->assertSee('id="modal-01"', false)
            ->assertSee('images/formal-photo.jpg')
            ->assertDontSee('spoof.test');
        foreach (['intro', 'about', 'works', 'contact'] as $anchor) {
            $response->assertSee('id="'.$anchor.'"', false)->assertSee('href="#'.$anchor.'"', false);
        }
        $this->get('/download-cv')->assertDownload('archell-enaya-resume.pdf');
    }

    public function test_template_preferences_filter_disabled_templates_and_choose_the_configured_default(): void
    {
        $contract = PortfolioContract::definition();
        $contract['template_preferences']['templates'][0]['enabled'] = false;
        $preferences = PortfolioContract::templatePreferences($contract);
        $this->assertSame('minimal', $preferences['default_template']);
        $this->assertSame('minimal creative terminal', $preferences['enabled_templates']);
        $this->assertSame('', $preferences['initial_theme']);

        $contract['template_preferences']['default_template'] = 'creative';
        $preferences = PortfolioContract::templatePreferences($contract);
        $this->assertSame('creative', $preferences['default_template']);
    }

    public function test_database_mode_ignores_json_and_reflects_database_changes(): void
    {
        $this->seed(PortfolioSeeder::class);
        file_put_contents($this->snapshot, 'invalid JSON');
        config(['portfolio.static' => false]);
        DB::table('profiles')->update(['display_name' => 'Live database name']);
        $this->get('/')->assertOk()->assertSee('Live database name');
    }

    public function test_invalid_or_missing_json_fails_without_database_fallback(): void
    {
        config(['portfolio.static' => true]);
        file_put_contents($this->snapshot, '{bad json');
        DB::shouldReceive('table')->never();
        DB::shouldReceive('transaction')->never();
        $this->get('/')->assertStatus(503);
        file_put_contents($this->snapshot, '{"schema_version":999}');
        $this->get('/')->assertStatus(503);
        unlink($this->snapshot);
        $this->get('/download-cv')->assertStatus(503);
    }

    public function test_empty_database_does_not_overwrite_snapshot(): void
    {
        file_put_contents($this->snapshot, 'existing snapshot');
        $this->artisan('portfolio:export')->assertFailed();
        $this->assertSame('existing snapshot', file_get_contents($this->snapshot));
    }
}
