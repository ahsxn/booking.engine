export type EventCategory = {
    label: string;
    tone: 'violet' | 'orange';
};

const eventCategories = new Map<string, EventCategory>([
    ['paper-trails', { label: 'Reading circle', tone: 'violet' }],
    ['city-in-the-margins', { label: 'Book discussion', tone: 'violet' }],
    ['lantern-pages', { label: 'Short fiction', tone: 'orange' }],
    ['new-year-new-chapter', { label: 'Reading circle', tone: 'violet' }],
]);

export function getEventCategory(slug: string): EventCategory | undefined {
    return eventCategories.get(slug);
}
