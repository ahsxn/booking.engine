import type { Mock } from 'vite-plus/test';
vi.mock('@/routes', () => import('../routes'));
vi.mock(
    '@/components/events/event-feed',
    () => import('../components/events/event-feed'),
);
import { isValidElement } from 'react';
import type { ComponentProps, MouseEvent, ReactNode } from 'react';
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

const { scrollState, scrollOptions, setError, localState } = vi.hoisted(() => ({
    scrollState: { loading: false, hasMore: false },
    scrollOptions: vi.fn(),
    setError: vi.fn(),
    localState: { error: null as string | null },
}));
afterEach(() => {
    scrollState.loading = false;
    scrollState.hasMore = false;
    localState.error = null;
    vi.clearAllMocks();
});

vi.mock('react', async (importOriginal) => ({
    ...(await importOriginal<typeof import('react')>()),
    useState: (initial: unknown) => [
        initial === null ? localState.error : initial,
        setError,
    ],
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
    it('renders actual event and ticket fields in the supplied group order', () => {
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
            '1 Library Street',
            'General admission',
            'Supporter admission',
            '£12.50',
            '£20.00',
            'GBP',
            'The same admission at a supporter price.',
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
        expect(html.indexOf('General admission')).toBeLessThan(
            html.indexOf('Supporter admission'),
        );
        expect(html).not.toContain('null');
    });

    it('renders server labels and UTC time attributes without browser date conversion', () => {
        const html = render({ upcoming: [event], past: [] });
        expect(html).toContain('1 January 2027, 01:30 (+02:00)');
        expect(html).toContain('1 January 2027, 03:30 (+02:00)');
        expect(html).toContain('Europe/Helsinki');
        expect(html).toContain('dateTime="2026-12-31T23:30:00.000000Z"');
        expect(html).toContain('dateTime="2027-01-01T01:30:00.000000Z"');
        const dst = render({
            upcoming: [
                {
                    ...event,
                    timezone: 'Europe/London',
                    startsAtLabel: '25 October 2026, 01:30 (+01:00)',
                    endsAtLabel: '25 October 2026, 01:30 (+00:00)',
                },
            ],
            past: [],
        });
        expect(dst).toContain('25 October 2026, 01:30 (+01:00)');
        expect(dst).toContain('25 October 2026, 01:30 (+00:00)');
    });

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

    it('shows an honest ticketless state without a sales claim', () => {
        expect(
            render({ upcoming: [{ ...event, ticketTypes: [] }], past: [] }),
        ).toContain('No ticket information is listed for this event.');
    });

    it('escapes catalogue content and does not render future-slice controls or covers', () => {
        const html = render({
            upcoming: [
                {
                    ...event,
                    title: '<script>alert(1)</script>',
                    venueName: '<b>Venue</b>',
                    ticketTypes: [
                        { ...event.ticketTypes[0], name: '<em>Ticket</em>' },
                    ],
                },
            ],
            past: [],
        });
        expect(html).toContain('&lt;script&gt;alert(1)&lt;/script&gt;');
        expect(html).toContain('&lt;b&gt;Venue&lt;/b&gt;');
        expect(html).toContain('&lt;em&gt;Ticket&lt;/em&gt;');
        expect(html).not.toMatch(
            /<script|<img|<input|<select|background-image|sold out|remaining tickets/i,
        );
        expect(html).not.toContain('/unused-cover.svg');
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
            ];
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
    expect(html).toContain('Loading more events');
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
