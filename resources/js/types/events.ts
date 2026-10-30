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

export type EventListingPageProps = {
    upcoming: EventListing[];
    past: EventListing[];
};
