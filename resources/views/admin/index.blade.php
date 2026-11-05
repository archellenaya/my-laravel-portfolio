@extends('admin.layout')
@section('content')
<h1>Manage your portfolio</h1><p>Edit the content displayed on your public site. Saved changes update the database. Use Publish Static to refresh the static site.</p>
<div class="panel">
    <h2>Update static site</h2>
    <p>Export your latest CMS content, prepare uploaded photos and your resume, and rebuild the static portfolio.</p>
    <form method="post" action="{{route('admin.build-static')}}">
        @csrf
        <button type="submit">Publish Static</button>
    </form>
    <p class="muted">Updates site/dist. To publish, commit the updated JSON and staged assets, then redeploy Vercel.</p>
</div>
<div class="grid">@foreach($counts as $section => $count)
<div class="panel"><h2>{{ucfirst($section === 'profiles' ? 'Profile & site content' : ($section === 'tools' ? 'Tool logos' : $section))}}</h2><p class="muted">{{$count}} {{Str::plural('record', $count)}}</p><a class="button" href="{{route('admin.list', $section)}}">Manage {{ $section === 'profiles' ? 'profile' : ($section === 'tools' ? 'tool logos' : $section) }}</a></div>
@endforeach</div>
@endsection
