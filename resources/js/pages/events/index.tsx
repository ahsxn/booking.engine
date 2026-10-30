import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import EventFeed from '@/components/events/event-feed';
import { home } from '@/routes';
import type { EventListingPageProps, EventView } from '@/types/events';

export default function EventsIndex({ view, events }: EventListingPageProps) {
    const [navigating, setNavigating] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const { errors } = usePage().props;
    return (
        <>
            <Head title="Events" />
            <main className="mx-auto min-h-screen w-full max-w-3xl space-y-8 bg-background px-6 py-12 break-words text-foreground">
                <h1 className="text-3xl font-semibold">Events</h1>
                <p>
                    This is a read-only event listing. Booking is not available
                    yet.
                </p>
                <nav aria-label="Event views" className="flex gap-6">
                    {(['upcoming', 'past'] as const).map(
                        (target: EventView) => (
                            <a
                                key={target}
                                href={home.url({ query: { view: target } })}
                                aria-current={
                                    view === target ? 'page' : undefined
                                }
                                className="underline focus-visible:outline-2"
                                onClick={(event) => {
                                    if (
                                        event.defaultPrevented ||
                                        event.button !== 0 ||
                                        event.metaKey ||
                                        event.ctrlKey ||
                                        event.shiftKey ||
                                        event.altKey ||
                                        (event.currentTarget.target &&
                                            event.currentTarget.target !==
                                                '_self')
                                    ) {
                                        return;
                                    }
                                    event.preventDefault();
                                    router.visit(
                                        home({ query: { view: target } }),
                                        {
                                            reset: ['events'],
                                            only: ['view', 'events'],
                                            preserveState: false,
                                            preserveScroll: false,
                                            onStart: () => {
                                                setNavigating(true);
                                                setError(null);
                                            },
                                            onFinish: () =>
                                                setNavigating(false),
                                            onNetworkError: () => {
                                                setError(
                                                    'Unable to switch views. Please try the view link again.',
                                                );
                                                return false;
                                            },
                                            onHttpException: (response) => {
                                                setError(
                                                    response.status === 429
                                                        ? 'Too many requests; try again shortly'
                                                        : 'Unable to switch views. Please try the view link again.',
                                                );
                                                return false;
                                            },
                                        },
                                    );
                                }}
                            >
                                {target === 'upcoming' ? 'Upcoming' : 'Past'}
                            </a>
                        ),
                    )}
                </nav>
                <h2 className="text-2xl font-semibold">
                    {view === 'upcoming' ? 'Upcoming' : 'Past'}
                </h2>
                {navigating && <p role="status">Loading events…</p>}
                {(error || errors.view) && (
                    <p role="alert">{error || errors.view}</p>
                )}
                <EventFeed
                    key={view}
                    view={view}
                    events={events}
                    navigating={navigating}
                />
            </main>
        </>
    );
}
