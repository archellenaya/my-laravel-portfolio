<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CaseStudyCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewed_cases_and_gallery_details_follow_approval_in_live_and_static_content(): void
    {
        $this->seed(PortfolioSeeder::class);
        config(['portfolio.static' => false]);
        $snapshot = tempnam(sys_get_temp_dir(), 'portfolio-case-review-');
        config(['portfolio.snapshot' => $snapshot]);

        try {
            $list = route('admin.list', 'case_studies');
            $this->get($list)->assertRedirect('/admin/login');
            $admin = User::factory()->create(['is_admin' => true]);
            $this->actingAs($admin)->withSession(['email_code_user' => $admin->id, 'email_code_password' => $admin->password]);
            $this->get($list)->assertOk()->assertSee('Property Data Synchronization')->assertSee('Draft');
            $caseId = DB::table('case_studies')->where('title', 'Property Data Synchronization')->value('id');
            $edit = route('admin.edit', ['case_studies', $caseId]);
            $update = route('admin.update', ['case_studies', $caseId]);
            $this->get($edit)->assertOk()->assertSee('Screenshot Path')->assertSee('Approved');

            $case = ['title' => 'Reviewed project', 'description' => 'Owner-reviewed description.', 'technologies' => 'PHP, Laravel', 'sort_order' => 0];
            $project = (array) DB::table('projects')->first();
            $galleryUpdate = route('admin.update', ['projects', $project['id']]);
            $project['contribution'] = 'Reviewed gallery contribution.';
            $project['technologies'] = 'Laravel';

            $this->put($update, $case + ['screenshot_path' => '../.env', 'screenshot_alt' => 'Invalid'])->assertSessionHasErrors('screenshot_path');
            $this->put($update, $case)->assertRedirect($list);
            $this->put($galleryUpdate, $project)->assertRedirect();
            $this->get('/')->assertOk()->assertDontSee($case['title'])->assertDontSee($project['contribution']);

            $this->artisan('portfolio:export')->assertSuccessful();
            $this->assertStringNotContainsString($case['description'], file_get_contents($snapshot));
            $this->assertStringNotContainsString($project['contribution'], file_get_contents($snapshot));

            $this->put($update, $case + ['approved' => '1'])->assertRedirect($list);
            $project['approved'] = '1';
            $this->put($galleryUpdate, $project)->assertRedirect();
            $this->get('/')->assertOk()->assertSee($case['title'])->assertSee($project['contribution']);
            $this->artisan('portfolio:export')->assertSuccessful();
            config(['portfolio.static' => true]);
            $this->get('/')->assertOk()->assertSee($case['title'])->assertSee($project['contribution']);

            config(['portfolio.static' => false]);
            $this->put($update, $case)->assertRedirect($list);
            $project['approved'] = '0';
            $this->put($galleryUpdate, $project)->assertRedirect();
            $this->get('/')->assertOk()->assertDontSee($case['title'])->assertDontSee($project['contribution']);
            $this->artisan('portfolio:export')->assertSuccessful();
            config(['portfolio.static' => true]);
            $this->get('/')->assertOk()->assertDontSee($case['title'])->assertDontSee($project['contribution']);
        } finally {
            unlink($snapshot);
        }
    }
}
