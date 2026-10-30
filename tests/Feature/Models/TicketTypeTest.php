<?php

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

test('belongs to its exact event', function () {
    $event = Event::factory()->create();
    $unrelated = Event::factory()->create();
    $ticket = TicketType::factory()->for($event)->create();

    expect($ticket->fresh()->event->is($event))->toBeTrue();
    expect($ticket->event->is($unrelated))->toBeFalse();
});

test('returns integer pence and independent total capacities', function () {
    $event = Event::factory()->create();
    $first = TicketType::factory()->for($event)->create(['price_minor' => 1250, 'capacity' => 3, 'display_order' => 0]);
    $second = TicketType::factory()->for($event)->create(['price_minor' => 2000, 'capacity' => 7]);

    expect($first->fresh()->price_minor)->toBe(1250);
    expect($first->capacity)->toBe(3);
    expect($first->display_order)->toBe(0);
    expect($first->currency)->toBe('GBP');
    expect($second->fresh()->price_minor)->toBe(2000);
    $first->update(['capacity' => 5]);
    expect($first->fresh()->capacity)->toBe(5);
    expect($second->fresh()->capacity)->toBe(7);
    expect($event->fresh()->getAttributes())->not->toHaveKey('capacity');
});
