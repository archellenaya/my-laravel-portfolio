<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['password' => Hash::make('a-long-test-password')]);
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    public function test_admin_access_login_and_logout(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->app['auth']->forgetGuards();
        Notification::fake();
        $user = $this->admin();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'a-long-test-password'])->assertRedirect('/admin/verify-code');
        $this->assertGuest();
        $code = Notification::sent($user, \App\Notifications\LoginCode::class)->first()->code;
        $this->post('/admin/verify-code', ['code' => $code])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk();
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_non_admin_cannot_login_as_administrator(): void
    {
        $user = User::factory()->create(['password' => Hash::make('a-long-test-password')]);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'a-long-test-password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_content_changes_are_visible_and_crud_is_validated(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->seed(PortfolioSeeder::class);
        $admin = $this->admin();
        $this->actingAs($admin)->withSession(['email_code_user' => $admin->id, 'email_code_password' => $admin->password]);
        foreach (array_keys(config('cms')) as $section) {
            $this->get('/admin/content/'.$section)->assertOk();
            $row = DB::table($section)->first();
            $this->get('/admin/content/'.$section.'/'.$row->id.'/edit')->assertOk();
        }
        $profile = (array) DB::table('profiles')->first();
        $profile['display_name'] = 'Updated name';
        $this->put('/admin/content/profiles/'.$profile['id'], $profile)->assertRedirect('/admin/content/profiles');
        $this->get('/')->assertOk()->assertSee('Updated name');
        $profile['github_url'] = 'javascript:alert(1)';
        $this->put('/admin/content/profiles/'.$profile['id'], $profile)->assertSessionHasErrors('github_url');
        $this->post('/admin/content/projects', [])->assertSessionHasErrors('title');
        $project = ['title' => '<script>alert(1)</script>', 'category' => 'Integration', 'description' => 'Example project', 'tags' => 'Laravel, PHP', 'image_upload' => \Illuminate\Http\UploadedFile::fake()->image('project.jpg'), 'sort_order' => 4];
        $this->post('/admin/content/projects', $project)->assertRedirect('/admin/content/projects');
        $id = DB::table('projects')->max('id');
        $this->get('/')->assertSee($project['title'])->assertDontSee($project['title'], false);
        $this->delete('/admin/content/projects/'.$id)->assertRedirect('/admin/content/projects');
        $this->assertDatabaseMissing('projects', ['id' => $id]);
        $this->delete('/admin/content/profiles/'.$profile['id'])->assertForbidden();
        $this->get('/admin/content/users')->assertNotFound();
        $this->get('/download-cv')->assertDownload('archell-enaya-resume.pdf');
    }

    public function test_password_reset_flow_rejects_invalid_and_reused_tokens(): void
    {
        Notification::fake();
        $user = $this->admin();
        $this->get('/admin/forgot-password')->assertOk();
        $this->post('/admin/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });
        $this->get('/admin/reset-password/'.$token.'?email='.urlencode($user->email))->assertOk();
        $payload = ['email' => $user->email, 'token' => 'invalid', 'password' => 'a-new-long-password', 'password_confirmation' => 'a-new-long-password'];
        $this->post('/admin/reset-password', $payload)->assertSessionHasErrors('email');
        $payload['token'] = $token;
        $this->post('/admin/reset-password', $payload)->assertRedirect('/admin/login');
        $this->assertTrue(Hash::check('a-new-long-password', $user->fresh()->password));
        $this->post('/admin/reset-password', $payload)->assertSessionHasErrors('email');
    }

    public function test_login_requests_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'unknown@example.test', 'password' => 'wrong']);
        }
        $this->post('/admin/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertStatus(429);
    }
}
