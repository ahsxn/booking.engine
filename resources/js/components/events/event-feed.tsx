import { InfiniteScroll, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import EventCard from '@/components/events/event-card';
import EventCardSkeleton from '@/components/events/event-card-skeleton';
import AlertError from '@/components/alert-error';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import type { EventListingPageProps } from '@/types/events';

type Props = EventListingPageProps & { navigating: boolean };

function AppendLoadingIndicator({ loading }: { loading: boolean }) {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (!loading) {
            setVisible(false);
            return;
        }

        const timer = setTimeout(() => setVisible(true), 200);
        return () => clearTimeout(timer);
    }, [loading]);

    if (!loading) {
        return null;
    }

    return (
        <>
            <p role="status" className="sr-only">
                Loading more events.
            </p>
            {visible && <EventCardSkeleton />}
        </>
    );
}

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
                    preserveScroll: true,
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
            className="mb-4 inline-flex min-h-11 items-center text-sm font-medium underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
        >
            Start of this view
        </a>
    );
    if (events.data.length === 0) {
        return (
            <div className="grid gap-3 rounded-xl border border-dashed p-6 text-sm sm:p-8">
                <p className="font-medium">No {label} events.</p>
                {startLink}
                {error && (
                    <AlertError
                        errors={[error]}
                        title="Unable to load events"
                    />
                )}
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
                    <div className="grid justify-items-start gap-4 py-6">
                        <AppendLoadingIndicator loading={loading} />
                        {error && (
                            <AlertError
                                errors={[error]}
                                title="Unable to load events"
                            />
                        )}
                        {hasMore ? (
                            <Button
                                type="button"
                                onClick={fetch}
                                disabled={loading || navigating}
                                className="h-auto min-h-11 px-5 whitespace-normal focus-visible:ring-ring motion-reduce:transition-none"
                            >
                                {error ? 'Retry' : 'Load more'}
                            </Button>
                        ) : (
                            <p
                                role="status"
                                className="text-sm text-muted-foreground"
                            >
                                All {label} events loaded.
                            </p>
                        )}
                    </div>
                )}
            >
                {events.data.map((event) => (
                    <li key={event.id} className="event-feed-item">
                        <EventCard event={event} />
                    </li>
                ))}
            </InfiniteScroll>
        </section>
    );
}
