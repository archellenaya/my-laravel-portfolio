<?php

namespace App\Http\Controllers;

use App\Services\BuildStaticPortfolio;

class StaticBuildController extends Controller
{
    public function __invoke(BuildStaticPortfolio $builder)
    {
        try {
            $builder->build();
        } catch (\Throwable $error) {
            report($error);

            return redirect()->route('admin.index')->withErrors([
                'build' => 'The static build could not complete. Check the application log and try again. Your CMS content is still saved.',
            ]);
        }

        return redirect()->route('admin.index')->with('status',
            'Static site rebuilt. JSON, uploaded assets, and both dist folders are updated. Commit the updated JSON and staged assets, then redeploy Vercel.');
    }
}
