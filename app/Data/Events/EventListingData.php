<?php

namespace App\Data\Events;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class EventListingData implements Arrayable
{
    /** @param list<TicketTypeListingData> $ticketTypes */
    public function __construct(
        public int $id,
        public string $slug,
        public string $title,
        public string $coverImageUrl,
        public string $localStartDate,
        public string $organizerName,
        public string $venueName,
        public string $venueAddress,
        public string $timezone,
        public string $startsAt,
        public string $endsAt,
        public string $startsAtLabel,
        public string $endsAtLabel,
        public array $ticketTypes,
    ) {}

    public static function fromModel(Event $event): self
    {
        $localStart = $event->starts_at->setTimezone($event->timezone);
        $localEnd = $event->ends_at->setTimezone($event->timezone);

        return new self(
            id: $event->id,
            slug: $event->slug,
            title: $event->title,
            coverImageUrl: $event->cover_image_url,
            localStartDate: $localStart->format('Y-m-d'),
            organizerName: $event->organizer_name,
            venueName: $event->venue_name,
            venueAddress: $event->venue_address,
            timezone: $event->timezone,
            startsAt: $event->starts_at->toISOString(),
            endsAt: $event->ends_at->toISOString(),
            startsAtLabel: $localStart->format('j F Y, H:i (P)'),
            endsAtLabel: $localEnd->format('j F Y, H:i (P)'),
            ticketTypes: array_values($event->ticketTypes->map(fn (TicketType $ticketType): TicketTypeListingData => TicketTypeListingData::fromModel($ticketType))->values()->all()),
        );
    }

    /**
     * @return array{
     *     id: int, slug: string, title: string, coverImageUrl: string, localStartDate: string,
     *     organizerName: string, venueName: string, venueAddress: string, timezone: string,
     *     startsAt: string, endsAt: string, startsAtLabel: string, endsAtLabel: string,
     *     ticketTypes: list<array{id: int, name: string, description: string|null, priceMinor: int, currency: string, priceLabel: string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'coverImageUrl' => $this->coverImageUrl,
            'localStartDate' => $this->localStartDate,
            'organizerName' => $this->organizerName,
            'venueName' => $this->venueName,
            'venueAddress' => $this->venueAddress,
            'timezone' => $this->timezone,
            'startsAt' => $this->startsAt,
            'endsAt' => $this->endsAt,
            'startsAtLabel' => $this->startsAtLabel,
            'endsAtLabel' => $this->endsAtLabel,
            'ticketTypes' => array_map(fn (TicketTypeListingData $ticketType): array => $ticketType->toArray(), $this->ticketTypes),
        ];
    }
}
