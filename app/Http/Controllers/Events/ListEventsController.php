<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ListEvents;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ListEventsController extends Controller
{
    public function __invoke(ListEvents $listEvents): Response
    {
        return Inertia::render('events/index', $listEvents->handle());
    }
}
