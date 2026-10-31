import { describe, expect, it } from 'vite-plus/test';
import {
    formatGbp,
    getTicketSelectionTotals,
    updateTicketQuantity,
} from '../lib/ticket-selection';
import type { TicketQuantities, TicketTypeListing } from '../types/events';

const tickets: TicketTypeListing[] = [
    {
        id: 1,
        name: 'General admission',
        priceMinor: 1500,
        priceLabel: '£15.00',
        currency: 'GBP',
        description: null,
    },
    {
        id: 2,
        name: 'Supporter admission',
        priceMinor: 2200,
        priceLabel: '£22.00',
        currency: 'GBP',
        description: null,
    },
];

describe('local ticket selection', () => {
    it('starts with no tickets and an exact zero total', () => {
        expect(getTicketSelectionTotals(tickets, {})).toEqual({
            totalQuantity: 0,
            totalMinor: 0,
        });
        expect(formatGbp(0)).toBe('£0.00');
    });

    it('updates independent quantities without mutating the previous selection', () => {
        const initial = { 1: 2, 2: 3 };
        const incremented = updateTicketQuantity(tickets, initial, 1, 1);
        const decremented = updateTicketQuantity(tickets, incremented, 2, -1);

        expect(initial).toEqual({ 1: 2, 2: 3 });
        expect(incremented).toEqual({ 1: 3, 2: 3 });
        expect(decremented).toEqual({ 1: 3, 2: 2 });
    });

    it('returns to zero and never decrements below it', () => {
        const zero = updateTicketQuantity(tickets, { 1: 1, 2: 2 }, 1, -1);

        expect(zero).toEqual({ 1: 0, 2: 2 });
        expect(updateTicketQuantity(tickets, zero, 1, -1)).toBe(zero);
        expect(updateTicketQuantity(tickets, {}, 2, -1)).toEqual({});
    });

    it('caps all types at ten combined and frees a place after decrementing', () => {
        const full = { 1: 6, 2: 4 };

        expect(updateTicketQuantity(tickets, full, 1, 1)).toBe(full);
        expect(updateTicketQuantity(tickets, full, 2, 1)).toBe(full);
        const reduced = updateTicketQuantity(tickets, full, 1, -1);
        expect(updateTicketQuantity(tickets, reduced, 2, 1)).toEqual({
            1: 5,
            2: 5,
        });
    });

    it('guards rapid repeated updates using the latest selection', () => {
        let quantities: TicketQuantities = {};
        for (let index = 0; index < 50; index++) {
            quantities = updateTicketQuantity(
                tickets,
                quantities,
                (index % 2) + 1,
                1,
            );
        }

        expect(quantities).toEqual({ 1: 5, 2: 5 });
        expect(
            getTicketSelectionTotals(tickets, quantities).totalQuantity,
        ).toBe(10);
    });

    it('uses integer catalogue prices for mixed line totals with no fees', () => {
        const totals = getTicketSelectionTotals(tickets, { 1: 2, 2: 1 });

        expect(totals).toEqual({ totalQuantity: 3, totalMinor: 5200 });
        expect(formatGbp(totals.totalMinor)).toBe('£52.00');
    });

    it('keeps pennies exact across increment and decrement and uses supplied prices', () => {
        const pennyTickets = [
            { ...tickets[0], priceMinor: 101 },
            { ...tickets[1], priceMinor: 333 },
        ];
        const selection = { 1: 3, 2: 2 };
        const total = getTicketSelectionTotals(pennyTickets, selection);

        expect(total.totalMinor).toBe(969);
        expect(formatGbp(total.totalMinor)).toBe('£9.69');
        expect(
            getTicketSelectionTotals(
                pennyTickets,
                updateTicketQuantity(pennyTickets, selection, 2, -1),
            ).totalMinor,
        ).toBe(636);
        expect(
            getTicketSelectionTotals([{ ...tickets[0], priceMinor: 1600 }], {
                1: 2,
            }).totalMinor,
        ).toBe(3200);
    });

    it('ignores unknown types and never counts quantities from another event', () => {
        const selection = { 1: 2, 99: 10 };

        expect(updateTicketQuantity(tickets, selection, 99, 1)).toBe(selection);
        expect(getTicketSelectionTotals(tickets, selection)).toEqual({
            totalQuantity: 2,
            totalMinor: 3000,
        });
        expect(getTicketSelectionTotals([], selection)).toEqual({
            totalQuantity: 0,
            totalMinor: 0,
        });
    });
});
