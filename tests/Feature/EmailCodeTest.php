<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\LoginCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailCodeTest extends TestCase
{
    use RefreshDatabase;

    private function begin(): array
    {
        Notification::fake();
        $user = User::factory()->create(['password' => Hash::make('a-long-test-password')]);
        $user->forceFill(['is_admin' => true])->save();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'a-long-test-password', 'remember' => 1])->assertRedirect('/admin/verify-code');
        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/verify-code')->assertOk();

        return [$user, Notification::sent($user, LoginCode::class)->last()->code];
    }

    public function test_code_is_hashed_single_use_and_bound_to_session(): void
    {
        [$user, $code] = $this->begin();
        $challenge = DB::table('login_codes')->first();
        $this->assertNotSame($code, $challenge->code_hash);
        $this->assertTrue(Hash::check($code, $challenge->code_hash));
        $this->post('/admin/verify-code', ['code' => $code])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk();
        $this->assertDatabaseCount('login_codes', 0);
        $this->post('/admin/logout');
        $this->post('/admin/verify-code', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_code_expires_and_attempts_are_limited(): void
    {
        [$user, $code] = $this->begin();
        $wrong = $code === '111111' ? '222222' : '111111';
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/verify-code', ['code' => $wrong])->assertSessionHasErrors('code');
        }
        $this->post('/admin/verify-code', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_expired_codes_and_changed_passwords_cannot_authenticate(): void
    {
        [$user, $code] = $this->begin();
        $this->travel(11)->minutes();
        $this->post('/admin/verify-code', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->travelBack();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'a-long-test-password']);
        $code = Notification::sent($user, LoginCode::class)->last()->code;
        $user->update(['password' => Hash::make('a-changed-password')]);
        $this->post('/admin/verify-code', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_resend_has_cooldown_replaces_previous_code_and_has_send_limit(): void
    {
        [$user, $code] = $this->begin();
        $oldId = DB::table('login_codes')->value('id');
        $this->post('/admin/resend-code')->assertSessionHasErrors('code');
        $this->travel(61)->seconds();
        $this->post('/admin/resend-code')->assertSessionHas('status');
        $this->assertDatabaseMissing('login_codes', ['id' => $oldId]);
        $this->assertDatabaseCount('login_codes', 1);
        $newCode = Notification::sent($user, LoginCode::class)->last()->code;
        $this->travel(61)->seconds();
        $this->post('/admin/resend-code')->assertSessionHas('status');
        $this->travel(61)->seconds();
        $this->post('/admin/resend-code')->assertSessionHasErrors('code');
        $this->assertCount(3, Notification::sent($user, LoginCode::class));
    }

    public function test_existing_unverified_sessions_cannot_access_cms(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user)->get('/admin')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_code_from_another_session_is_rejected_and_cancel_clears_challenge(): void
    {
        [$user, $code] = $this->begin();
        $id = session('login_challenge');
        $this->withSession(['login_challenge' => null])->post('/admin/verify-code', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->withSession(['login_challenge' => $id])->post('/admin/cancel-code')->assertRedirect('/admin/login');
        $this->assertDatabaseCount('login_codes', 0);
    }
}
