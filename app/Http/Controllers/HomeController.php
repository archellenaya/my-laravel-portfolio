<?php

namespace App\Http\Controllers;

use App\Services\PortfolioContent;
use App\Services\PortfolioContract;
use App\Services\PortfolioPresentation;

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
        foreach (['experiences', 'education', 'tools'] as $table) {
            $content[$table] = collect($snapshot[$table])->map(fn ($row) => (object) $row);
        }
        $content['tools'] = $content['tools']->map(function ($tool) {
            $optimized = 'images/optimized/'.pathinfo($tool->image, PATHINFO_FILENAME).'.webp';
            if (is_file(public_path($optimized))) {
                $tool->image = $optimized;
            }

            return $tool;
        });
        foreach (['linkedin_url', 'github_url', 'facebook_url', 'instagram_url'] as $field) {
            $data[$field] = PortfolioContract::projectWebsite($data[$field] ?? null);
        }

        $data['whatsapp'] = preg_match('/^\d{7,15}$/D', $data['whatsapp'] ?? '') ? $data['whatsapp'] : '';

        $contract = PortfolioContract::definition();
        $projects = collect($snapshot['projects'])->map(function (array $project, int $index) {
            $project['modal_id'] = 'modal-0'.($index + 1);
            $project['image_url'] = asset($project['image']);
            $project['website_link'] = PortfolioContract::projectWebsite($project['website_url'] ?? null);
            $project['tags_list'] = array_filter(array_map('trim', explode(',', $project['tags'] ?? '')));

            $project['contribution'] ??= '';
            $project['technologies'] = PortfolioPresentation::technologies($project['technologies'] ?? '');

            return $project;
        });
        $anchors = $contract['anchors'];
        $resumePath = $contract['resume_path'];

        return view('home_page', compact('data', 'projects', 'anchors', 'resumePath') + $content + [
            'templatePreferences' => PortfolioContract::templatePreferences($contract),
            'hero' => $contract['hero'],
            'availability' => $contract['availability'],
            'experienceIntro' => $contract['experience_intro'],
            'skillGroups' => PortfolioPresentation::skills($snapshot, $contract),
            'services' => $contract['optional_sections']['services'],
            'featuredProjects' => PortfolioPresentation::caseStudies($snapshot['case_studies'], $contract),
            'canonicalUrl' => rtrim($contract['public_origin'], '/').'/',
        ]);
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
