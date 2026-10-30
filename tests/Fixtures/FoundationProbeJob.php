<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class FoundationProbeJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $marker, public readonly bool $shouldFail = false) {}

    public function handle(): void
    {
        Cache::store('database')->increment($this->marker.':attempts');

        if ($this->shouldFail) {
            throw new RuntimeException('Intentional foundation probe failure.');
        }

        Cache::store('database')->put($this->marker, 'processed', 60);
    }
}
