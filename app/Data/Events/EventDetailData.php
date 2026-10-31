<?php

namespace App\Data\Events;

use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class EventDetailData implements Arrayable
{
    public function __construct(
        public EventListingData $summary,
        public string $description,
        public bool $selectionOpen,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            ...$this->summary->toArray(),
            'description' => $this->description,
            'selectionOpen' => $this->selectionOpen,
        ];
    }
}
