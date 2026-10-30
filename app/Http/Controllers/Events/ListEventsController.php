<?php

namespace App\Http\Controllers\Events;

use App\Actions\Events\ListEvents;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\ListEventsRequest;
use Inertia\Inertia;
use Inertia\Response;

class ListEventsController extends Controller
{
    public function __invoke(ListEventsRequest $request, ListEvents $listEvents): Response
    {
        $view = $request->selectedView();

        return Inertia::render('events/index', [
            'view' => $view,
            'events' => Inertia::scroll(fn () => $listEvents->handle($view))->matchOn('data.id'),
        ]);
    }
}
