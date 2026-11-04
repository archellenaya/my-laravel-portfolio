<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $data = (array) DB::table('profiles')->first();
        abort_unless($data, 503, 'Portfolio content has not been initialized.');
        $content = [];
        foreach (['expertise', 'skills', 'experiences', 'education'] as $table) {
            $content[$table] = DB::table($table)->orderBy('sort_order')->orderBy('id')->get();
        }
        $projects = DB::table('projects')->orderBy('sort_order')->orderBy('id')->get()->map(fn ($row) => (array) $row);

        return view('home_page', compact('data', 'projects') + $content);
    }

    public function download_cv()
    {
        $file = DB::table('profiles')->value('resume');
        $uploaded = $file && str_starts_with($file, 'storage/cms/');
        $base = $uploaded ? \Illuminate\Support\Facades\Storage::disk('public')->path('') : public_path();
        $relative = $uploaded ? substr($file, 8) : $file;
        $path = $relative ? realpath($base.'/'.$relative) : false;
        abort_unless($path && str_starts_with($path, realpath($base).DIRECTORY_SEPARATOR) && is_file($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf', 404);

        return response()->download($path);
    }
}
