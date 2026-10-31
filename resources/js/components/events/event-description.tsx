export default function EventDescription({
    description,
}: {
    description: string;
}) {
    return (
        <section
            className="event-detail-about"
            aria-labelledby="event-about-heading"
        >
            <h2
                id="event-about-heading"
                className="event-detail-section-heading"
            >
                About Event
            </h2>
            <div className="event-detail-description">
                {description.split(/\n\s*\n/).map((paragraph, index) => (
                    <p key={index}>{paragraph}</p>
                ))}
            </div>
        </section>
    );
}
