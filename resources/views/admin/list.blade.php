@extends('admin.layout')
@section('content')
<a href="{{route('admin.index')}}">← Dashboard</a><h1>{{ucfirst($section === 'profiles' ? 'Profile & site content' : $section)}}</h1>
@if($section !== 'profiles')<a class="button" href="{{route('admin.create', $section)}}">Add record</a>@endif
<div class="panel table-wrap"><table><thead><tr><th>Order</th><th>Content</th><th>Actions</th></tr></thead><tbody>
@forelse($rows as $row)<tr><td>{{$row->sort_order}}</td><td>{{$row->title ?? $row->fullname ?? 'Profile'}}</td><td><a href="{{route('admin.edit', [$section, $row->id])}}">Edit</a>
@if($section !== 'profiles')
<details><summary>Delete</summary><p>Delete this record from the site?</p><form method="post" action="{{route('admin.delete', [$section, $row->id])}}">@csrf @method('DELETE')<button class="danger">Confirm delete</button></form></details>
@endif</td></tr>
@empty<tr><td colspan="3">No records yet. Add your first record above.</td></tr>@endforelse
</tbody></table></div>
@endsection
