<?php

namespace App\Data\Events;

use App\Models\TicketType;

final readonly class TicketTypeListingData
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public int $priceMinor,
        public string $currency,
        public string $priceLabel,
    ) {}

    public static function fromModel(TicketType $ticketType): self
    {
        return new self(
            id: $ticketType->id,
            name: $ticketType->name,
            description: $ticketType->description,
            priceMinor: $ticketType->price_minor,
            currency: $ticketType->currency,
            priceLabel: '£'.intdiv($ticketType->price_minor, 100).'.'.str_pad((string) ($ticketType->price_minor % 100), 2, '0', STR_PAD_LEFT),
        );
    }

    /** @return array{id: int, name: string, description: string|null, priceMinor: int, currency: string, priceLabel: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'priceMinor' => $this->priceMinor,
            'currency' => $this->currency,
            'priceLabel' => $this->priceLabel,
        ];
    }
}
