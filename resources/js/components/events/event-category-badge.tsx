import { Badge } from '@/components/ui/badge';
import type { EventCategory } from '@/lib/event-presentation';

export default function EventCategoryBadge({
    category,
}: {
    category?: EventCategory;
}) {
    if (!category) {
        return null;
    }

    return (
        <Badge
            variant="secondary"
            className="event-category-badge rounded-full border-0 px-2 py-0.5 text-xs leading-4 font-medium whitespace-normal"
            data-tone={category.tone}
        >
            {category.label}
        </Badge>
    );
}
