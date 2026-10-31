<?php

namespace App\Actions\Events;

use App\Data\Events\EventListingData;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;

final class ListEvents
{
    private const int PAGE_SIZE = 20;

    /**
     * @param  'upcoming'|'past'  $view
     * @return Paginator<int, EventListingData>
     */
    public function handle(string $view): Paginator
    {
        $reference = CarbonImmutable::now('UTC');
        $direction = $view === 'upcoming' ? 'asc' : 'desc';

        return Event::query()
            ->select(['id', 'slug', 'title', 'cover_image_url', 'organizer_name', 'venue_name', 'venue_address', 'timezone', 'starts_at', 'ends_at'])
            ->with('ticketTypes:id,event_id,name,description,display_order,price_minor,currency')
            ->where('starts_at', $view === 'upcoming' ? '>' : '<=', $reference)
            ->orderBy('starts_at', $direction)
            ->orderBy('id', $direction)
            ->simplePaginate(self::PAGE_SIZE)
            ->appends(['view' => $view])
            ->through(fn (Event $event): EventListingData => EventListingData::fromModel($event));
    }
}
