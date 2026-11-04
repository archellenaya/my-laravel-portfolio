@extends('admin.layout')
@section('content')
<div class="panel auth">
<h1>Check your email</h1>
<p>Enter the 6-digit code sent to your administrator email. It expires in 10 minutes.</p>
<form method="post" action="{{route('login.code.verify')}}">@csrf
<label for="code">Verification code</label>
<input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
<div class="actions"><button>Verify and sign in</button></div>
</form>
<div class="actions">
<form method="post" action="{{route('login.code.resend')}}">@csrf<button>Resend code</button></form>
<form method="post" action="{{route('login.code.cancel')}}">@csrf<button>Back to sign in</button></form>
</div><p class="muted">You can request another code after 60 seconds.</p>
</div>
@endsection
