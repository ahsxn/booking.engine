import { Minus, Plus } from 'lucide-react';
import { useRef } from 'react';
import { formatGbp } from '@/lib/ticket-selection';
import type { TicketTypeListing } from '@/types/events';

export default function TicketQuantityRow({
    ticket,
    quantity,
    disabled,
    incrementDisabled,
    onChange,
}: {
    ticket: TicketTypeListing;
    quantity: number;
    disabled: boolean;
    incrementDisabled: boolean;
    onChange: (change: 1 | -1) => void;
}) {
    const incrementButton = useRef<HTMLButtonElement>(null);

    function decrement() {
        if (disabled || quantity === 0) {
            return;
        }

        onChange(-1);

        if (quantity === 1) {
            requestAnimationFrame(() => incrementButton.current?.focus());
        }
    }

    return (
        <li className="event-detail-ticket-row" data-selected={quantity > 0}>
            <div className="min-w-0">
                <p className="font-medium">{ticket.name}</p>
                <p className="event-detail-ticket-price">
                    {quantity > 0
                        ? `${quantity} × ${ticket.priceLabel} = ${formatGbp(quantity * ticket.priceMinor)}`
                        : ticket.priceLabel}
                </p>
                {ticket.description && (
                    <p className="event-detail-ticket-description">
                        {ticket.description}
                    </p>
                )}
            </div>
            <div className="event-detail-quantity-controls">
                {quantity > 0 && (
                    <>
                        <button
                            type="button"
                            disabled={disabled}
                            className="event-detail-quantity-button"
                            aria-label={`Decrease ${ticket.name} quantity`}
                            aria-describedby="event-ticket-limit"
                            onClick={decrement}
                        >
                            <Minus aria-hidden="true" className="size-4" />
                        </button>
                        <span className="event-detail-quantity-count">
                            <span className="sr-only">
                                {ticket.name} quantity:{' '}
                            </span>
                            {quantity}
                        </span>
                    </>
                )}
                <button
                    ref={incrementButton}
                    type="button"
                    disabled={disabled || incrementDisabled}
                    className="event-detail-quantity-button"
                    aria-label={`Increase ${ticket.name} quantity`}
                    aria-describedby="event-ticket-limit"
                    onClick={() => {
                        if (!disabled && !incrementDisabled) {
                            onChange(1);
                        }
                    }}
                >
                    <Plus aria-hidden="true" className="size-4" />
                </button>
            </div>
        </li>
    );
}
