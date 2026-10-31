import { Avatar, AvatarFallback } from '@/components/ui/avatar';

export default function EventAttendeeStack({
    attendees,
}: {
    attendees: Array<{ name: string; initials: string }>;
}) {
    if (attendees.length === 0) {
        return null;
    }

    return (
        <ul aria-label="Attendees" className="flex -space-x-2">
            {attendees.map((attendee, index) => (
                <li key={`${attendee.name}-${index}`}>
                    <span className="sr-only">{attendee.name}</span>
                    <Avatar
                        aria-hidden="true"
                        className="size-6 border-2 border-card"
                    >
                        <AvatarFallback className="text-[0.5rem] font-medium">
                            {attendee.initials}
                        </AvatarFallback>
                    </Avatar>
                </li>
            ))}
        </ul>
    );
}
