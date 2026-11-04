<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AdminAuthController extends Controller
{
    public function loginForm()
    {
        return view('admin.auth', ['mode' => 'login']);
    }

    public function forgotForm()
    {
        return view('admin.auth', ['mode' => 'forgot']);
    }

    public function resetForm(Request $request, string $token)
    {
        $request->validate(['email' => 'nullable|string|email|max:254']);

        return view('admin.auth', ['mode' => 'reset', 'token' => $token, 'email' => $request->query('email')]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = User::where('email', $credentials['email'])->where('is_admin', true)->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        if (! LoginCodeController::send($request, $user)) {
            return back()->withErrors(['email' => 'Unable to send a verification code. Please wait and try again.'])->onlyInput('email');
        }

        return redirect()->route('login.code');
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        Password::sendResetLink(['email' => $request->input('email'), 'is_admin' => true]);

        return back()->with('status', 'If this email belongs to an administrator, a password reset link has been sent.');
    }

    public function reset(Request $request)
    {
        $request->validate(['token' => 'required', 'email' => 'required|email', 'password' => ['required', 'confirmed', PasswordRule::min(12)]]);
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token') + ['is_admin' => true], function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
