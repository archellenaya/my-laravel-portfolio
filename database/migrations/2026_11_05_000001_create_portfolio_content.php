<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'profiles' => ['display_name' => 'text', 'fullname' => 'text', 'email' => 'text', 'whatsapp' => 'text', 'contact_number' => 'text', 'linkedin_url' => 'text', 'instagram_url' => 'text', 'github_url' => 'text', 'facebook_url' => 'text', 'role' => 'text', 'location' => 'text', 'contact_location' => 'text', 'greeting' => 'text', 'page_title' => 'text', 'meta_description' => 'textarea', 'photo' => 'text', 'photo_retina' => 'text', 'resume' => 'text', 'works_intro' => 'textarea', 'confidentiality' => 'textarea', 'contact_intro' => 'textarea', 'summary' => 'textarea', 'about' => 'textarea'],
        'expertise' => ['title' => 'text'],
        'skills' => ['title' => 'text', 'description' => 'textarea'],
        'experiences' => ['title' => 'text', 'organization' => 'text', 'period' => 'text', 'description' => 'textarea'],
        'education' => ['title' => 'text', 'organization' => 'text', 'period' => 'text', 'description' => 'textarea'],
        'projects' => ['title' => 'text', 'category' => 'text', 'description' => 'textarea', 'tags' => 'text', 'image' => 'text'],
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });
        foreach (self::TABLES as $name => $fields) {
            Schema::create($name, function (Blueprint $table) use ($fields) {
                $table->id();
                foreach ($fields as $field => $type) {
                    $table->text($field)->nullable();
                }
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(array_keys(self::TABLES)) as $name) {
            Schema::dropIfExists($name);
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
