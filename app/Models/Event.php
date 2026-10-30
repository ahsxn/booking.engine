<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\EventFactory;
use DateTimeZone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $organizer_name
 * @property string $cover_image_url
 * @property string $venue_name
 * @property string $venue_address
 * @property string $timezone
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, TicketType> $ticketTypes
 */
#[Fillable(['slug', 'title', 'description', 'organizer_name', 'cover_image_url', 'venue_name', 'venue_address', 'timezone', 'starts_at', 'ends_at'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /** @return HasMany<TicketType, $this> */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class)->orderBy('display_order')->orderBy('id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /** @return Attribute<string, string> */
    protected function timezone(): Attribute
    {
        return Attribute::make(set: function (string $value): string {
            if ($value !== 'UTC' && ! in_array($value, DateTimeZone::listIdentifiers(DateTimeZone::ALL), true)) {
                throw new InvalidArgumentException('The event timezone must be an exact IANA timezone identifier.');
            }

            return $value;
        });
    }
}
