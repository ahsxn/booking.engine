import type {
    TicketQuantities,
    TicketSelectionTotals,
    TicketTypeListing,
} from '@/types/events';

export const MAX_TICKET_QUANTITY = 10;

export function getTicketSelectionTotals(
    ticketTypes: TicketTypeListing[],
    quantities: TicketQuantities,
): TicketSelectionTotals {
    return ticketTypes.reduce(
        (totals, ticket) => {
            const quantity = quantities[ticket.id] ?? 0;

            return {
                totalQuantity: totals.totalQuantity + quantity,
                totalMinor: totals.totalMinor + quantity * ticket.priceMinor,
            };
        },
        { totalQuantity: 0, totalMinor: 0 },
    );
}

export function updateTicketQuantity(
    ticketTypes: TicketTypeListing[],
    quantities: TicketQuantities,
    ticketId: number,
    change: 1 | -1,
): TicketQuantities {
    if (!ticketTypes.some((ticket) => ticket.id === ticketId)) {
        return quantities;
    }

    const quantity = quantities[ticketId] ?? 0;
    const { totalQuantity } = getTicketSelectionTotals(ticketTypes, quantities);

    if (
        (change === -1 && quantity === 0) ||
        (change === 1 && totalQuantity >= MAX_TICKET_QUANTITY)
    ) {
        return quantities;
    }

    return { ...quantities, [ticketId]: quantity + change };
}

export function formatGbp(minor: number): string {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(minor / 100);
}
