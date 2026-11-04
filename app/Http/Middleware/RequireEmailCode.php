<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireEmailCode
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($request->session()->get('email_code_user') !== $user->id ||
            $request->session()->get('email_code_password') !== $user->password) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Please sign in and verify your email code.');
        }

        return $next($request);
    }
}
