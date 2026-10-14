<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('programs', function (Blueprint $t) {
        $t->id();
        $t->string('short', 10);
        $t->string('icon', 20);
        $t->string('name');
        $t->text('description');
        $t->json('skills');
        $t->json('jobs');
        $t->json('facilities');
        $t->timestamps();
    });

    Schema::create('posts', function (Blueprint $t) {
        $t->id();
        $t->string('category', 30);
        $t->string('title');
        $t->text('excerpt');
        $t->date('published_at');
        $t->unsignedTinyInteger('art')->default(0);
        $t->timestamps();
    });

    Schema::create('agendas', function (Blueprint $t) {
        $t->id();
        $t->date('date');
        $t->string('title');
        $t->string('place_time');
        $t->timestamps();
    });

    Schema::create('achievements', function (Blueprint $t) {
        $t->id();
        $t->string('title');
        $t->string('who');
        $t->string('level', 30);
        $t->unsignedSmallInteger('year');
        $t->timestamps();
    });

    Schema::create('gallery_items', function (Blueprint $t) {
        $t->id();
        $t->string('category', 30);
        $t->string('title');
        $t->string('size', 5)->default('');
        $t->unsignedTinyInteger('art')->default(0);
        $t->timestamps();
    });

    Schema::create('japan_steps', function (Blueprint $t) {
        $t->id();
        $t->string('title');
        $t->text('description');
        $t->string('duration');
        $t->timestamps();
    });

    Schema::create('partners', function (Blueprint $t) {
        $t->id();
        $t->string('name');
        $t->string('category', 30);
        $t->timestamps();
    });

    Schema::create('alumni', function (Blueprint $t) {
        $t->id();
        $t->string('name');
        $t->text('role_desc');
        $t->text('quote');
        $t->timestamps();
    });

    Schema::create('tracer_stats', function (Blueprint $t) {
        $t->id();
        $t->string('label');
        $t->unsignedTinyInteger('value');
        $t->string('color');
        $t->timestamps();
    });

    Schema::create('announcements', function (Blueprint $t) {
        $t->id();
        $t->string('text');
        $t->timestamps();
    });
}

public function down(): void
{
    foreach (['announcements', 'tracer_stats', 'alumni', 'partners', 'japan_steps',
              'gallery_items', 'achievements', 'agendas', 'posts', 'programs'] as $table) {
        Schema::dropIfExists($table);
    }
}
};
