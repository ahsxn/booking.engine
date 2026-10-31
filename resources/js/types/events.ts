export type TicketTypeListing = {
    id: number;
    name: string;
    description: string | null;
    priceMinor: number;
    currency: 'GBP';
    priceLabel: string;
};

export type EventListing = {
    id: number;
    slug: string;
    title: string;
    coverImageUrl: string;
    localStartDate: string;
    organizerName: string;
    venueName: string;
    venueAddress: string;
    timezone: string;
    startsAt: string;
    endsAt: string;
    startsAtLabel: string;
    endsAtLabel: string;
    ticketTypes: TicketTypeListing[];
};

export type EventView = 'upcoming' | 'past';

export type EventDetail = EventListing & {
    description: string;
    selectionOpen: boolean;
};

export type EventDetailPageProps = {
    event: EventDetail;
};

export type TicketQuantities = Partial<Record<number, number>>;

export type TicketSelectionTotals = {
    totalQuantity: number;
    totalMinor: number;
};
export type EventListingPage = {
    current_page: number;
    current_page_url: string;
    data: EventListing[];
    first_page_url: string;
    from: number | null;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
};
export type EventListingPageProps = {
    view: EventView;
    events: EventListingPage;
};
