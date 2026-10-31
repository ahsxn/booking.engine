import { Link } from '@inertiajs/react';
import { BookOpen, ChevronRight } from 'lucide-react';
import EventCategoryBadge from '@/components/events/event-category-badge';
import { getEventCategory } from '@/lib/event-presentation';
import { home } from '@/routes';

export default function EventDetailOrganizer({
    name,
    slug,
}: {
    name: string;
    slug: string;
}) {
    return (
        <div className="event-detail-organizer">
            <Link href={home()} className="event-detail-organizer-link">
                <span
                    className="event-detail-organizer-mark"
                    aria-hidden="true"
                >
                    <BookOpen />
                </span>
                <span className="min-w-0">
                    <span className="event-detail-presented">Presented by</span>
                    <span className="flex items-center gap-1">
                        {name}
                        <ChevronRight
                            aria-hidden="true"
                            className="size-3.5 shrink-0"
                        />
                    </span>
                </span>
            </Link>
            <div className="event-detail-category">
                <EventCategoryBadge category={getEventCategory(slug)} />
            </div>
        </div>
    );
}
