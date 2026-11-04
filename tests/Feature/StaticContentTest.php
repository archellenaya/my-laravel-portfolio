<?php

namespace Tests\Feature;

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
        $this->assertSame(array_keys(config('cms')), array_keys($snapshot));
        $this->assertArrayNotHasKey('users', $snapshot);
        $this->assertArrayNotHasKey('password', $snapshot['profiles'][0]);
    }

    public function test_static_home_and_resume_work_without_database_queries(): void
    {
        $snapshot = json_decode(file_get_contents(database_path('seeders/portfolio.json')), true);
        $snapshot['profiles'][0]['display_name'] = 'Static-only name';
        file_put_contents($this->snapshot, json_encode($snapshot));
        config(['portfolio.static' => true]);
        DB::shouldReceive('table')->never();
        DB::shouldReceive('transaction')->never();
        $this->get('/')->assertOk()->assertSee('Static-only name');
        $this->get('/download-cv')->assertDownload('archell-enaya-resume.pdf');
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
