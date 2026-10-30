import { Head } from '@inertiajs/react';
import type { EventListing, EventListingPageProps } from '@/types/events';

type EventSectionProps = {
    heading: string;
    emptyMessage: string;
    events: EventListing[];
};

function EventSection({ heading, emptyMessage, events }: EventSectionProps) {
    return (
        <section className="space-y-4">
            <h2 className="text-2xl font-semibold">{heading}</h2>
            {events.length === 0 ? (
                <p>{emptyMessage}</p>
            ) : (
                <ul className="space-y-8">
                    {events.map((event) => (
                        <li key={event.id} className="space-y-2">
                            <h3 className="text-xl font-semibold">
                                {event.title}
                            </h3>
                            <p>{event.organizerName}</p>
                            <p>
                                {event.venueName} — {event.venueAddress}
                            </p>
                            <p>
                                Starts:{' '}
                                <time dateTime={event.startsAt}>
                                    {event.startsAtLabel}
                                </time>
                                <br />
                                Ends:{' '}
                                <time dateTime={event.endsAt}>
                                    {event.endsAtLabel}
                                </time>
                                <br />
                                Time zone: {event.timezone}
                            </p>
                            {event.ticketTypes.length === 0 ? (
                                <p>
                                    No ticket information is listed for this
                                    event.
                                </p>
                            ) : (
                                <ul className="space-y-2">
                                    {event.ticketTypes.map((ticket) => (
                                        <li key={ticket.id}>
                                            <p>
                                                {ticket.name}:{' '}
                                                {ticket.priceLabel}{' '}
                                                {ticket.currency}
                                            </p>
                                            {ticket.description && (
                                                <p>{ticket.description}</p>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}

export default function EventsIndex({ upcoming, past }: EventListingPageProps) {
    return (
        <>
            <Head title="Events" />
            <main className="mx-auto min-h-screen w-full max-w-3xl space-y-8 bg-background px-6 py-12 break-words text-foreground">
                <h1 className="text-3xl font-semibold">Events</h1>
                <p>
                    This is a read-only event listing. Booking is not available
                    yet.
                </p>
                <EventSection
                    heading="Upcoming"
                    emptyMessage="No upcoming events."
                    events={upcoming}
                />
                <EventSection
                    heading="Past"
                    emptyMessage="No past events."
                    events={past}
                />
            </main>
        </>
    );
}
