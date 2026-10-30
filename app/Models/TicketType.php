<?php

namespace App\Models;

use Database\Factories\TicketTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $display_order
 * @property int $price_minor
 * @property string $currency
 * @property int $capacity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 */
#[Fillable(['event_id', 'code', 'name', 'description', 'display_order', 'price_minor', 'currency', 'capacity'])]
class TicketType extends Model
{
    /** @use HasFactory<TicketTypeFactory> */
    use HasFactory;

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['price_minor' => 'integer', 'capacity' => 'integer', 'display_order' => 'integer'];
    }
}
