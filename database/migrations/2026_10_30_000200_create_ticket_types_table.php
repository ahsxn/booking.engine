<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->restrictOnDelete()->restrictOnUpdate();

            $table->string('code', 64);
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->integer('price_minor');
            $table->string('currency', 3)->default('GBP');
            $table->integer('capacity');

            $table->timestampsTz(precision: 0);

            $table->unique(['event_id', 'code']);
            $table->index(['event_id', 'display_order', 'id']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE ticket_types
                ADD CONSTRAINT ticket_types_code_format_check CHECK (code ~ '^[a-z0-9]+(-[a-z0-9]+)*$'),
                ADD CONSTRAINT ticket_types_name_check CHECK (btrim(name) <> ''),
                ADD CONSTRAINT ticket_types_display_order_check CHECK (display_order >= 0),
                ADD CONSTRAINT ticket_types_price_minor_check CHECK (price_minor > 0),
                ADD CONSTRAINT ticket_types_currency_check CHECK (currency = 'GBP'),
                ADD CONSTRAINT ticket_types_capacity_check CHECK (capacity > 0)
            SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
