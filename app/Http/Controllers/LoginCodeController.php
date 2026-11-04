<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\LoginCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginCodeController extends Controller
{
    public static function send(Request $request, User $user): bool
    {
        $key = 'email-code-send:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return false;
        }
        RateLimiter::hit($key, 600);
        $code = (string) random_int(100000, 999999);
        $id = (string) Str::uuid();
        DB::table('login_codes')->where('expires_at', '<', now())->delete();
        DB::table('login_codes')->insert([
            'id' => $id, 'user_id' => $user->id, 'code_hash' => Hash::make($code),
            'password_hash' => $user->password, 'expires_at' => now()->addMinutes(10), 'sent_at' => now(),
        ]);
        try {
            $user->notify(new LoginCode($code));
        } catch (\Throwable $e) {
            DB::table('login_codes')->where('id', $id)->delete();
            report($e);

            return false;
        }
        if ($previous = $request->session()->get('login_challenge')) {
            DB::table('login_codes')->where('id', $previous)->delete();
        }
        $request->session()->put('login_challenge', $id);

        return true;
    }

    private function challenge(Request $request)
    {
        return DB::table('login_codes')->where('id', $request->session()->get('login_challenge', ''))->first();
    }

    public function show(Request $request)
    {
        $challenge = $this->challenge($request);
        if (! $challenge || now()->gte($challenge->expires_at)) {
            return redirect()->route('login')->with('status', 'Your sign-in code expired. Please sign in again.');
        }

        return view('admin.code');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        $user = DB::transaction(function () use ($request) {
            $challenge = DB::table('login_codes')->where('id', $request->session()->get('login_challenge', ''))->lockForUpdate()->first();
            if (! $challenge) {
                return null;
            }
            $user = User::find($challenge->user_id);
            if (! $user || ! $user->is_admin || $challenge->password_hash !== $user->password || now()->gte($challenge->expires_at) || $challenge->attempts >= 5) {
                DB::table('login_codes')->where('id', $challenge->id)->delete();

                return null;
            }
            if (! Hash::check($request->input('code'), $challenge->code_hash)) {
                DB::table('login_codes')->where('id', $challenge->id)->update(['attempts' => $challenge->attempts + 1]);

                return null;
            }
            DB::table('login_codes')->where('id', $challenge->id)->delete();

            return $user;
        });
        if (! $user) {
            return back()->withErrors(['code' => 'The code is invalid, expired, or has reached its attempt limit.']);
        }
        Auth::login($user);
        $request->session()->forget('login_challenge');
        $request->session()->regenerate();
        $request->session()->put(['email_code_user' => $user->id, 'email_code_password' => $user->password]);

        return redirect()->intended(route('admin.index'));
    }

    public function resend(Request $request)
    {
        $challenge = $this->challenge($request);
        $user = $challenge ? User::find($challenge->user_id) : null;
        if (! $challenge || ! $user || ! $user->is_admin || $challenge->password_hash !== $user->password || now()->gte($challenge->expires_at)) {
            return redirect()->route('login')->with('status', 'Please sign in again to request a code.');
        }
        if (now()->lt(\Illuminate\Support\Carbon::parse($challenge->sent_at)->addSeconds(60))) {
            return back()->withErrors(['code' => 'Wait 60 seconds before requesting another code.']);
        }
        if (! self::send($request, $user)) {
            return back()->withErrors(['code' => 'Unable to send a code. Please wait and try again.']);
        }

        return back()->with('status', 'A new code has been sent. The previous code no longer works.');
    }

    public function cancel(Request $request)
    {
        DB::table('login_codes')->where('id', $request->session()->get('login_challenge', ''))->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
