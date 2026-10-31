import { MapPin, UsersRound } from 'lucide-react';
import EventAttendeeStack from '@/components/events/event-attendee-stack';
import EventCategoryBadge from '@/components/events/event-category-badge';
import EventCover from '@/components/events/event-cover';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { getEventCategory } from '@/lib/event-presentation';
import type { EventListing, TicketTypeListing } from '@/types/events';

export default function EventCard({
    event,
    attendees = [],
}: {
    event: EventListing;
    attendees?: Array<{ name: string; initials: string }>;
}) {
    const calendarDate = new Date(`${event.localStartDate}T12:00:00Z`);
    const dateLabel = new Intl.DateTimeFormat('en-GB', {
        day: 'numeric',
        month: 'short',
        timeZone: 'UTC',
    }).format(calendarDate);
    const fullDateLabel = new Intl.DateTimeFormat('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(calendarDate);
    const weekday = new Intl.DateTimeFormat('en-GB', {
        weekday: 'long',
        timeZone: 'UTC',
    }).format(calendarDate);
    const startTime = new Intl.DateTimeFormat('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
        timeZone: event.timezone,
    }).format(new Date(event.startsAt));
    const startingTicket = event.ticketTypes.reduce<
        TicketTypeListing | undefined
    >(
        (lowest, ticket) =>
            !lowest || ticket.priceMinor < lowest.priceMinor ? ticket : lowest,
        undefined,
    );

    return (
        <article
            aria-labelledby={`event-${event.id}-title`}
            className="grid gap-3"
        >
            <p className="flex flex-wrap items-baseline gap-x-2 gap-y-1 text-sm">
                <time
                    dateTime={event.localStartDate}
                    aria-label={fullDateLabel}
                    className="font-medium"
                >
                    {dateLabel}
                </time>
                <span className="text-muted-foreground">{weekday}</span>
            </p>
            <Card className="event-card gap-0 rounded-2xl border-0 p-3.5 shadow-none sm:p-4">
                <div className="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-3 sm:gap-5">
                    <div className="grid min-w-0 content-start gap-1">
                        <time
                            dateTime={event.startsAt}
                            className="text-sm leading-5 text-muted-foreground"
                        >
                            {startTime}
                        </time>
                        <h4
                            id={`event-${event.id}-title`}
                            className="mb-0.5 text-lg leading-snug font-medium tracking-tight sm:text-xl"
                        >
                            {event.title}
                        </h4>
                        <p className="flex items-start gap-1.5 text-sm leading-5 text-muted-foreground">
                            <UsersRound
                                aria-hidden="true"
                                className="mt-0.5 size-3.5 shrink-0"
                            />
                            <span>By {event.organizerName}</span>
                        </p>
                        <p className="flex items-start gap-1.5 text-sm leading-5 text-muted-foreground">
                            <MapPin
                                aria-hidden="true"
                                className="mt-0.5 size-3.5 shrink-0"
                            />
                            <span>{event.venueName}</span>
                        </p>
                        <EventCategoryBadge
                            category={getEventCategory(event.slug)}
                        />
                        {(startingTicket || attendees.length > 0) && (
                            <div className="mt-2 flex flex-wrap items-center gap-2">
                                {startingTicket && (
                                    <Badge
                                        variant="secondary"
                                        className="event-price-badge rounded border-0 px-1.5 py-0.5 text-xs leading-4 font-medium whitespace-normal"
                                    >
                                        From {startingTicket.priceLabel}
                                    </Badge>
                                )}
                                <EventAttendeeStack attendees={attendees} />
                            </div>
                        )}
                    </div>
                    <EventCover
                        key={event.coverImageUrl}
                        src={event.coverImageUrl}
                        title={event.title}
                    />
                </div>
            </Card>
        </article>
    );
}
