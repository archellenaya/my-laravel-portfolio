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
        foreach (['headshot', 'photo', 'photo_retina'] as $field) {
            $optimized = 'images/optimized/'.pathinfo($data[$field], PATHINFO_FILENAME).'.webp';
            if (is_file(public_path($optimized))) {
                $data[$field] = $optimized;
            }
        }
        $content = [];
        foreach (['expertise', 'skills', 'experiences', 'education', 'tools'] as $table) {
            $content[$table] = collect($snapshot[$table])->map(fn ($row) => (object) $row);
        }
        $content['tools'] = $content['tools']->map(function ($tool) {
            $optimized = 'images/optimized/'.pathinfo($tool->image, PATHINFO_FILENAME).'.webp';
            if (is_file(public_path($optimized))) {
                $tool->image = $optimized;
            }

            return $tool;
        });
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
