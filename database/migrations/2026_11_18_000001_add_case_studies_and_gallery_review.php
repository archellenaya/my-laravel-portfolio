<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            foreach (['description', 'problem', 'responsibilities', 'technologies', 'architecture', 'challenges', 'solutions', 'outcomes', 'confidentiality', 'live_url', 'repository_url', 'screenshot_path', 'screenshot_alt'] as $field) {
                $table->text($field)->nullable();
            }
            $table->boolean('approved')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->text('contribution')->nullable();
            $table->text('technologies')->nullable();
            $table->boolean('approved')->default(false);
        });

        $drafts = [
            ['title' => 'Property Data Synchronization', 'description' => 'Work on synchronizing property information.'],
            ['title' => 'Commerce Integrations', 'description' => 'REST API integrations between Laravel, WordPress and WooCommerce, including order, pricing and inventory synchronization.'],
            ['title' => 'Headless Drupal', 'description' => 'Work on custom Drupal modules, structured content and JSON:API for headless CMS implementations.'],
        ];
        foreach ($drafts as $order => $draft) {
            DB::table('case_studies')->insert($draft + ['sort_order' => $order, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn(['contribution', 'technologies', 'approved']));
        Schema::dropIfExists('case_studies');
    }
};
