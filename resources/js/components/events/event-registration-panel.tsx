import { useState } from 'react';
import TicketQuantityRow from '@/components/events/ticket-quantity-row';
import { Button } from '@/components/ui/button';
import {
    formatGbp,
    getTicketSelectionTotals,
    MAX_TICKET_QUANTITY,
    updateTicketQuantity,
} from '@/lib/ticket-selection';
import type { TicketQuantities, TicketTypeListing } from '@/types/events';

export default function EventRegistrationPanel({
    ticketTypes,
    selectionOpen,
}: {
    ticketTypes: TicketTypeListing[];
    selectionOpen: boolean;
}) {
    const [quantities, setQuantities] = useState<TicketQuantities>({});
    const { totalQuantity, totalMinor } = getTicketSelectionTotals(
        ticketTypes,
        quantities,
    );

    function changeQuantity(ticketId: number, change: 1 | -1) {
        if (!selectionOpen) {
            return;
        }

        setQuantities((current) =>
            updateTicketQuantity(ticketTypes, current, ticketId, change),
        );
    }

    return (
        <section
            className="event-detail-registration"
            aria-labelledby="event-registration-heading"
        >
            <h2
                id="event-registration-heading"
                className="event-detail-registration-heading"
            >
                Registration
            </h2>
            <div className="event-detail-registration-body">
                <p>
                    {selectionOpen
                        ? 'Welcome! Please choose your desired ticket type:'
                        : 'Registration closed'}
                </p>
                {ticketTypes.length > 0 ? (
                    <ul
                        className="event-detail-ticket-list"
                        aria-label="Ticket types"
                    >
                        {ticketTypes.map((ticket) => (
                            <TicketQuantityRow
                                key={ticket.id}
                                ticket={ticket}
                                quantity={quantities[ticket.id] ?? 0}
                                disabled={!selectionOpen}
                                incrementDisabled={
                                    totalQuantity >= MAX_TICKET_QUANTITY
                                }
                                onChange={(change) =>
                                    changeQuantity(ticket.id, change)
                                }
                            />
                        ))}
                    </ul>
                ) : (
                    <p className="event-detail-secondary">
                        No ticket information is listed for this event.
                    </p>
                )}
                <div>
                    <p
                        className="flex flex-wrap items-baseline justify-between gap-x-3"
                        role="status"
                        aria-live="polite"
                        aria-atomic="true"
                    >
                        <span>
                            {totalQuantity}{' '}
                            {totalQuantity === 1 ? 'ticket' : 'tickets'} · Total
                        </span>
                        <strong className="font-medium">
                            {formatGbp(totalMinor)}
                        </strong>
                    </p>
                    <p
                        id="event-ticket-limit"
                        className="event-detail-booking-note"
                    >
                        Maximum {MAX_TICKET_QUANTITY} tickets per selection.
                    </p>
                    <p className="event-detail-booking-note">
                        Based on listed prices. Price and availability will be
                        checked at checkout.
                    </p>
                </div>
                <Button
                    type="button"
                    disabled
                    aria-describedby="event-booking-note"
                    className="event-detail-get-tickets w-full rounded-[10px] text-base disabled:opacity-100"
                >
                    Get Tickets
                </Button>
                <p
                    id="event-booking-note"
                    className="event-detail-booking-note"
                >
                    Booking is not available yet. No tickets are reserved.
                </p>
            </div>
        </section>
    );
}
