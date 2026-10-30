import { Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome" />
            <main className="flex min-h-screen items-center justify-center bg-background px-6 py-16 text-foreground">
                <div className="w-full max-w-xl space-y-4">
                    <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">
                        A new way to book your next event.
                    </h1>
                    <p className="text-lg leading-relaxed text-muted-foreground">
                        We’re preparing the booking experience. Event details
                        and tickets will be available here soon.
                    </p>
                </div>
            </main>
        </>
    );
}
