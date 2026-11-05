<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ToolLogosTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_reorder_replace_and_delete_a_logo(): void
    {
        Storage::fake('public');
        $this->seed(PortfolioSeeder::class);
        config(['portfolio.static' => false]);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin)->withSession(['email_code_user' => $admin->id, 'email_code_password' => $admin->password]);
        $this->get('/admin/content/tools')->assertOk()->assertSee('Claude Code');
        $this->post('/admin/content/tools', [
            'title' => 'New Tool', 'appearance' => 'original', 'sort_order' => 99,
            'image_upload' => UploadedFile::fake()->image('logo.png', 64, 64),
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/content/tools');
        $tool = DB::table('tools')->where('title', 'New Tool')->first();
        $this->assertNotNull($tool);
        Storage::disk('public')->assertExists(substr($tool->image, 8));
        $this->get('/')->assertOk()->assertSee('New Tool');
        $this->put('/admin/content/tools/'.$tool->id, [
            'title' => 'Updated Tool', 'appearance' => 'light', 'sort_order' => 0,
            'image_upload' => UploadedFile::fake()->image('replacement.png', 64, 64),
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/content/tools');
        $updated = DB::table('tools')->find($tool->id);
        $this->assertNotSame($tool->image, $updated->image);
        $this->assertSame('light', $updated->appearance);
        $this->assertSame(0, $updated->sort_order);
        $this->get('/')->assertOk()->assertSee('Updated Tool');
        $this->delete('/admin/content/tools/'.$tool->id)->assertRedirect('/admin/content/tools');
        $this->get('/')->assertOk()->assertDontSee('Updated Tool');
    }

    public function test_logo_upload_and_appearance_are_validated(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin)->withSession(['email_code_user' => $admin->id, 'email_code_password' => $admin->password]);
        $this->post('/admin/content/tools', [
            'title' => 'Invalid', 'appearance' => 'invalid', 'sort_order' => 0,
            'image_upload' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ])->assertSessionHasErrors(['appearance', 'image_upload']);
        $this->assertFalse(DB::table('tools')->where('title', 'Invalid')->exists());
    }

    public function test_empty_tool_list_leaves_no_logo_images_for_the_globe(): void
    {
        $this->seed(PortfolioSeeder::class);
        config(['portfolio.static' => false]);
        DB::table('tools')->delete();
        $response = $this->get('/')->assertOk()->assertDontSee('data-tool-marquee', false);
        $this->assertMatchesRegularExpression('/<template id="globe-logos">\s*<\/template>/', $response->getContent());
    }
}
