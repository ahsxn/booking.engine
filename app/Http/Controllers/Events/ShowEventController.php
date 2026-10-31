<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ShowEvent;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class ShowEventController extends Controller
{
    public function __invoke(Event $event, ShowEvent $showEvent): Response
    {
        return Inertia::render('events/show', ['event' => $showEvent->handle($event)]);
    }
}
