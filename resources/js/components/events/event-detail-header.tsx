import { MapPin } from 'lucide-react';
import type { ReactNode } from 'react';
import type { EventDetail } from '@/types/events';

export default function EventDetailHeader({
    event,
    organizer,
}: {
    event: EventDetail;
    organizer: ReactNode;
}) {
    const start = new Date(event.startsAt);
    const end = new Date(event.endsAt);
    const date = (value: Date) =>
        new Intl.DateTimeFormat('en-GB', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            timeZone: event.timezone,
        })
            .format(value)
            .replace(',', '');
    const time = (value: Date) =>
        new Intl.DateTimeFormat('en-GB', {
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23',
            timeZone: event.timezone,
        }).format(value);
    const offset = (value: Date) =>
        new Intl.DateTimeFormat('en-GB', {
            timeZone: event.timezone,
            timeZoneName: 'longOffset',
        })
            .formatToParts(value)
            .find((part) => part.type === 'timeZoneName')?.value;
    const sameDay = date(start) === date(end);
    const startOffset = offset(start);
    const endOffset = offset(end);

    return (
        <header className="event-detail-header">
            <h1 className="event-detail-title">{event.title}</h1>
            {organizer}
            <div className="event-detail-metadata">
                <div className="event-detail-metadata-row">
                    <div className="event-detail-date-tile" aria-hidden="true">
                        <span>
                            {new Intl.DateTimeFormat('en-GB', {
                                month: 'short',
                                timeZone: event.timezone,
                            }).format(start)}
                        </span>
                        <strong>
                            {new Intl.DateTimeFormat('en-GB', {
                                day: 'numeric',
                                timeZone: event.timezone,
                            }).format(start)}
                        </strong>
                    </div>
                    <div className="min-w-0">
                        <p className="event-detail-metadata-primary">
                            <time dateTime={event.startsAt}>{date(start)}</time>
                        </p>
                        <p className="event-detail-secondary">
                            <time dateTime={event.startsAt}>{time(start)}</time>{' '}
                            –{' '}
                            <time dateTime={event.endsAt}>
                                {!sameDay && `${date(end)}, `}
                                {time(end)}
                            </time>
                        </p>
                        <p className="event-detail-timezone">
                            {event.timezone} ({startOffset}
                            {startOffset !== endOffset && ` → ${endOffset}`})
                        </p>
                    </div>
                </div>
                <div className="event-detail-metadata-row">
                    <span
                        className="event-detail-location-tile"
                        aria-hidden="true"
                    >
                        <MapPin />
                    </span>
                    <div className="min-w-0">
                        <p
                            className="event-detail-metadata-primary truncate"
                            title={event.venueName}
                        >
                            {event.venueName}
                        </p>
                        <p className="event-detail-secondary">
                            {event.venueAddress}
                        </p>
                    </div>
                </div>
            </div>
        </header>
    );
}
