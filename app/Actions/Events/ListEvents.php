<?php

namespace App\Actions\Events;

use App\Data\Events\EventListingData;
use App\Models\Event;
use Carbon\CarbonImmutable;

final class ListEvents
{
    /** @return array{upcoming: list<EventListingData>, past: list<EventListingData>} */
    public function handle(): array
    {
        $reference = CarbonImmutable::now('UTC');

        $events = Event::query()
            ->select(['id', 'slug', 'title', 'cover_image_url', 'organizer_name', 'venue_name', 'venue_address', 'timezone', 'starts_at', 'ends_at'])
            ->with('ticketTypes:id,event_id,name,description,display_order,price_minor,currency')
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get();

        $upcoming = [];
        $past = [];

        foreach ($events as $event) {
            $data = EventListingData::fromModel($event);

            if ($event->starts_at->greaterThan($reference)) {
                $upcoming[] = $data;
            } else {
                $past[] = $data;
            }
        }

        return ['upcoming' => $upcoming, 'past' => array_reverse($past)];
    }
}
