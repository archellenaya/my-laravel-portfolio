<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>@yield('title', 'Portfolio CMS')</title>
<style>
:root{color-scheme:light;font-family:system-ui,sans-serif;color:#172033;background:#f3f5f9}*{box-sizing:border-box}body{margin:0}header{background:#172033;color:white;padding:20px max(20px,calc((100vw - 1100px)/2));display:flex;gap:20px;align-items:center;flex-wrap:wrap}header a{color:white}header strong{margin-right:auto}main{max-width:1100px;margin:36px auto;padding:0 20px}a{color:#254dad}h1{font-size:30px}h2{font-size:20px}p{line-height:1.6}.panel{background:white;border:1px solid #dce1ea;border-radius:12px;padding:26px;margin:20px 0}.auth{max-width:470px;margin:55px auto}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px}.grid .panel{margin:0}label{display:block;font-weight:600;margin:18px 0 7px}input:not([type=checkbox]),textarea{font:inherit;width:100%;padding:12px;border:1px solid #9ca8ba;border-radius:6px}textarea{min-height:135px;resize:vertical}input:focus,textarea:focus,a:focus,button:focus{outline:3px solid #7daaff;outline-offset:3px}button,.button{display:inline-block;background:#254dad;color:white;border:0;border-radius:6px;padding:11px 17px;font:inherit;cursor:pointer;text-decoration:none}button.danger{background:#a52c36}.actions{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:20px}.actions form{margin:0}.notice{background:#e2f5e8;padding:16px;border-radius:8px}.errors{background:#ffe6e6;color:#7b1822;padding:16px;border-radius:8px}.muted{color:#556174;font-size:14px}table{width:100%;border-collapse:collapse}td,th{text-align:left;padding:15px 8px;border-bottom:1px solid #dce1ea}th:first-child{width:75px}.table-wrap{overflow-x:auto}.inline{display:inline}header button{background:#36435b}small{display:block;margin-top:6px;color:#556174}
</style>
</head>
<body><header><strong>Portfolio CMS</strong><a href="{{url('/')}}" target="_blank" rel="noopener">View site ↗</a>
@auth @can('manage-content')<a href="{{route('admin.index')}}">Dashboard</a><form method="post" action="{{route('logout')}}">@csrf<button>Log out</button></form>@endcan @endauth
</header><main>
@if(session('status'))<div class="notice" role="status">{{session('status')}}</div>@endif
@if($errors->any())<div class="errors" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
@yield('content')
</main></body></html>
