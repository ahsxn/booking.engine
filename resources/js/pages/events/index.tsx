import { Head, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import EventCardSkeleton from '@/components/events/event-card-skeleton';
import EventFeed from '@/components/events/event-feed';
import OrganizerHero from '@/components/events/organizer-hero';
import VisualCalendar from '@/components/events/visual-calendar';
import AlertError from '@/components/alert-error';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import type { EventListingPageProps, EventView } from '@/types/events';

export default function EventsIndex({ view, events }: EventListingPageProps) {
    const [navigating, setNavigating] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [showViewSkeleton, setShowViewSkeleton] = useState(false);
    const [requestedView, setRequestedView] = useState(view);
    const skeletonDelay = useRef<ReturnType<typeof setTimeout> | undefined>(
        undefined,
    );
    const showingViewSkeleton = navigating && showViewSkeleton;
    const { errors } = usePage().props;

    useEffect(() => () => clearTimeout(skeletonDelay.current), []);

    function finishNavigation() {
        clearTimeout(skeletonDelay.current);
        skeletonDelay.current = undefined;
        setShowViewSkeleton(false);
        setNavigating(false);
    }

    return (
        <div className="event-browsing min-h-screen bg-background [overflow-wrap:anywhere] text-foreground">
            <Head title="Events" />
            <a
                href="#events"
                className="sr-only z-50 rounded-lg bg-primary p-3 text-primary-foreground focus:not-sr-only focus:fixed focus:top-3 focus:left-3"
            >
                Skip to events
            </a>
            <header className="mx-auto flex min-h-16 max-w-[68rem] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <span className="text-sm font-semibold tracking-tight">
                    Margin &amp; Lantern
                </span>
                <a
                    href="#events"
                    className="inline-flex min-h-11 items-center text-sm underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                >
                    Events
                </a>
            </header>
            <main className="mx-auto max-w-[68rem] pb-16 sm:px-6 lg:px-8">
                <OrganizerHero />
                <section
                    id="events"
                    tabIndex={-1}
                    aria-labelledby="events-heading"
                    className="border-t px-4 pt-6 outline-none sm:px-0 sm:pt-8"
                >
                    <div className="mb-2">
                        <h2
                            id="events-heading"
                            className="text-2xl font-semibold tracking-tight"
                        >
                            Events
                        </h2>
                    </div>
                    <p className="mb-5 text-sm leading-relaxed text-muted-foreground">
                        This is a read-only event listing. Booking is not
                        available yet.
                    </p>
                    <p role="status" className="sr-only">
                        {navigating ? `Loading ${requestedView} events.` : ''}
                    </p>
                    {(error || errors.view) && (
                        <div className="mb-4">
                            <AlertError
                                errors={[error || errors.view]}
                                title="Unable to switch views"
                            />
                        </div>
                    )}
                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:gap-8">
                        <aside className="min-w-0 lg:col-start-2 lg:row-start-1 lg:rounded-xl lg:border lg:p-1">
                            <VisualCalendar />
                            <nav
                                aria-label="Event views"
                                className="flex gap-1 rounded-lg bg-muted p-1"
                            >
                                {(['upcoming', 'past'] as const).map(
                                    (target: EventView) => (
                                        <a
                                            key={target}
                                            href={home.url({
                                                query: { view: target },
                                            })}
                                            aria-current={
                                                view === target
                                                    ? 'page'
                                                    : undefined
                                            }
                                            className={cn(
                                                buttonVariants({
                                                    variant: 'ghost',
                                                }),
                                                'h-auto min-h-11 flex-1 px-4 py-2 whitespace-normal focus-visible:ring-ring motion-reduce:transition-none',
                                                view === target &&
                                                    'bg-card text-foreground shadow-xs',
                                            )}
                                            onClick={(event) => {
                                                if (
                                                    event.defaultPrevented ||
                                                    event.button !== 0 ||
                                                    event.metaKey ||
                                                    event.ctrlKey ||
                                                    event.shiftKey ||
                                                    event.altKey ||
                                                    (event.currentTarget
                                                        .target &&
                                                        event.currentTarget
                                                            .target !== '_self')
                                                ) {
                                                    return;
                                                }
                                                event.preventDefault();
                                                router.visit(
                                                    home({
                                                        query: { view: target },
                                                    }),
                                                    {
                                                        reset: ['events'],
                                                        only: [
                                                            'view',
                                                            'events',
                                                        ],
                                                        preserveState: false,
                                                        preserveScroll: true,
                                                        onStart: () => {
                                                            clearTimeout(
                                                                skeletonDelay.current,
                                                            );
                                                            skeletonDelay.current =
                                                                undefined;
                                                            setNavigating(true);
                                                            setError(null);
                                                            setRequestedView(
                                                                target,
                                                            );
                                                            setShowViewSkeleton(
                                                                false,
                                                            );
                                                            if (
                                                                target !== view
                                                            ) {
                                                                skeletonDelay.current =
                                                                    setTimeout(
                                                                        () =>
                                                                            setShowViewSkeleton(
                                                                                true,
                                                                            ),
                                                                        200,
                                                                    );
                                                            }
                                                        },
                                                        onCancel:
                                                            finishNavigation,
                                                        onFinish:
                                                            finishNavigation,
                                                        onNetworkError: () => {
                                                            finishNavigation();
                                                            setError(
                                                                'Unable to switch views. Please try the view link again.',
                                                            );
                                                            return false;
                                                        },
                                                        onHttpException: (
                                                            response,
                                                        ) => {
                                                            finishNavigation();
                                                            setError(
                                                                response.status ===
                                                                    429
                                                                    ? 'Too many requests; try again shortly'
                                                                    : 'Unable to switch views. Please try the view link again.',
                                                            );
                                                            return false;
                                                        },
                                                    },
                                                );
                                            }}
                                        >
                                            {target === 'upcoming'
                                                ? 'Upcoming'
                                                : 'Past'}
                                        </a>
                                    ),
                                )}
                            </nav>
                        </aside>
                        <div className="min-w-0 lg:col-start-1 lg:row-start-1">
                            <h3 className="sr-only">
                                {view === 'upcoming'
                                    ? 'Upcoming events'
                                    : 'Past events'}
                            </h3>
                            <div
                                aria-busy={navigating}
                                className="relative min-h-[30rem]"
                            >
                                <div
                                    className={
                                        showingViewSkeleton
                                            ? 'invisible'
                                            : undefined
                                    }
                                    inert={showingViewSkeleton || undefined}
                                    aria-hidden={
                                        showingViewSkeleton || undefined
                                    }
                                >
                                    <EventFeed
                                        key={view}
                                        view={view}
                                        events={events}
                                        navigating={navigating}
                                    />
                                </div>
                                {showingViewSkeleton && (
                                    <div className="pointer-events-none absolute inset-x-0 top-0">
                                        <EventCardSkeleton count={2} />
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    );
}
