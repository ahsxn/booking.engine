import { InfiniteScroll, router } from '@inertiajs/react';
import { useState } from 'react';
import { home } from '@/routes';
import type { EventListingPageProps } from '@/types/events';

type Props = EventListingPageProps & { navigating: boolean };

export default function EventFeed({ view, events, navigating }: Props) {
    const [error, setError] = useState<string | null>(null);
    const label = view === 'past' ? 'past' : 'upcoming';
    const startLink = events.current_page > 1 && (
        <a
            href={home.url({ query: { view } })}
            onClick={(event) => {
                if (
                    event.defaultPrevented ||
                    event.button !== 0 ||
                    event.metaKey ||
                    event.ctrlKey ||
                    event.shiftKey ||
                    event.altKey ||
                    (event.currentTarget.target &&
                        event.currentTarget.target !== '_self')
                ) {
                    return;
                }
                event.preventDefault();
                router.visit(home({ query: { view } }), {
                    reset: ['events'],
                    only: ['view', 'events'],
                    preserveState: false,
                    preserveScroll: false,
                    onNetworkError: () => {
                        setError(
                            'Unable to load events. Please try the start link again.',
                        );
                        return false;
                    },
                    onHttpException: (response) => {
                        setError(
                            response.status === 429
                                ? 'Too many requests; try again shortly'
                                : 'Unable to load events. Please try the start link again.',
                        );
                        return false;
                    },
                });
            }}
            className="underline focus-visible:outline-2"
        >
            Start of this view
        </a>
    );
    if (events.data.length === 0) {
        return (
            <div>
                <p>No {label} events.</p>
                {startLink}
                {error && <p role="alert">{error}</p>}
            </div>
        );
    }
    return (
        <section aria-label={`${view === 'past' ? 'Past' : 'Upcoming'} events`}>
            {startLink}
            <InfiniteScroll
                key={view}
                data="events"
                as="ul"
                onlyNext
                preserveUrl
                buffer={300}
                params={{
                    onNetworkError: () => {
                        setError(
                            'Unable to load more events. Please try again.',
                        );
                        return false;
                    },
                    onHttpException: (response) => {
                        setError(
                            response.status === 429
                                ? 'Too many requests; try again shortly'
                                : 'Unable to load more events. Please try again.',
                        );
                        return false;
                    },
                    onSuccess: () => setError(null),
                }}
                manual={error !== null || navigating}
                className="space-y-8"
                next={({ loading, fetch, hasMore }) => (
                    <div className="py-4" aria-live="polite">
                        {loading && <p role="status">Loading more events…</p>}
                        {error && <p role="alert">{error}</p>}
                        {hasMore ? (
                            <button
                                type="button"
                                onClick={fetch}
                                disabled={loading || navigating}
                                className="underline focus-visible:outline-2"
                            >
                                {error ? 'Retry' : 'Load more'}
                            </button>
                        ) : (
                            <p>All {label} events loaded.</p>
                        )}
                    </div>
                )}
            >
                {events.data.map((event) => (
                    <li key={event.id} className="space-y-2">
                        <h3 className="text-xl font-semibold">{event.title}</h3>
                        <p>{event.organizerName}</p>
                        <p>
                            {event.venueName} — {event.venueAddress}
                        </p>
                        <p>
                            Starts:{' '}
                            <time dateTime={event.startsAt}>
                                {event.startsAtLabel}
                            </time>
                            <br />
                            Ends:{' '}
                            <time dateTime={event.endsAt}>
                                {event.endsAtLabel}
                            </time>
                            <br />
                            Time zone: {event.timezone}
                        </p>
                        {event.ticketTypes.length === 0 ? (
                            <p>
                                No ticket information is listed for this event.
                            </p>
                        ) : (
                            <ul className="space-y-2">
                                {event.ticketTypes.map((ticket) => (
                                    <li key={ticket.id}>
                                        <p>
                                            {ticket.name}: {ticket.priceLabel}{' '}
                                            {ticket.currency}
                                        </p>
                                        {ticket.description && (
                                            <p>{ticket.description}</p>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </li>
                ))}
            </InfiniteScroll>
        </section>
    );
}
