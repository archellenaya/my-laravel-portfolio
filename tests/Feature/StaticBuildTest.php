<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BuildStaticPortfolio;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class StaticBuildTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_requires_verified_administrator(): void
    {
        Process::fake();
        $this->post('/admin/build-static')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->post('/admin/build-static')->assertForbidden();
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user)->post('/admin/build-static')->assertRedirect('/admin/login');
        Process::assertNothingRan();
    }

    public function test_button_exports_current_data_and_builds_site_dist(): void
    {
        $this->seed(PortfolioSeeder::class);
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user)->withSession(['email_code_user' => $user->id, 'email_code_password' => $user->password]);
        $file = tempnam(sys_get_temp_dir(), 'cms-build-');
        config(['portfolio.snapshot' => $file]);
        Process::fake();
        try {
            $this->get('/admin')->assertOk()->assertSee('Publish Static');
            $this->post('/admin/build-static')->assertRedirect('/admin')->assertSessionHas('status');
            $snapshot = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame('Archell Enaya', $snapshot['profiles'][0]['fullname']);
            Process::assertRan(fn ($process) => $process->command === ['node', base_path('scripts/build-static.mjs'), '--stage-assets']);
            Process::assertRanTimes(fn ($process) => $process->command === ['node', base_path('scripts/build-static.mjs'), '--stage-assets'], 1);
        } finally {
            unlink($file);
        }
    }

    public function test_failed_process_is_reported_without_success_message(): void
    {
        $this->seed(PortfolioSeeder::class);
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user)->withSession(['email_code_user' => $user->id, 'email_code_password' => $user->password]);
        $file = tempnam(sys_get_temp_dir(), 'cms-build-');
        config(['portfolio.snapshot' => $file]);
        Process::fake(['*' => Process::result(errorOutput: 'Missing asset', exitCode: 1)]);
        try {
            $this->post('/admin/build-static')->assertRedirect('/admin')->assertSessionHasErrors('build')->assertSessionMissing('status');
        } finally {
            unlink($file);
        }
    }

    public function test_overlapping_builds_are_blocked(): void
    {
        Process::fake();
        $lock = Cache::lock('portfolio-static-build', 180);
        $lock->get();
        try {
            $this->expectException(\RuntimeException::class);
            app(BuildStaticPortfolio::class)->build();
        } finally {
            $lock->release();
            Process::assertNothingRan();
        }
    }
}
