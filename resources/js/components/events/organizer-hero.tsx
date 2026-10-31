import { BookOpen } from 'lucide-react';

export default function OrganizerHero() {
    return (
        <section aria-labelledby="organizer-name" className="pb-7 sm:pb-8">
            <div
                className="organizer-cover h-36 overflow-hidden sm:h-44 sm:rounded-xl lg:h-52"
                aria-hidden="true"
            />
            <div className="relative grid gap-4 px-4 pt-12 sm:px-0 sm:pt-14">
                <div
                    aria-hidden="true"
                    className="absolute -top-6 left-4 flex size-16 flex-col items-center justify-center gap-1 rounded-xl border-4 border-background bg-primary text-primary-foreground sm:left-6 sm:size-18"
                >
                    <BookOpen className="size-6 sm:size-7" />
                    <span className="text-xs font-semibold tracking-widest">
                        M&amp;L
                    </span>
                </div>
                <div className="grid gap-2">
                    <h1
                        id="organizer-name"
                        className="text-[1.75rem] leading-tight font-semibold tracking-tight sm:text-3xl lg:text-4xl"
                    >
                        Margin &amp; Lantern
                    </h1>
                    <p className="max-w-xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                        Reading circles, shared stories, and time well spent.
                    </p>
                </div>
            </div>
        </section>
    );
}
