@extends('layouts.app')

@section('content')
    <h1>My Projects</h1>
    <div class="projects">
        @foreach($projects as $project)
            <div class="project">
                <h2>{{ $project->title }}</h2>
                <p>{{ $project->description }}</p>
                @if($project->image)
                    <img src="{{ asset('images/'.$project->image) }}" alt="{{ $project->title }}">
                @endif
                @if($project->url)
                    <a href="{{ $project->url }}" target="_blank">View Project</a>
                @endif
            </div>
        @endforeach
    </div>
@endsection