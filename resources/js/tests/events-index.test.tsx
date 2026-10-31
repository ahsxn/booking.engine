import type { Mock } from 'vite-plus/test';
vi.mock('@/routes', () => import('../routes'));
vi.mock('@/routes/events', () => import('../routes/events'));
vi.mock(
    '@/components/events/event-feed',
    () => import('../components/events/event-feed'),
);
vi.mock(
    '@/components/events/event-card',
    () => import('../components/events/event-card'),
);
vi.mock(
    '@/components/events/event-cover',
    () => import('../components/events/event-cover'),
);
vi.mock(
    '@/components/events/ticket-type-list',
    () => import('../components/events/ticket-type-list'),
);
vi.mock('@/components/ui/card', () => import('../components/ui/card'));
vi.mock('@/components/ui/button', () => import('../components/ui/button'));
vi.mock('@/components/ui/skeleton', () => import('../components/ui/skeleton'));
vi.mock('@/components/alert-error', () => import('../components/alert-error'));
vi.mock('@/components/ui/alert', () => import('../components/ui/alert'));
vi.mock('@/lib/utils', () => import('../lib/utils'));
vi.mock(
    '@/components/events/organizer-hero',
    () => import('../components/events/organizer-hero'),
);
vi.mock(
    '@/components/events/visual-calendar',
    () => import('../components/events/visual-calendar'),
);
vi.mock(
    '@/components/events/event-category-badge',
    () => import('../components/events/event-category-badge'),
);
vi.mock(
    '@/components/events/event-attendee-stack',
    () => import('../components/events/event-attendee-stack'),
);
vi.mock('@/components/ui/badge', () => import('../components/ui/badge'));
vi.mock('@/components/ui/avatar', () => import('../components/ui/avatar'));
vi.mock('@/lib/event-presentation', () => import('../lib/event-presentation'));
vi.mock(
    '@/components/events/event-card-skeleton',
    () => import('../components/events/event-card-skeleton'),
);
import EventCardSkeleton from '../components/events/event-card-skeleton';
import EventCard from '../components/events/event-card';
import EventCover from '../components/events/event-cover';
import VisualCalendar from '../components/events/visual-calendar';
import { isValidElement } from 'react';
import type {
    ComponentProps,
    MouseEvent,
    ReactElement,
    ReactNode,
} from 'react';
import { router } from '@inertiajs/react';
import type { InfiniteScroll } from '@inertiajs/react';
import { http, HttpNetworkError, HttpResponseError } from '@inertiajs/core';
import EventFeed from '../components/events/event-feed';
import { renderToStaticMarkup } from 'react-dom/server';
import {
    afterAll,
    afterEach,
    beforeAll,
    describe,
    expect,
    it,
    vi,
} from 'vite-plus/test';
import EventsIndex from '../pages/events';
import type { EventListing, EventListingPageProps } from '../types/events';

const { scrollState, scrollOptions, setError, localState, clockSubscription } =
    vi.hoisted(() => ({
        scrollState: { loading: false, hasMore: false },
        scrollOptions: vi.fn(),
        setError: vi.fn(),
        localState: {
            error: null as string | null,
            failedSource: '',
            serverClock: false,
        },
        clockSubscription: vi.fn(),
    }));
const componentHooks = vi.hoisted(() => ({
    active: false,
    cursor: 0,
    cells: [] as Array<{
        value: unknown;
        dependencies?: readonly unknown[];
        cleanup?: () => void;
    }>,
    pending: [] as Array<() => void>,
}));

afterEach(() => {
    for (const cell of componentHooks.cells) cell.cleanup?.();
    componentHooks.active = false;
    componentHooks.cursor = 0;
    componentHooks.cells = [];
    componentHooks.pending = [];
    vi.restoreAllMocks();
    scrollState.loading = false;
    scrollState.hasMore = false;
    localState.error = null;
    localState.failedSource = '';
    localState.serverClock = false;
    vi.useRealTimers();
    vi.clearAllMocks();
});

vi.mock('react', async (importOriginal) => ({
    ...(await importOriginal<typeof import('react')>()),
    useSyncExternalStore: (
        subscribe: (listener: () => void) => () => void,
        getSnapshot: () => unknown,
        getServerSnapshot: () => unknown,
    ) => {
        clockSubscription(subscribe);
        return localState.serverClock ? getServerSnapshot() : getSnapshot();
    },
    useState: (initial: unknown) => {
        if (componentHooks.active) {
            const index = componentHooks.cursor++;
            const cell = (componentHooks.cells[index] ??= {
                value: typeof initial === 'function' ? initial() : initial,
            });
            return [
                cell.value,
                (value: unknown) => {
                    cell.value =
                        typeof value === 'function' ? value(cell.value) : value;
                },
            ];
        }
        return [
            initial === null
                ? localState.error
                : initial === ''
                  ? localState.failedSource
                  : initial,
            setError,
        ];
    },
    useRef: (initial: unknown) => {
        if (!componentHooks.active) return { current: initial };
        const index = componentHooks.cursor++;
        const cell = (componentHooks.cells[index] ??= {
            value: { current: initial },
        });
        return cell.value;
    },
    useEffect: (
        effect: () => void | (() => void),
        dependencies?: readonly unknown[],
    ) => {
        if (!componentHooks.active) return;
        const index = componentHooks.cursor++;
        const cell = (componentHooks.cells[index] ??= { value: undefined });
        if (
            !dependencies ||
            !cell.dependencies ||
            dependencies.length !== cell.dependencies.length ||
            dependencies.some(
                (value, i) => !Object.is(value, cell.dependencies![i]),
            )
        ) {
            componentHooks.pending.push(() => {
                cell.cleanup?.();
                const cleanup = effect();
                cell.cleanup =
                    typeof cleanup === 'function' ? cleanup : undefined;
                cell.dependencies = dependencies
                    ? [...dependencies]
                    : undefined;
            });
        }
    },
}));

vi.mock('@inertiajs/react', async (importOriginal) => ({
    ...(await importOriginal<typeof import('@inertiajs/react')>()),
    Head: () => null,
    usePage: () => ({ props: { errors: {} } }),
    InfiniteScroll: ({
        children,
        next,
        ...options
    }: ComponentProps<typeof InfiniteScroll>) => {
        scrollOptions(options);
        return (
            <>
                <ul>{children as ReactNode}</ul>
                {typeof next === 'function'
                    ? next({
                          loading: scrollState.loading,
                          loadingPrevious: false,
                          loadingNext: false,
                          fetch: vi.fn(),
                          hasMore: scrollState.hasMore,
                          hasNext: false,
                          hasPrevious: false,
                          autoMode: true,
                          manualMode: false,
                      })
                    : next}
            </>
        );
    },
}));

const event: EventListing = {
    id: 1,
    slug: 'year-boundary',
    title: 'A New Chapter',
    coverImageUrl: '/unused-cover.svg',
    localStartDate: '2027-01-01',
    organizerName: 'Margin & Lantern',
    venueName: 'Reading Room',
    venueAddress: '1 Library Street',
    timezone: 'Europe/Helsinki',
    startsAt: '2026-12-31T23:30:00.000000Z',
    endsAt: '2027-01-01T01:30:00.000000Z',
    startsAtLabel: '1 January 2027, 01:30 (+02:00)',
    endsAtLabel: '1 January 2027, 03:30 (+02:00)',
    ticketTypes: [
        {
            id: 1,
            name: 'General admission',
            description: null,
            priceMinor: 1250,
            currency: 'GBP',
            priceLabel: '£12.50',
        },
        {
            id: 2,
            name: 'Supporter admission',
            description: 'The same admission at a supporter price.',
            priceMinor: 2000,
            currency: 'GBP',
            priceLabel: '£20.00',
        },
    ],
};

function props(
    upcoming: EventListing[],
    past: EventListing[] = [],
    view: 'upcoming' | 'past' = 'upcoming',
): EventListingPageProps {
    return {
        view,
        events: {
            current_page: 1,
            current_page_url: `/?view=${view}&page=1`,
            first_page_url: `/?view=${view}&page=1`,
            path: '/',
            from: 1,
            to: 1,
            data: view === 'upcoming' ? upcoming : past,
            per_page: 20,
            next_page_url: null,
            prev_page_url: null,
        },
    };
}
function render(
    groups: { upcoming: EventListing[]; past: EventListing[] },
    view: 'upcoming' | 'past' = 'upcoming',
) {
    return renderToStaticMarkup(
        <EventsIndex {...props(groups.upcoming, groups.past, view)} />,
    );
}

describe('event listing', () => {
    it('renders compact event summaries in the supplied group order', () => {
        const html = render({
            upcoming: [event, { ...event, id: 2, title: 'Second event' }],
            past: [{ ...event, id: 3, title: 'Past event' }],
        });
        for (const text of [
            'Events',
            'Upcoming',
            'Past',
            'A New Chapter',
            'Margin &amp; Lantern',
            'Reading Room',
            '01:30',
            'From £12.50',
        ]) {
            expect(html).toContain(text);
        }
        expect(html.indexOf('A New Chapter')).toBeLessThan(
            html.indexOf('Second event'),
        );
        expect(html).not.toContain('Past event');
        expect(html).toContain('href="/?view=past"');
        expect(html).toContain('aria-current="page"');
        expect(html).toContain('All upcoming events loaded.');
        for (const hidden of [
            '1 Library Street',
            'General admission',
            'Supporter admission',
            'The same admission at a supporter price.',
            '£20.00',
            'GBP',
            event.startsAtLabel,
            event.endsAtLabel,
            event.timezone,
        ]) {
            expect(html).not.toContain(hidden);
        }
        expect(html).not.toContain('null');
    });

    it.each([
        {
            timezone: 'Europe/Helsinki',
            instant: '2026-12-31T23:30:00.000000Z',
            localDate: '2027-01-01',
            time: '01:30',
        },
        {
            timezone: 'America/New_York',
            instant: '2026-12-31T23:30:00.000000Z',
            localDate: '2026-12-31',
            time: '18:30',
        },
        {
            timezone: 'Europe/London',
            instant: '2026-10-25T00:30:00.000000Z',
            localDate: '2026-10-25',
            time: '01:30',
        },
        {
            timezone: 'Europe/London',
            instant: '2026-10-25T01:30:00.000000Z',
            localDate: '2026-10-25',
            time: '01:30',
        },
    ])(
        'shows event-local time for $timezone at $instant',
        ({ timezone, instant, localDate, time }) => {
            const html = render({
                upcoming: [
                    {
                        ...event,
                        timezone,
                        startsAt: instant,
                        localStartDate: localDate,
                    },
                ],
                past: [],
            });
            expect(html).toContain('>' + time + '</time>');
            expect(html).toContain('dateTime="' + instant + '"');
            expect(html).toContain('dateTime="' + localDate + '"');
            expect(html).not.toContain(timezone);
            expect(html).not.toContain('Starts:');
            expect(html).not.toContain('Ends:');
        },
    );

    it.each([
        { upcoming: [], past: [], upcomingEmpty: true, pastEmpty: true },
        { upcoming: [event], past: [], upcomingEmpty: false, pastEmpty: true },
        { upcoming: [], past: [event], upcomingEmpty: true, pastEmpty: false },
    ])(
        'shows independent empty states for $upcomingEmpty/$pastEmpty',
        ({ upcoming, past, upcomingEmpty, pastEmpty }) => {
            const html = render({ upcoming, past });
            expect(html.includes('No upcoming events.')).toBe(upcomingEmpty);
            expect(html).not.toContain('No past events.');
            const pastHtml = render({ upcoming, past }, 'past');
            expect(pastHtml.includes('No past events.')).toBe(pastEmpty);
            expect(pastHtml).not.toContain('No upcoming events.');
            if (upcoming.length) {
                expect(html).toContain('A New Chapter');
            }
        },
    );

    it('omits price for a ticketless event without a sales claim', () => {
        const html = render({
            upcoming: [{ ...event, ticketTypes: [] }],
            past: [],
        });
        expect(html).not.toContain('event-price-badge');
        expect(html).not.toMatch(/free|sold out|available tickets/i);
    });

    it.each([
        {
            tickets: [event.ticketTypes[1], event.ticketTypes[0]],
            price: '£12.50',
        },
        { tickets: [event.ticketTypes[1]], price: '£20.00' },
        {
            tickets: [
                event.ticketTypes[0],
                {
                    ...event.ticketTypes[1],
                    priceMinor: 1250,
                    priceLabel: '£12.50',
                },
            ],
            price: '£12.50',
        },
    ])(
        'shows the truthful starting price $price without mutating tickets',
        ({ tickets, price }) => {
            const before = tickets.map((ticket) => ({ ...ticket }));
            const html = render({
                upcoming: [{ ...event, ticketTypes: tickets }],
                past: [],
            });
            expect(html).toContain('From ' + price);
            expect(tickets).toEqual(before);
        },
    );

    it.each([
        { slug: 'paper-trails', label: 'Reading circle', tone: 'violet' },
        {
            slug: 'city-in-the-margins',
            label: 'Book discussion',
            tone: 'violet',
        },
        { slug: 'lantern-pages', label: 'Short fiction', tone: 'orange' },
        {
            slug: 'new-year-new-chapter',
            label: 'Reading circle',
            tone: 'violet',
        },
    ])('uses the approved category for $slug', ({ slug, label, tone }) => {
        const html = render({ upcoming: [{ ...event, slug }], past: [] });
        expect(html).toContain(label);
        expect(html).toContain('data-tone="' + tone + '"');
    });

    it.each(['year-boundary', 'constructor', 'toString'])(
        'does not invent a category for %s',
        (slug) => {
            expect(
                render({ upcoming: [{ ...event, slug }], past: [] }),
            ).not.toContain('event-category-badge');
        },
    );

    it('renders attendee visuals only from explicitly supplied presentation data', () => {
        const live = render({ upcoming: [event], past: [] });
        expect(live).not.toContain('aria-label="Attendees"');
        const illustrative = renderToStaticMarkup(
            <EventCard
                event={event}
                attendees={[
                    { name: 'Alex Lane', initials: 'AL' },
                    { name: '<Jamie Shaw>', initials: 'JS' },
                ]}
            />,
        );
        expect(illustrative).toContain('aria-label="Attendees"');
        expect(illustrative).toContain('Alex Lane');
        expect(illustrative).toContain('&lt;Jamie Shaw&gt;');
        expect(illustrative).toContain('AL');
        expect(illustrative).not.toMatch(
            /registered|sold out|available tickets|\+\d/i,
        );
    });

    it('escapes catalogue content while showing covers without future-slice controls', () => {
        const html = render({
            upcoming: [
                {
                    ...event,
                    title: '<script>alert(1)</script>',
                    venueName: '<b>Venue</b>',
                    ticketTypes: [
                        {
                            ...event.ticketTypes[0],
                            name: '<em>Ticket</em>',
                            priceLabel: '<em>£12.50</em>',
                        },
                    ],
                },
            ],
            past: [],
        });
        expect(html).toContain('&lt;script&gt;alert(1)&lt;/script&gt;');
        expect(html).toContain('&lt;b&gt;Venue&lt;/b&gt;');
        expect(html).toContain('From &lt;em&gt;£12.50&lt;/em&gt;');
        expect(html).not.toContain('&lt;em&gt;Ticket&lt;/em&gt;');
        expect(html).not.toMatch(
            /<script|<input|<select|sold out|remaining tickets/i,
        );
        expect(html).toContain('src="/unused-cover.svg"');
        expect(html).toContain('alt=""');
    });
});

type AnchorProps = ComponentProps<'a'>;
function anchors(node: ReactNode): AnchorProps[] {
    if (Array.isArray(node)) {
        return node.flatMap(anchors);
    }
    if (!isValidElement<{ children?: ReactNode }>(node)) {
        return [];
    }
    return node.type === 'a'
        ? [node.props as AnchorProps]
        : anchors(node.props.children);
}
function click(overrides: Partial<MouseEvent<HTMLAnchorElement>> = {}) {
    return {
        button: 0,
        defaultPrevented: false,
        metaKey: false,
        ctrlKey: false,
        shiftKey: false,
        altKey: false,
        currentTarget: { target: '' },
        preventDefault: vi.fn(),
        ...overrides,
    } as unknown as Omit<MouseEvent<HTMLAnchorElement>, 'preventDefault'> & {
        preventDefault: Mock<() => void>;
    };
}

beforeAll(async () => {
    // Minimal host interfaces for the real Inertia router; no DOM/browser simulation.
    const events = new EventTarget();
    vi.stubGlobal('document', {
        dispatchEvent: events.dispatchEvent.bind(events),
        addEventListener: events.addEventListener.bind(events),
        removeEventListener: events.removeEventListener.bind(events),
        querySelectorAll: () => [],
    });
    vi.stubGlobal('window', {
        location: new URL('http://localhost/?view=upcoming'),
        history: { state: null },
        sessionStorage: { getItem: () => null, removeItem: () => {} },
        addEventListener: () => {},
        requestAnimationFrame: (callback: () => void) => callback(),
    });
    router.init({
        initialPage: {
            component: 'events/index',
            url: 'http://localhost/?view=upcoming',
            version: 'test-version',
            props: { ...props([event]), errors: {} },
            clearHistory: false,
            encryptHistory: false,
            rescuedProps: [],
            flash: {},
            rememberedState: {},
        },
        resolveComponent: async () => null,
        swapComponent: async () => {},
    });
    await new Promise((resolve) => setTimeout(resolve, 0));
});
afterAll(() => vi.unstubAllGlobals());

it.each(['upcoming', 'past'] as const)(
    'sends a native reset request through the real router when navigating to %s',
    async (target) => {
        const visit = vi.spyOn(router, 'visit');
        const transport = vi
            .spyOn(http.getClient(), 'request')
            .mockRejectedValue(new HttpNetworkError('offline'));
        try {
            const link = anchors(EventsIndex(props([event]))).find(
                (anchor) => anchor.href === `/?view=${target}`,
            )!;
            const activation = click();
            link.onClick!(activation);
            expect(activation.preventDefault).toHaveBeenCalledOnce();
            expect(visit).toHaveBeenCalledWith(
                expect.anything(),
                expect.objectContaining({
                    preserveScroll: true,
                    preserveState: false,
                    reset: ['events'],
                    only: ['view', 'events'],
                }),
            );
            await vi.waitFor(() => expect(transport).toHaveBeenCalledOnce());
            expect(transport.mock.calls[0][0]).toMatchObject({
                method: 'get',
                url: `http://localhost/?view=${target}`,
                headers: {
                    'X-Inertia': true,
                    'X-Inertia-Reset': 'events',
                    'X-Inertia-Partial-Component': 'events/index',
                },
            });
            expect(
                String(
                    transport.mock.calls[0][0].headers?.[
                        'X-Inertia-Partial-Data'
                    ],
                ).split(','),
            ).toEqual(expect.arrayContaining(['view', 'events']));
            await vi.waitFor(() =>
                expect(setError).toHaveBeenCalledWith(
                    'Unable to switch views. Please try the view link again.',
                ),
            );
        } finally {
            transport.mockRestore();
        }
    },
);

it.each([
    {
        status: 500,
        message: 'Unable to switch views. Please try the view link again.',
    },
    { status: 429, message: 'Too many requests; try again shortly' },
])(
    'handles a failed view request with HTTP $status through the real router',
    async ({ status, message }) => {
        const transport = vi
            .spyOn(http.getClient(), 'request')
            .mockRejectedValue(
                new HttpResponseError('failed', {
                    status,
                    data: '<private-error-body>',
                    headers: {},
                }),
            );
        try {
            const link = anchors(EventsIndex(props([event]))).find(
                (anchor) => anchor.href === '/?view=past',
            )!;
            link.onClick!(click());
            await vi.waitFor(() =>
                expect(setError).toHaveBeenCalledWith(message),
            );
            expect(setError).not.toHaveBeenCalledWith('<private-error-body>');
        } finally {
            transport.mockRestore();
        }
    },
);

it('sends native reset through the real router for a deep-page start link', async () => {
    const visit = vi.spyOn(router, 'visit');
    const transport = vi
        .spyOn(http.getClient(), 'request')
        .mockRejectedValue(new HttpNetworkError('offline'));
    try {
        const page = props([event]);
        const link = anchors(
            EventFeed({
                ...page,
                events: { ...page.events, current_page: 3 },
                navigating: false,
            }),
        )[0];
        link.onClick!(click());
        expect(visit).toHaveBeenCalledWith(
            expect.anything(),
            expect.objectContaining({
                preserveScroll: true,
                preserveState: false,
                reset: ['events'],
                only: ['view', 'events'],
            }),
        );
        await vi.waitFor(() => expect(transport).toHaveBeenCalledOnce());
        expect(transport.mock.calls[0][0]).toMatchObject({
            url: 'http://localhost/?view=upcoming',
            headers: { 'X-Inertia-Reset': 'events' },
        });
        await vi.waitFor(() =>
            expect(setError).toHaveBeenCalledWith(
                'Unable to load events. Please try the start link again.',
            ),
        );
    } finally {
        transport.mockRestore();
    }
});

it.each([
    { metaKey: true },
    { ctrlKey: true },
    { shiftKey: true },
    { altKey: true },
    { button: 1 },
    { button: 2 },
    { defaultPrevented: true },
    { currentTarget: { target: '_blank' } },
])(
    'leaves modified/default-prevented/other-target anchor activation native: %j',
    (overrides) => {
        const visit = vi.spyOn(router, 'visit');
        try {
            const page = props([event]);
            const links = [
                ...anchors(EventsIndex(page)),
                ...anchors(
                    EventFeed({
                        ...page,
                        events: { ...page.events, current_page: 3 },
                        navigating: false,
                    }),
                ),
            ].filter((anchor) => anchor.href?.startsWith('/?view='));
            for (const link of links) {
                const activation = click(
                    overrides as Partial<MouseEvent<HTMLAnchorElement>>,
                );
                link.onClick!(activation);
                expect(activation.preventDefault).not.toHaveBeenCalled();
                expect(link.href).toMatch(/^\/\?view=(upcoming|past)$/);
            }
            expect(visit).not.toHaveBeenCalled();
        } finally {
            visit.mockRestore();
        }
    },
);

it('configures native scroll and request error callbacks with a manual keyboard fallback', () => {
    scrollState.hasMore = true;
    const html = render({ upcoming: [event], past: [] });
    expect(html).toContain('Load more');
    const options = scrollOptions.mock.calls[0][0];
    expect(options).toMatchObject({
        data: 'events',
        onlyNext: true,
        preserveUrl: true,
        buffer: 300,
        manual: false,
    });
    expect(options.params.data).toBeUndefined();
    expect(options.params.onNetworkError(new Error('<secret>'))).toBe(false);
    expect(setError).toHaveBeenLastCalledWith(
        'Unable to load more events. Please try again.',
    );
    expect(
        options.params.onHttpException({ status: 500, data: '<secret>' }),
    ).toBe(false);
    expect(setError).toHaveBeenLastCalledWith(
        'Unable to load more events. Please try again.',
    );
    expect(options.params.onHttpException({ status: 429 })).toBe(false);
    expect(setError).toHaveBeenLastCalledWith(
        'Too many requests; try again shortly',
    );
    options.params.onSuccess();
    expect(setError).toHaveBeenLastCalledWith(null);
});

it('renders loading and manual retry states with existing rows', () => {
    scrollState.hasMore = true;
    scrollState.loading = true;
    let html = render({ upcoming: [event], past: [] });
    expect(html).toContain(
        '<p role="status" class="sr-only">Loading more events.</p>',
    );
    expect(html).not.toContain('data-slot="event-card-skeleton"');
    expect(html).toContain('disabled');
    scrollState.loading = false;
    localState.error = 'Too many requests; try again shortly';
    html = render({ upcoming: [event], past: [] });
    expect(html).toContain('A New Chapter');
    expect(html).toContain('role="alert"');
    expect(html).toContain('Too many requests; try again shortly');
    expect(html).toContain('Retry');
    expect(scrollOptions).toHaveBeenLastCalledWith(
        expect.objectContaining({ manual: true }),
    );
});

it('pauses automatic loading during a view visit', () => {
    const page = props([event]);
    renderToStaticMarkup(<EventFeed {...page} navigating />);
    expect(scrollOptions).toHaveBeenLastCalledWith(
        expect.objectContaining({ manual: true }),
    );
});

it.each([{ rows: [event] }, { rows: [] }])(
    'offers a deep-page restart with populated or empty results',
    ({ rows }) => {
        const page = props(rows);
        const html = renderToStaticMarkup(
            <EventFeed
                {...page}
                events={{ ...page.events, current_page: 3 }}
                navigating={false}
            />,
        );
        expect(html).toContain('Start of this view');
        expect(html).toContain('href="/?view=upcoming"');
    },
);

it('keeps a semantic date rail and compact event articles', () => {
    const html = render({ upcoming: [event], past: [] });
    expect(html).toContain('dateTime="2027-01-01"');
    expect(html).toContain('aria-labelledby="event-1-title"');
    expect(html).toContain('<h4');
    expect(html).toContain('id="event-1-title"');
});

it('shows a stable cover fallback and permits a replacement source', () => {
    const cover = EventCover({ src: '/broken.svg', title: 'Reading circle' });
    const image = cover.props.children as ReactElement<ComponentProps<'img'>>;
    image.props.onError!({} as never);
    expect(setError).toHaveBeenCalledWith('/broken.svg');
    localState.failedSource = '/broken.svg';
    const failed = renderToStaticMarkup(
        <EventCover src="/broken.svg" title="Reading circle" />,
    );
    expect(failed).toContain('Cover unavailable');
    expect(failed).not.toContain('<img');
    const replacement = renderToStaticMarkup(
        <EventCover src="/replacement.svg" title="Reading circle" />,
    );
    expect(replacement).toContain('src="/replacement.svg"');
    expect(
        renderToStaticMarkup(<EventCover src="" title="Empty" />),
    ).not.toContain('<img');
});

describe('visual calendar', () => {
    it('uses the visitor local month at a UTC year boundary', () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date('2027-01-01T00:30:00Z'));
        const html = renderToStaticMarkup(<VisualCalendar />);
        const zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const expected =
            zone === 'Pacific/Honolulu' ? 'December 2026' : 'January 2027';
        expect(html).toContain(`<caption>${expected}</caption>`);
    });

    it.each([
        { year: 2028, month: 1, caption: 'February 2028', day: '2028-02-29' },
        { year: 2027, month: 1, caption: 'February 2027', day: '2027-02-28' },
        { year: 2026, month: 3, caption: 'April 2026', day: '2026-04-30' },
        { year: 2027, month: 0, caption: 'January 2027', day: '2026-12-28' },
    ])(
        'renders the correct static month for $caption',
        ({ year, month, caption, day }) => {
            vi.useFakeTimers();
            vi.setSystemTime(new Date(year, month, 15, 12));
            const html = renderToStaticMarkup(<VisualCalendar />);
            expect(html).toContain(`<caption>${caption}</caption>`);
            expect(html).toContain(`dateTime="${day}"`);
            expect(html.match(/<td[ >]/g)).toHaveLength(42);
            expect(html.indexOf('Monday')).toBeLessThan(html.indexOf('Sunday'));
            if (year === 2027 && month === 1) {
                expect(html).not.toContain('2027-02-29');
            }
        },
    );

    it('does not depend on events, view or pagination', () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date(2028, 1, 15, 12));
        const appended = props([
            event,
            { ...event, id: 2, localStartDate: '2030-09-10' },
        ]);
        for (const page of [
            props([]),
            props([event]),
            props([], [event], 'past'),
            appended,
            { ...appended, events: { ...appended.events, current_page: 3 } },
        ]) {
            const html = renderToStaticMarkup(<EventsIndex {...page} />);
            expect(html).toContain('<caption>February 2028</caption>');
            expect(html).not.toContain('Month not shown');
        }
    });

    it('provides a labelled static table without selectable dates or event markers', () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date(2026, 10, 15, 12));
        const html = renderToStaticMarkup(<VisualCalendar />);
        expect(html).toContain('Display only');
        expect(html).toContain('Dates cannot be selected');
        expect(html).toContain('scope="col"');
        expect(html).toMatch(/dateTime="2026-10-26"/);
        expect(html).not.toMatch(
            /<button|<a |<input|<select|role="grid"|tabindex|tabIndex|aria-selected|aria-current/,
        );
    });

    it('reserves the frame until the visitor clock is available during hydration', () => {
        localState.serverClock = true;
        const html = renderToStaticMarkup(<VisualCalendar />);
        expect(html).not.toContain('<table');
        expect(html).toContain('Calendar');
        expect(html).not.toContain('Month not shown');
    });

    it('rechecks at local midnight and cleans up its timer and focus listener', () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date(2026, 11, 31, 23, 59, 59));
        renderToStaticMarkup(<VisualCalendar />);
        const subscribe = clockSubscription.mock.calls[0][0] as (
            callback: () => void,
        ) => () => void;
        const onChange = vi.fn();
        const removeListener = vi.fn();
        Object.assign(window, { removeEventListener: removeListener });
        const cleanup = subscribe(onChange);
        vi.advanceTimersByTime(1100);
        expect(onChange).toHaveBeenCalledOnce();
        expect(renderToStaticMarkup(<VisualCalendar />)).toContain(
            '<caption>January 2027</caption>',
        );
        cleanup();
        expect(removeListener).toHaveBeenCalledWith(
            'focus',
            expect.any(Function),
        );
        expect(vi.getTimerCount()).toBe(0);
    });
});

// A component-local hook driver for display timers; it does not simulate a browser or Inertia history.
function componentHarness(renderComponent: () => ReactNode) {
    componentHooks.cells = [];
    componentHooks.pending = [];
    function commitEffects() {
        for (const effect of componentHooks.pending.splice(0)) effect();
    }
    function render(commit = true) {
        componentHooks.cursor = 0;
        componentHooks.active = true;
        let output: ReactNode;
        try {
            output = renderComponent();
        } finally {
            componentHooks.active = false;
        }
        if (commit) commitEffects();
        return output;
    }
    function unmount() {
        for (const cell of componentHooks.cells) cell.cleanup?.();
        componentHooks.cells = [];
        componentHooks.pending = [];
    }
    return {
        render,
        html: () => renderToStaticMarkup(render()),
        commitEffects,
        unmount,
    };
}

function pendingView(view: 'upcoming' | 'past' = 'past') {
    const visit = vi.spyOn(router, 'visit').mockImplementation(() => {});
    const ui = componentHarness(() => EventsIndex(props([event])));
    const link = anchors(ui.render()).find(
        (anchor) => anchor.href === '/?view=' + view,
    )!;
    link.onClick!(click());
    const options = visit.mock.calls[0][1]!;
    options.onStart!({} as never);
    return { ui, options };
}

function appendIndicator() {
    const feed = EventFeed({ ...props([event]), navigating: false });
    const scroll = feed.props.children[1] as ReactElement<
        ComponentProps<typeof InfiniteScroll>
    >;
    const next = scroll.props.next;
    if (typeof next !== 'function')
        throw new Error('Expected native next slot.');
    const slot = next({
        loading: false,
        loadingPrevious: false,
        loadingNext: false,
        fetch: vi.fn(),
        hasMore: true,
        hasNext: true,
        hasPrevious: false,
        autoMode: true,
        manualMode: false,
    }) as ReactElement<{ children: ReactElement<{ loading: boolean }>[] }>;
    return slot.props.children[0].type as (props: {
        loading: boolean;
    }) => ReactNode;
}

describe('delayed event loading', () => {
    it('shows two slow-view skeletons after 200ms without removing or tracking the old rows', () => {
        vi.useFakeTimers();
        const { ui, options } = pendingView();
        expect(ui.html()).toContain(
            '<p role="status" class="sr-only">Loading past events.</p>',
        );
        vi.advanceTimersByTime(199);
        expect(ui.html()).not.toContain('data-slot="event-card-skeleton"');
        vi.advanceTimersByTime(1);
        const pending = ui.html();
        expect(pending.match(/data-slot="event-card-skeleton"/g)).toHaveLength(
            2,
        );
        expect(pending).toContain('inert=""');
        expect(pending).toContain('class="invisible"');
        expect(pending).toContain('A New Chapter');
        expect(pending.match(/<ul>[\s\S]*?<\/ul>/)?.[0]).not.toContain(
            'event-card-skeleton',
        );
        expect(scrollOptions).toHaveBeenLastCalledWith(
            expect.objectContaining({ manual: true }),
        );
        options.onFinish!({} as never);
        expect(ui.html()).not.toContain('event-card-skeleton');
        expect(ui.html()).not.toContain('inert=""');
        expect(vi.getTimerCount()).toBe(0);
    });

    it.each([0, 50, 199])(
        'does not flash skeletons for a view request completing at %dms',
        (duration) => {
            vi.useFakeTimers();
            const { ui, options } = pendingView();
            vi.advanceTimersByTime(duration);
            options.onFinish!({} as never);
            vi.advanceTimersByTime(1000);
            expect(ui.html()).not.toContain('event-card-skeleton');
            expect(ui.html()).not.toContain('Loading past events.');
            expect(vi.getTimerCount()).toBe(0);
        },
    );

    it('does not show view skeletons when reloading the selected view', () => {
        vi.useFakeTimers();
        const { ui, options } = pendingView('upcoming');
        vi.advanceTimersByTime(1000);
        expect(ui.html()).not.toContain('event-card-skeleton');
        options.onFinish!({} as never);
        expect(vi.getTimerCount()).toBe(0);
    });

    it.each([100, 250])(
        'cleans up cancellation at %dms without showing an error',
        (duration) => {
            vi.useFakeTimers();
            const { ui, options } = pendingView();
            vi.advanceTimersByTime(duration);
            options.onCancel!();
            vi.advanceTimersByTime(1000);
            const html = ui.html();
            expect(html).not.toContain('event-card-skeleton');
            expect(html).not.toContain('role="alert"');
            expect(html).toContain('A New Chapter');
            expect(vi.getTimerCount()).toBe(0);
        },
    );

    it.each([
        {
            status: 0,
            message: 'Unable to switch views. Please try the view link again.',
        },
        {
            status: 500,
            message: 'Unable to switch views. Please try the view link again.',
        },
        { status: 429, message: 'Too many requests; try again shortly' },
    ])(
        'restores existing rows and clears slow placeholders on failure $status',
        ({ status, message }) => {
            vi.useFakeTimers();
            const { ui, options } = pendingView();
            vi.advanceTimersByTime(200);
            expect(ui.html()).toContain('event-card-skeleton');
            if (status === 0) {
                expect(options.onNetworkError!({} as never)).toBe(false);
            } else {
                expect(
                    options.onHttpException!({
                        status,
                        data: '<private>',
                    } as never),
                ).toBe(false);
            }
            options.onFinish!({} as never);
            const html = ui.html();
            expect(html).toContain(message);
            expect(html).toContain('A New Chapter');
            expect(html).not.toContain('event-card-skeleton');
            expect(html).not.toContain('inert=""');
            expect(html).not.toContain('&lt;private&gt;');
            expect(vi.getTimerCount()).toBe(0);
        },
    );

    it('cancels a pending timer on unmount and mounts restored props with no stale loading state', () => {
        vi.useFakeTimers();
        const { ui } = pendingView();
        vi.advanceTimersByTime(100);
        ui.unmount();
        vi.advanceTimersByTime(1000);
        const restored = componentHarness(() =>
            EventsIndex(props([], [event], 'past')),
        );
        const html = restored.html();
        expect(html).toContain('A New Chapter');
        expect(html).toContain('All past events loaded.');
        expect(html).not.toContain('event-card-skeleton');
        expect(html).not.toContain('inert=""');
        expect(vi.getTimerCount()).toBe(0);
    });

    it('delays append skeletons and removes them synchronously before effect cleanup', () => {
        vi.useFakeTimers();
        const Indicator = appendIndicator();
        let loading = true;
        const ui = componentHarness(() => Indicator({ loading }));
        expect(ui.html()).toContain('Loading more events.');
        vi.advanceTimersByTime(199);
        expect(ui.html()).not.toContain('event-card-skeleton');
        vi.advanceTimersByTime(1);
        expect(
            ui.html().match(/data-slot="event-card-skeleton"/g),
        ).toHaveLength(1);
        loading = false;
        expect(renderToStaticMarkup(ui.render(false))).toBe('');
        ui.commitEffects();
        loading = true;
        expect(ui.html()).not.toContain('event-card-skeleton');
        vi.advanceTimersByTime(200);
        expect(ui.html()).toContain('event-card-skeleton');
        ui.unmount();
        expect(vi.getTimerCount()).toBe(0);
    });

    it.each([0, 199])(
        'does not flash a fast append finishing at %dms',
        (duration) => {
            vi.useFakeTimers();
            const Indicator = appendIndicator();
            let loading = true;
            const ui = componentHarness(() => Indicator({ loading }));
            ui.html();
            vi.advanceTimersByTime(duration);
            loading = false;
            expect(ui.html()).toBe('');
            vi.advanceTimersByTime(1000);
            expect(ui.html()).toBe('');
            expect(vi.getTimerCount()).toBe(0);
        },
    );

    it('clears an append timer when its feed unmounts', () => {
        vi.useFakeTimers();
        const Indicator = appendIndicator();
        const ui = componentHarness(() => Indicator({ loading: true }));
        ui.html();
        ui.unmount();
        expect(vi.getTimerCount()).toBe(0);
    });

    it('keeps placeholders decorative without event identities or controls', () => {
        const html = renderToStaticMarkup(<EventCardSkeleton count={2} />);
        expect(html.match(/data-slot="event-card-skeleton"/g)).toHaveLength(2);
        expect(html).toContain('aria-hidden="true"');
        expect(html).toContain('motion-reduce:animate-none');
        expect(html).not.toMatch(
            /<button|<a |<ul|data-infinite-scroll|event-\d+-title/,
        );
    });
});

it('keeps loaded rows and uses the native Retry callback exactly once after append failure', () => {
    localState.error = 'Unable to load more events. Please try again.';
    scrollState.hasMore = true;
    const page = props([event]);
    const html = renderToStaticMarkup(
        <EventFeed {...page} navigating={false} />,
    );
    expect(html).toContain('A New Chapter');
    expect(html).toContain('Retry');
    expect(scrollOptions).toHaveBeenLastCalledWith(
        expect.objectContaining({ manual: true }),
    );

    const feed = EventFeed({ ...page, navigating: false });
    const scroll = feed.props.children[1] as ReactElement<
        ComponentProps<typeof InfiniteScroll>
    >;
    const next = scroll.props.next;
    if (typeof next !== 'function')
        throw new Error('Expected native next slot.');
    const fetch = vi.fn();
    const slot = next({
        loading: false,
        loadingPrevious: false,
        loadingNext: false,
        fetch,
        hasMore: true,
        hasNext: true,
        hasPrevious: false,
        autoMode: false,
        manualMode: true,
    }) as ReactElement<{
        children: Array<ReactElement<ComponentProps<'button'>>>;
    }>;
    const retry = slot.props.children.find(
        (child) => isValidElement(child) && child.props.type === 'button',
    )!;
    expect(retry.props.onClick).toBe(fetch);
    retry.props.onClick!({} as never);
    expect(fetch).toHaveBeenCalledOnce();
    expect(page.events.data).toEqual([event]);
});

it('links the event title and cover to its slug detail without changing the listing cover', () => {
    const html = renderToStaticMarkup(<EventCard event={event} />);

    expect(
        html.match(new RegExp(`href="/events/${event.slug}"`, 'g')),
    ).toHaveLength(2);
    expect(html).toContain(`aria-label="View ${event.title}"`);
    expect(html).toContain('width="128" height="128" loading="lazy"');
});
