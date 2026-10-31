import type { TicketTypeListing } from '@/types/events';

export default function TicketTypeList({
    ticketTypes,
}: {
    ticketTypes: TicketTypeListing[];
}) {
    if (ticketTypes.length === 0) {
        return (
            <p className="text-sm text-muted-foreground">
                No ticket information is listed for this event.
            </p>
        );
    }

    return (
        <ul
            aria-label="Ticket information"
            className="grid gap-3 border-t pt-4"
        >
            {ticketTypes.map((ticket) => (
                <li key={ticket.id} className="grid gap-1 text-sm">
                    <p className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                        <span className="font-medium">{ticket.name}</span>
                        <span className="font-medium text-primary">
                            {ticket.priceLabel} {ticket.currency}
                        </span>
                    </p>
                    {ticket.description && (
                        <p className="leading-relaxed text-muted-foreground">
                            {ticket.description}
                        </p>
                    )}
                </li>
            ))}
        </ul>
    );
}
