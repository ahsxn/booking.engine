<?php

namespace App\Actions\Events;

use App\Data\Events\EventDetailData;
use App\Data\Events\EventListingData;
use App\Models\Event;
use Carbon\CarbonImmutable;

final class ShowEvent
{
    public function handle(Event $event): EventDetailData
    {
        $event->load('ticketTypes:id,event_id,name,description,display_order,price_minor,currency');

        return new EventDetailData(
            summary: EventListingData::fromModel($event),
            description: $event->description,
            selectionOpen: $event->starts_at->isAfter(CarbonImmutable::now('UTC')),
        );
    }
}
