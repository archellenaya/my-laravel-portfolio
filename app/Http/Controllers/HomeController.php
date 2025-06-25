<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            "display_name"   => "Arch",
            "fullname"       => "Archell Enaya",
            "email"          => "archell.asilo.enaya@gmail.com",
            "whatsapp"       => "639383998076",
            "contact_number" => "+639913013468",
            "linkedin_url"   => "https://www.linkedin.com/in/archell-enaya-b52093207",
            "instagram_url"  => "https://www.instagram.com/instag_arch?igsh=MWM2dDUxMDcxYnl4",
            "github_url"     => "https://github.com/archellenaya", 
            "facebook_url"   => "https://www.facebook.com/archell.enaya.980"
        ];

        $projects = [
            [
                'category' => 'Laravel Multi-Tenancy',
                'title' => 'Estate Agency Platform - Multi-Tenancy',
                'description' => "A Multi-tenant Real Estate Agency serve as Software as a Service (SAAS). Technology used are Wordpress as Frontend and Laravel as Backend.
                            My responsibilities as a Laravel Backend Engineer, are providing REST-API endpoints to Wordpress and getting data from third party source.",
                'tags' => [
                    'Multi-tenancy',
                    'Laravel',
                    'Wordpress',
                    'REST-API'
                ], 
                'project_links' => [
                    [
                        'title' => 'Sample Tenant',
                        'url'=>'https://benestates.com', 
                    ] 
                ],
                'image' => [
                    'tile' => "images/portfolio/laravel.png",
                    'gallery' => "images/portfolio/laravel.png"
                ]
            ],[
                'category' => 'Laravel Multi-Tenancy',
                'title' => 'SIMS Integration - Multi-Tenancy',
                'description' => "A Multi-tenant SIMS Integration using Laravel",
                'tags' => [
                    'Multi-tenancy',
                    'Laravel',
                    'Wordpress',
                    'REST-API'
                ],
                'project_links' => [ 
                     [
                        'title' => 'Sample Tenant',
                        'url'=>'https://bhome.com.mt', 
                    ]  
                ],
                'image' => [
                    'tile' => "images/portfolio/laravel.png",
                    'gallery' => "images/portfolio/laravel.png"
                ]
            ],[
                'category' => 'Drupal CMS',
                'title' => 'Drupal CMS',
                'description' => "Projects with Backend as Drupal CMS providing endpoints to Frontend Dev using React JS.",
                'tags' => [
                    'Drupal', 'React JS'
                ],
                'project_links' => [ 
                    [
                        'title' => 'Sample CMS',
                        'url'=> 'https://apcopay.abcnow.xyz', 
                    ]  
                    
                ],
                'image' => [
                    'tile' => "images/portfolio/drupal.png",
                    'gallery' => "images/portfolio/drupal.png"
                ]
            ]
        ];

        return view('home', compact('data', 'projects'));
    }

    public function download_cv() 
    { 
        $fileName = 'archell-enaya-resume.pdf';
        $path = public_path($fileName);

        if (file_exists($path)) {
            return response()->download($path, $fileName);
        }

        return response('File not found.', 404);
    }
}