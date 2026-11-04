<?php

namespace App\Http\Controllers;

use App\Services\PortfolioContent;

class HomeController extends Controller
{
    public function index(PortfolioContent $portfolio)
    {
        $snapshot = $portfolio->load();
        $data = $snapshot['profiles'][0] ?? [];
        abort_unless($data, 503, 'Portfolio content has not been initialized.');
        $content = [];
        foreach (['expertise', 'skills', 'experiences', 'education'] as $table) {
            $content[$table] = collect($snapshot[$table])->map(fn ($row) => (object) $row);
        }
        $projects = collect($snapshot['projects']);

        return view('home_page', compact('data', 'projects') + $content);
    }

    public function download_cv(PortfolioContent $portfolio)
    {
        $file = $portfolio->load()['profiles'][0]['resume'] ?? null;
        $uploaded = $file && str_starts_with($file, 'storage/cms/');
        $base = $uploaded ? \Illuminate\Support\Facades\Storage::disk('public')->path('') : public_path();
        $relative = $uploaded ? substr($file, 8) : $file;
        $path = $relative ? realpath($base.'/'.$relative) : false;
        abort_unless($path && str_starts_with($path, realpath($base).DIRECTORY_SEPARATOR) && is_file($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf', 404);

        return response()->download($path);
    }
}
