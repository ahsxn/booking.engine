import { renderToStaticMarkup } from 'react-dom/server';
import { describe, expect, it, vi } from 'vite-plus/test';
import EventsIndex from '../pages/events';
import type { EventListing, EventListingPageProps } from '../types/events';

vi.mock('@inertiajs/react', () => ({ Head: () => null }));

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

function render(props: EventListingPageProps) {
    return renderToStaticMarkup(<EventsIndex {...props} />);
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
        expect(html.indexOf('Second event')).toBeLessThan(
            html.indexOf('Past event'),
        );
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
            expect(html.includes('No past events.')).toBe(pastEmpty);
            if (upcoming.length || past.length) {
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
            /<script|<img|<a\b|<button|<input|<select|background-image|sold out|remaining tickets/i,
        );
        expect(html).not.toContain('/unused-cover.svg');
    });
});
