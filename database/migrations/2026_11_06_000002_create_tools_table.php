<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('image');
            $table->string('appearance')->default('original');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $tools = json_decode(file_get_contents(database_path('seeders/tool-logos.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($tools as $tool) {
            DB::table('tools')->insert($tool + ['created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
