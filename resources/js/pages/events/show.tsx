import { Head } from '@inertiajs/react';
import EventCover from '@/components/events/event-cover';
import EventDescription from '@/components/events/event-description';
import EventDetailHeader from '@/components/events/event-detail-header';
import EventDetailOrganizer from '@/components/events/event-detail-organizer';
import EventRegistrationPanel from '@/components/events/event-registration-panel';
import type { EventDetailPageProps } from '@/types/events';

export default function ShowEvent({ event }: EventDetailPageProps) {
    return (
        <div className="event-detail min-h-screen bg-background text-foreground">
            <Head title={event.title} />
            <main className="event-detail-layout">
                <div className="event-detail-sidebar">
                    <EventCover
                        key={event.coverImageUrl}
                        src={event.coverImageUrl}
                        title={event.title}
                        variant="detail"
                    />
                </div>
                <div className="event-detail-content">
                    <EventDetailHeader
                        event={event}
                        organizer={
                            <EventDetailOrganizer
                                name={event.organizerName}
                                slug={event.slug}
                            />
                        }
                    />
                    <EventRegistrationPanel
                        key={event.id}
                        ticketTypes={event.ticketTypes}
                        selectionOpen={event.selectionOpen}
                    />
                    <EventDescription description={event.description} />
                </div>
            </main>
        </div>
    );
}
