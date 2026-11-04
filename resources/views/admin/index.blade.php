@extends('admin.layout')
@section('content')
<h1>Manage your portfolio</h1><p>Edit the content displayed on your public site. Saved changes appear immediately.</p>
<div class="grid">@foreach($counts as $section => $count)
<div class="panel"><h2>{{ucfirst($section === 'profiles' ? 'Profile & site content' : $section)}}</h2><p class="muted">{{$count}} {{Str::plural('record', $count)}}</p><a class="button" href="{{route('admin.list', $section)}}">Manage {{ $section === 'profiles' ? 'profile' : $section }}</a></div>
@endforeach</div>
@endsection
