<?php

namespace Tests\Feature;

use App\Services\PortfolioContract;
use App\Services\PortfolioPresentation;
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
        $this->assertSame([...array_keys(PortfolioContract::definition()['sections']), 'case_studies'], array_keys($snapshot));
        $this->assertSame([], $snapshot['case_studies']);
        $sections = PortfolioContract::definition()['sections'];
        foreach ($sections as $section => $rules) {
            $this->assertSame($rules['fields'], array_keys(array_intersect_key(config("cms.{$section}"), array_flip($rules['fields']))));
        }
        $this->assertArrayNotHasKey('users', $snapshot);
        $this->assertArrayNotHasKey('password', $snapshot['profiles'][0]);
    }

    public function test_static_home_and_resume_work_without_database_queries(): void
    {
        $snapshot = json_decode(file_get_contents(database_path('seeders/portfolio.json')), true);
        $snapshot['profiles'][0]['display_name'] = 'Static-only name';
        $snapshot['skills'][] = ['title' => 'Web', 'description' => 'JavaScript, HTML, CSS, PHP'];
        unset($snapshot['profiles'][0]['headshot'], $snapshot['profiles'][0]['github_url'], $snapshot['projects'][0]['website_url'], $snapshot['tools']);
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
            ->assertSee('<meta property="og:url" content="https://www.archellenaya.com/">', false)
            ->assertSee('<meta name="twitter:card" content="summary">', false)
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
            ->assertSee('8+ years of professional experience')
            ->assertSee('Download Resume')
            ->assertSee('Learning & Academic Background')
            ->assertSee('Web: JavaScript, HTML, CSS</li>', false)
            ->assertSee('Server-side Languages: PHP</li>', false)
            ->assertSee('Client Website Gallery')
            ->assertSee('Freelance Services')
            ->assertDontSee('Featured Technical Case Studies')
            ->assertSee('Available for remote employment')
            ->assertSee('Available for freelance projects')
            ->assertDontSee('>GitHub</a>', false)
            ->assertDontSee('spoof.test');
        $this->assertSame(6, substr_count($response->getContent(), 'aria-label="Discuss '));
        foreach (['intro', 'about', 'works', 'services', 'contact'] as $anchor) {
            $response->assertSee('id="'.$anchor.'"', false)->assertSee('href="#'.$anchor.'"', false);
        }
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $response->getContent(), $structuredData);
        $profile = json_decode($structuredData[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('https://www.archellenaya.com/', $profile['url']);
        $this->assertSame($snapshot['profiles'][0]['fullname'], $profile['mainEntity']['name']);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: https://www.archellenaya.com/sitemap.xml');
        $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>https://www.archellenaya.com/</loc>', false);
        $this->get('/download-cv')->assertDownload('archell-enaya-resume.pdf');
    }

    public function test_case_studies_require_approval_and_publish_only_configured_details(): void
    {
        $contract = PortfolioContract::definition();
        $records = [
            ['title' => 'Private draft', 'approved' => false],
            [
                'approved' => true,
                'title' => 'Approved example',
                'description' => 'Example description',
                'problem' => 'Example problem',
                'repository_url' => 'https://example.test/repository',
                'live_url' => 'javascript:alert(1)',
            ],
        ];

        $study = array_values(PortfolioPresentation::caseStudies($records, $contract));
        $this->assertCount(1, $study);
        $this->assertSame('Approved example', $study[0]['title']);
        $this->assertSame([['label' => 'Business problem', 'text' => 'Example problem']], $study[0]['details']);
        $this->assertSame([['label' => 'Public repository', 'url' => 'https://example.test/repository']], $study[0]['links']);
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
