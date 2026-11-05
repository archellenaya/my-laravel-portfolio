@extends('admin.layout')
@section('content')
<a href="{{route('admin.list', $section)}}">← Back to {{ $section }}</a>
<h1>{{$row ? 'Edit' : 'Add'}} {{ $section === 'profiles' ? 'profile & site content' : rtrim($section, 's') }}</h1>
<form class="panel" method="post" enctype="multipart/form-data" action="{{$row ? route('admin.update', [$section, $row->id]) : route('admin.store', $section)}}">
@csrf @if($row) @method('PUT') @endif
@foreach($fields as $field => $type)
<label for="{{$field}}">{{$field === 'headshot' ? 'Intro headshot' : ucwords(str_replace('_', ' ', $field))}}</label>
@if(in_array($field, ['headshot', 'photo', 'photo_retina', 'image', 'resume']))
@if(!empty($row->$field))
@if($field === 'resume')<p><a href="{{url('/download-cv')}}" target="_blank" rel="noopener">Download current resume</a></p>
@else<img src="{{asset($row->$field)}}" alt="Current {{str_replace('_', ' ', $field)}}" style="max-width:220px;max-height:180px;object-fit:contain;display:block;margin-bottom:12px">@endif
@endif
<input id="{{$field}}" type="file" name="{{$field}}_upload" accept="{{$field === 'resume' ? 'application/pdf' : 'image/jpeg,image/png,image/webp,image/gif'}}" @if(empty($row->$field)) required @endif>
<small>{{$field === 'resume' ? 'PDF, up to 10 MB.' : 'JPG, PNG, WebP or GIF, up to 8 MB.'}} Leave empty to keep the current file.</small>
@elseif($type === 'textarea')<textarea id="{{$field}}" name="{{$field}}" required maxlength="20000">{{old($field, $row->$field ?? '')}}</textarea>
@else<input id="{{$field}}" name="{{$field}}" type="{{$field === 'email' ? 'email' : (str_ends_with($field, '_url') ? 'url' : 'text')}}" value="{{old($field, $row->$field ?? '')}}" @if($field !== 'website_url') required @endif maxlength="1000">@endif

@if($field === 'tags')<small>Separate tags with commas.</small>@endif
@if($field === 'description' && in_array($section, ['experiences', 'education']))<small>Use one line per bullet point.</small>@endif
@endforeach
<label for="sort_order">Display order</label><input type="number" id="sort_order" name="sort_order" min="0" max="100000" value="{{old('sort_order', $row->sort_order ?? 0)}}" required>
<div class="actions"><button>Save changes</button><a href="{{route('admin.list', $section)}}">Cancel</a></div>
</form>
@endsection
