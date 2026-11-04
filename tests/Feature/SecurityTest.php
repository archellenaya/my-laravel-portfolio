<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_malformed_and_sql_injection_credentials_do_not_authenticate(): void
    {
        $this->post('/admin/login', ['email' => ['admin@example.test'], 'password' => ['bad']])->assertSessionHasErrors(['email', 'password']);
        $this->post('/admin/login', ['email' => "' OR 1=1 --", 'password' => 'anything'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_cannot_edit_delete_or_upload_content(): void
    {
        Storage::fake('public');
        $this->seed(PortfolioSeeder::class);
        $before = DB::table('projects')->count();
        $this->post('/admin/content/projects', ['title' => 'Unauthorized', 'image_upload' => UploadedFile::fake()->image('attack.png')])->assertRedirect('/admin/login');
        $this->put('/admin/content/profiles/1', ['fullname' => 'Unauthorized'])->assertRedirect('/admin/login');
        $this->delete('/admin/content/projects/1')->assertRedirect('/admin/login');
        $this->assertDatabaseCount('projects', $before);
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_reset_response_does_not_reveal_whether_account_exists(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $known = $this->post('/admin/forgot-password', ['email' => $user->email]);
        $known->assertSessionHas('status');
        $message = session('status');
        $this->post('/admin/forgot-password', ['email' => 'unknown@example.test'])->assertSessionHas('status', $message);
    }

    public function test_resume_download_rejects_path_traversal(): void
    {
        $this->seed(PortfolioSeeder::class);
        foreach (['../.env', '../../docker-compose.yaml', '/etc/passwd', 'storage/cms/../../../../.env'] as $path) {
            DB::table('profiles')->update(['resume' => $path]);
            $this->get('/download-cv')->assertNotFound();
        }
    }

    public function test_unverified_admin_cannot_mutate_content(): void
    {
        $this->seed(PortfolioSeeder::class);
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $before = DB::table('projects')->count();
        $this->actingAs($user)->delete('/admin/content/projects/1')->assertRedirect('/admin/login');
        $this->assertDatabaseCount('projects', $before);
        $this->assertGuest();
    }

    public function test_malformed_reset_email_is_rejected_without_debug_leak(): void
    {
        $response = $this->getJson('/admin/reset-password/security-probe?email%5B%5D=test');
        $response->assertUnprocessable()->assertJsonValidationErrors('email');
        $response->assertDontSee('/var/www/html', false);
        $response->assertDontSee('TypeError', false);
    }
}
