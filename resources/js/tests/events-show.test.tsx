import { renderToStaticMarkup } from 'react-dom/server';
import { afterEach, describe, expect, it, vi } from 'vite-plus/test';
import { Children, isValidElement } from 'react';
import type { ComponentProps, ReactElement, ReactNode } from 'react';
import type { EventDetail } from '../types/events';

vi.mock('@/routes', () => import('../routes'));
vi.mock(
    '@/components/events/event-cover',
    () => import('../components/events/event-cover'),
);
vi.mock(
    '@/components/events/event-description',
    () => import('../components/events/event-description'),
);
vi.mock(
    '@/components/events/event-detail-header',
    () => import('../components/events/event-detail-header'),
);
vi.mock(
    '@/components/events/event-detail-organizer',
    () => import('../components/events/event-detail-organizer'),
);
vi.mock(
    '@/components/events/event-registration-panel',
    () => import('../components/events/event-registration-panel'),
);
vi.mock(
    '@/components/events/ticket-quantity-row',
    () => import('../components/events/ticket-quantity-row'),
);
vi.mock(
    '@/components/events/event-category-badge',
    () => import('../components/events/event-category-badge'),
);
vi.mock('@/components/ui/badge', () => import('../components/ui/badge'));
vi.mock('@/components/ui/button', () => import('../components/ui/button'));
vi.mock('@/lib/event-presentation', () => import('../lib/event-presentation'));
vi.mock('@/lib/utils', () => import('../lib/utils'));
vi.mock('@/lib/ticket-selection', () => import('../lib/ticket-selection'));

const hooks = vi.hoisted(() => ({
    active: false,
    cursor: 0,
    cells: [] as unknown[],
}));
vi.mock('react', async (importOriginal) => {
    const original = await importOriginal<typeof import('react')>();
    return {
        ...original,
        useState: (initial: unknown) => {
            if (!hooks.active) return original.useState(initial);
            const index = hooks.cursor++;
            if (!(index in hooks.cells)) hooks.cells[index] = initial;
            return [
                hooks.cells[index],
                (next: unknown) => {
                    hooks.cells[index] =
                        typeof next === 'function'
                            ? next(hooks.cells[index])
                            : next;
                },
            ];
        },
        useRef: (initial: unknown) => {
            if (!hooks.active) return original.useRef(initial);
            const index = hooks.cursor++;
            return (hooks.cells[index] ??= { current: initial });
        },
    };
});

afterEach(() => {
    hooks.active = false;
    hooks.cursor = 0;
    hooks.cells = [];
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
});
vi.mock('@inertiajs/react', async (importOriginal) => ({
    ...(await importOriginal<typeof import('@inertiajs/react')>()),
    Head: () => null,
}));

import ShowEvent from '../pages/events/show';
import EventDetailHeader from '../components/events/event-detail-header';
import EventRegistrationPanel from '../components/events/event-registration-panel';
import TicketQuantityRow from '../components/events/ticket-quantity-row';
import { router } from '@inertiajs/react';

const event: EventDetail = {
    id: 1,
    slug: 'lantern-pages',
    title: 'Lantern Pages: Stories After Dusk',
    description:
        'An evening of stories.\n\nBring <script>original words</script>.',
    coverImageUrl: '/images/events/lantern-pages-photo-v1.png',
    localStartDate: '2027-01-17',
    organizerName: 'Margin & Lantern',
    venueName: 'Reading Room',
    venueAddress: '1 Library Street, Helsinki, Finland',
    timezone: 'Europe/Helsinki',
    startsAt: '2027-01-16T23:00:00.000Z',
    endsAt: '2027-01-17T01:00:00.000Z',
    startsAtLabel: '17 January 2027, 01:00 (+02:00)',
    endsAtLabel: '17 January 2027, 03:00 (+02:00)',
    selectionOpen: true,
    ticketTypes: [
        {
            id: 1,
            name: 'General admission',
            description: 'Admission to the story evening.',
            priceMinor: 1500,
            currency: 'GBP',
            priceLabel: '£15.00',
        },
        {
            id: 2,
            name: 'Supporter admission',
            description: null,
            priceMinor: 2200,
            currency: 'GBP',
            priceLabel: '£22.00',
        },
    ],
};

describe('event detail', () => {
    it('renders original data, a square cover, organizer and safely escaped paragraphs', () => {
        const html = renderToStaticMarkup(<ShowEvent event={event} />);

        expect(html).toContain(
            '<h1 class="event-detail-title">Lantern Pages: Stories After Dusk</h1>',
        );
        expect(html).toContain('width="1024" height="1024" loading="eager"');
        expect(html).toContain('Margin &amp; Lantern');
        expect(html).toContain('1 Library Street, Helsinki, Finland');
        expect(html).toContain('<p>An evening of stories.</p>');
        expect(html).toContain('&lt;script&gt;original words&lt;/script&gt;');
        expect(html).not.toContain('<script>');
        expect(html.indexOf('event-detail-title')).toBeLessThan(
            html.indexOf('event-detail-organizer'),
        );
        expect(html.indexOf('event-detail-organizer')).toBeLessThan(
            html.indexOf('event-detail-metadata'),
        );
    });

    it('shows paid ticket prices without stock claims and keeps booking disabled', () => {
        const html = renderToStaticMarkup(<ShowEvent event={event} />);

        expect(html).toContain('£15.00');
        expect(html).toContain('£22.00');
        expect(html).not.toContain('>Available<');
        expect(html).not.toContain('Sold out');
        expect(html.match(/<button[^>]*disabled=""/g)).toHaveLength(1);
        expect(html).toContain('0 tickets · Total');
        expect(html).toContain('£0.00');
        expect(html).toContain('>Get Tickets</button>');
        expect(html).toContain(
            'Booking is not available yet. No tickets are reserved.',
        );
        expect(html).not.toContain('<form');
        expect(html).not.toContain('Payment successful');
    });

    it('shows closed registration and honest empty ticket information', () => {
        const html = renderToStaticMarkup(
            <ShowEvent
                event={{ ...event, selectionOpen: false, ticketTypes: [] }}
            />,
        );

        expect(html).toContain('Registration closed');
        expect(html).toContain(
            'No ticket information is listed for this event.',
        );
        expect(html.match(/<button[^>]*disabled=""/g)).toHaveLength(1);
    });

    it('shows event-local time and timezone independently of the machine timezone', () => {
        const html = renderToStaticMarkup(
            <EventDetailHeader event={event} organizer={null} />,
        );

        expect(html).toContain('Sunday 17 January 2027');
        expect(html).toContain('>01:00</time>');
        expect(html).toContain('>03:00</time>');
        expect(html).toContain('Europe/Helsinki (GMT+02:00)');
    });

    it('includes the end date across midnight and both offsets across a DST change', () => {
        const midnight = renderToStaticMarkup(
            <EventDetailHeader
                event={{
                    ...event,
                    startsAt: '2027-01-17T21:00:00Z',
                    endsAt: '2027-01-18T01:00:00Z',
                }}
                organizer={null}
            />,
        );
        const dst = renderToStaticMarkup(
            <EventDetailHeader
                event={{
                    ...event,
                    timezone: 'Europe/London',
                    startsAt: '2026-10-25T00:30:00Z',
                    endsAt: '2026-10-25T02:30:00Z',
                }}
                organizer={null}
            />,
        );

        expect(midnight).toContain('Monday 18 January 2027, 03:00');
        expect(dst).toContain('Europe/London (GMT+01:00 → GMT+00:00)');
    });
});

function elements(node: ReactNode): ReactElement[] {
    if (!isValidElement<{ children?: ReactNode }>(node)) return [];
    return [node, ...Children.toArray(node.props.children).flatMap(elements)];
}

function panel(selectionOpen = true) {
    hooks.active = true;
    hooks.cursor = 0;
    return EventRegistrationPanel({
        ticketTypes: event.ticketTypes,
        selectionOpen,
    });
}

function rows(view: ReactNode) {
    return elements(view).filter(
        (element) => element.type === TicketQuantityRow,
    ) as ReactElement<ComponentProps<typeof TicketQuantityRow>>[];
}

function rowButtons(
    row: ReactElement<ComponentProps<typeof TicketQuantityRow>>,
) {
    hooks.cursor = 1;
    return elements(TicketQuantityRow(row.props)).filter(
        (element) => element.type === 'button',
    ) as ReactElement<ComponentProps<'button'>>[];
}

describe('interactive registration', () => {
    it('wires real row click handlers to independent quantities and reactive line/combined totals', () => {
        const network = vi.spyOn(globalThis, 'fetch');
        const visit = vi.spyOn(router, 'visit');
        const initialRows = rows(panel());
        const firstPlus = rowButtons(initialRows[0])[0];
        const secondPlus = rowButtons(initialRows[1])[0];

        firstPlus.props.onClick!({} as never);
        firstPlus.props.onClick!({} as never);
        secondPlus.props.onClick!({} as never);
        const selected = panel();
        const selectedRows = rows(selected);
        const html = renderToStaticMarkup(selected);

        expect(selectedRows.map((row) => row.props.quantity)).toEqual([2, 1]);
        expect(html).toContain('2 × £15.00 = £30.00');
        expect(html).toContain('1 × £22.00 = £22.00');
        expect(html).toContain('3 tickets · Total');
        expect(html).toContain('£52.00');
        expect(html.match(/data-selected="true"/g)).toHaveLength(2);
        expect(html).toContain(
            'role="status" aria-live="polite" aria-atomic="true"',
        );
        expect(network).not.toHaveBeenCalled();
        expect(visit).not.toHaveBeenCalled();
    });

    it('caps rapid increments across types, disables every plus and enables them after a decrement', () => {
        const firstRows = rows(panel());
        for (let index = 0; index < 6; index++) firstRows[0].props.onChange(1);
        for (let index = 0; index < 8; index++) firstRows[1].props.onChange(1);
        const fullRows = rows(panel());

        expect(fullRows.map((row) => row.props.quantity)).toEqual([6, 4]);
        expect(fullRows.every((row) => row.props.incrementDisabled)).toBe(true);
        const buttons = rowButtons(fullRows[0]);
        expect(buttons[0].props.disabled).toBe(false);
        expect(buttons[1].props.disabled).toBe(true);
        buttons[0].props.onClick!({} as never);
        const reducedRows = rows(panel());
        expect(reducedRows.map((row) => row.props.quantity)).toEqual([5, 4]);
        expect(reducedRows.every((row) => !row.props.incrementDisabled)).toBe(
            true,
        );
        expect(renderToStaticMarkup(panel())).toContain(
            'Maximum 10 tickets per selection.',
        );
    });

    it('returns focus to the persistent plus after removing the decrement at zero, including from the cap', () => {
        const pending: FrameRequestCallback[] = [];
        vi.stubGlobal(
            'requestAnimationFrame',
            (callback: FrameRequestCallback) => {
                pending.push(callback);
                return 1;
            },
        );
        const focus = vi.fn();
        hooks.active = true;
        hooks.cursor = 1;
        hooks.cells[1] = { current: { focus } };
        const change = vi.fn();
        const row = (
            <TicketQuantityRow
                ticket={event.ticketTypes[0]}
                quantity={1}
                disabled={false}
                incrementDisabled={true}
                onChange={change}
            />
        );
        const buttons = rowButtons(row);

        expect(buttons[0].props['aria-label']).toBe(
            'Decrease General admission quantity',
        );
        expect(buttons[0].props.type).toBe('button');
        expect(buttons[0].props['aria-describedby']).toBe('event-ticket-limit');
        buttons[0].props.onClick!({} as never);
        expect(change).toHaveBeenCalledWith(-1);
        expect(focus).not.toHaveBeenCalled();
        const zeroButtons = rowButtons({
            ...row,
            props: { ...row.props, quantity: 0, incrementDisabled: false },
        });
        pending.forEach((callback) => callback(0));
        expect(focus).toHaveBeenCalledOnce();
        expect(zeroButtons).toHaveLength(1);
        expect(zeroButtons[0].props['aria-label']).toBe(
            'Increase General admission quantity',
        );
        expect(zeroButtons[0].props.disabled).toBe(false);
    });

    it('blocks closed registration changes and always keeps Get Tickets disabled without a submit handler', () => {
        const closed = rows(panel(false));
        closed[0].props.onChange(1);
        expect(rows(panel(false))[0].props.quantity).toBe(0);
        expect(rowButtons(closed[0])[0].props.disabled).toBe(true);
        const buttons = elements(panel()).filter(
            (element) =>
                element.props &&
                (element.props as { children?: unknown }).children ===
                    'Get Tickets',
        ) as ReactElement<ComponentProps<'button'>>[];

        expect(buttons).toHaveLength(1);
        expect(buttons[0].props.disabled).toBe(true);
        expect(buttons[0].props.type).toBe('button');
        expect(buttons[0].props.onClick).toBeUndefined();
        expect(buttons[0].props['aria-describedby']).toBe('event-booking-note');
    });

    it('resets to an empty selection for a fresh page mount', () => {
        rows(panel())[0].props.onChange(1);
        expect(rows(panel())[0].props.quantity).toBe(1);
        hooks.cells = [];
        expect(rows(panel())[0].props.quantity).toBe(0);
    });
});
