@extends('admin.layout')
@section('content')
<div class="panel auth">
<h1>{{ ['login' => 'Welcome back', 'forgot' => 'Forgot password?', 'reset' => 'Set a new password'][$mode] }}</h1>
<p class="muted">{{ $mode === 'login' ? 'Sign in to manage your portfolio.' : ($mode === 'forgot' ? 'Enter your administrator email to request a reset link.' : 'Choose a password with at least 12 characters.') }}</p>
<form method="post" action="{{$mode === 'login' ? route('login') : ($mode === 'forgot' ? route('password.email') : route('password.update'))}}">
@csrf
@if($mode === 'reset')<input type="hidden" name="token" value="{{$token}}">@endif
<label for="email">Email address</label><input id="email" type="email" name="email" value="{{old('email', $email ?? '')}}" autocomplete="username" required autofocus>
@if($mode !== 'forgot')
<label for="password">Password</label><input id="password" type="password" name="password" autocomplete="{{$mode === 'reset' ? 'new-password' : 'current-password'}}" @if($mode === 'reset') minlength="12" @endif required>
@endif
@if($mode === 'reset')
<label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required>
@endif

<div class="actions"><button>{{['login' => 'Sign in', 'forgot' => 'Send reset link', 'reset' => 'Reset password'][$mode]}}</button></div>
</form>
<p><a href="{{$mode === 'login' ? route('password.request') : route('login')}}">{{$mode === 'login' ? 'Forgot password?' : 'Back to sign in'}}</a></p>
</div>
@endsection
