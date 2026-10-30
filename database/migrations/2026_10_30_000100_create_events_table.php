<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->string('slug', 160)->unique();
            $table->string('title');
            $table->text('description');
            $table->string('organizer_name', 120);
            $table->text('cover_image_url');
            $table->string('venue_name');
            $table->text('venue_address');
            $table->string('timezone', 64);

            $table->timestampTz('starts_at', precision: 0);
            $table->timestampTz('ends_at', precision: 0);
            $table->timestampsTz(precision: 0);

            $table->index(['starts_at', 'id']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE events
                ADD CONSTRAINT events_slug_format_check CHECK (slug ~ '^[a-z0-9]+(-[a-z0-9]+)*$'),
                ADD CONSTRAINT events_required_text_check CHECK (
                    btrim(title) <> '' AND btrim(description) <> '' AND btrim(organizer_name) <> ''
                    AND btrim(cover_image_url) <> '' AND btrim(venue_name) <> ''
                    AND btrim(venue_address) <> '' AND btrim(timezone) <> ''
                ),
                ADD CONSTRAINT events_schedule_check CHECK (
                    isfinite(starts_at) AND isfinite(ends_at) AND ends_at > starts_at
                )
            SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
