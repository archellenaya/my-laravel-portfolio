<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            "display_name" => "Arch",
            "fullname" => "Archell Enaya",
            "email" => "archell.asilo.enaya@gmail.com",
            "whatsapp" => "639383998076",
            "contact_number" => "+639913013468",
            "linkedin_url" => "https://www.linkedin.com/in/archell-enaya-b52093207",
            "instagram_url" => "https://www.instagram.com/instag_arch?igsh=MWM2dDUxMDcxYnl4",
            "github_url" => "https://github.com/archellenaya", 
            "facebook_url" => "https://www.facebook.com/share/1JHXi7wBwD/"
        ];

        return view('welcome', compact('data'));
    }
}