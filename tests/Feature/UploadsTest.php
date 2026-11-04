<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(PortfolioSeeder::class);
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user)->withSession(['email_code_user' => $user->id, 'email_code_password' => $user->password]);
    }

    public function test_profile_uploads_are_saved_rendered_and_downloadable(): void
    {
        $profile = (array) DB::table('profiles')->first();
        $payload = $profile + [
            'photo_upload' => UploadedFile::fake()->image('portrait.jpg'),
            'photo_retina_upload' => UploadedFile::fake()->image('portrait-large.png'),
            'resume_upload' => new UploadedFile(public_path('archell-enaya-resume.pdf'), 'resume.pdf', 'application/pdf', null, true),
        ];
        $this->put('/admin/content/profiles/'.$profile['id'], $payload)->assertRedirect('/admin/content/profiles');
        $saved = DB::table('profiles')->first();
        foreach (['photo', 'photo_retina', 'resume'] as $field) {
            $this->assertStringStartsWith('storage/cms/', $saved->$field);
            Storage::disk('public')->assertExists(substr($saved->$field, 8));
        }
        $this->get('/')->assertOk()->assertSee(asset($saved->photo));
        $this->get('/download-cv')->assertOk()->assertDownload(basename($saved->resume));
    }

    public function test_blank_uploads_preserve_existing_files(): void
    {
        $profile = (array) DB::table('profiles')->first();
        $profile['fullname'] = 'Updated profile';
        $this->put('/admin/content/profiles/'.$profile['id'], $profile)->assertRedirect('/admin/content/profiles');
        $this->assertSame($profile['photo'], DB::table('profiles')->value('photo'));
        $this->assertSame($profile['resume'], DB::table('profiles')->value('resume'));
        $this->get('/admin/content/profiles/'.$profile['id'].'/edit')->assertOk()->assertSee('multipart/form-data')->assertSee('type="file"', false);
    }

    public function test_invalid_and_oversized_uploads_are_rejected(): void
    {
        $profile = (array) DB::table('profiles')->first();
        $this->put('/admin/content/profiles/'.$profile['id'], $profile + [
            'photo_upload' => UploadedFile::fake()->create('script.php', 1, 'application/x-httpd-php'),
            'resume_upload' => UploadedFile::fake()->image('not-a-pdf.jpg'),
        ])->assertSessionHasErrors(['photo_upload', 'resume_upload']);
        $this->put('/admin/content/profiles/'.$profile['id'], $profile + [
            'photo_upload' => UploadedFile::fake()->image('large.jpg')->size(8193),
            'resume_upload' => UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf'),
        ])->assertSessionHasErrors(['photo_upload', 'resume_upload']);
        $this->assertSame($profile['resume'], DB::table('profiles')->value('resume'));
    }

    public function test_project_requires_image_on_creation_and_disallows_unvalidated_uploads(): void
    {
        $project = ['title' => 'New project', 'category' => 'Backend', 'description' => 'Description', 'tags' => 'PHP', 'sort_order' => 0];
        $this->post('/admin/content/projects', $project)->assertSessionHasErrors('image_upload');
        $this->post('/admin/content/projects', $project + [
            'image_upload' => UploadedFile::fake()->image('project.png'),
            'title_upload' => UploadedFile::fake()->create('script.php', 1, 'application/x-httpd-php'),
        ])->assertRedirect('/admin/content/projects');
        $this->assertDatabaseHas('projects', ['title' => 'New project']);
        $this->assertCount(1, Storage::disk('public')->allFiles('cms'));
    }
}
