import { useSyncExternalStore } from 'react';
import { Card } from '@/components/ui/card';

function localMonth(): string {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
}

function subscribeToLocalClock(onChange: () => void): () => void {
    let timer: ReturnType<typeof setTimeout>;
    const recheck = () => {
        clearTimeout(timer);
        onChange();
        schedule();
    };
    const schedule = () => {
        const now = new Date();
        const midnight = new Date(
            now.getFullYear(),
            now.getMonth(),
            now.getDate() + 1,
        );
        timer = setTimeout(recheck, midnight.getTime() - now.getTime() + 50);
    };
    schedule();
    window.addEventListener('focus', recheck);
    return () => {
        clearTimeout(timer);
        window.removeEventListener('focus', recheck);
    };
}

export default function VisualCalendar() {
    const month = useSyncExternalStore<string | null>(
        subscribeToLocalClock,
        localMonth,
        () => null,
    );
    const [year, monthNumber] = month?.split('-').map(Number) ?? [];
    const firstDay = month
        ? new Date(Date.UTC(year, monthNumber - 1, 1))
        : null;
    const mondayOffset = firstDay ? (firstDay.getUTCDay() + 6) % 7 : 0;
    const weekdays = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    return (
        <section
            aria-labelledby="calendar-heading"
            aria-describedby="calendar-note"
            className="hidden self-start lg:block"
        >
            <Card className="relative gap-0 rounded-none border-0 bg-transparent p-3 shadow-none">
                <h3 id="calendar-heading" className="sr-only">
                    Calendar
                </h3>
                <span className="absolute top-3 right-3 text-xs leading-6 text-muted-foreground">
                    Display only
                </span>
                <div className="visual-calendar min-h-68">
                    {firstDay ? (
                        <table className="w-full table-fixed text-center text-xs">
                            <caption>
                                {new Intl.DateTimeFormat('en-GB', {
                                    month: 'long',
                                    year: 'numeric',
                                    timeZone: 'UTC',
                                }).format(firstDay)}
                            </caption>
                            <thead>
                                <tr>
                                    {weekdays.map((day) => (
                                        <th key={day} scope="col">
                                            <abbr title={day}>
                                                {day.slice(0, 1)}
                                            </abbr>
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {Array.from({ length: 6 }, (_, week) => {
                                    const start = new Date(
                                        Date.UTC(
                                            year,
                                            monthNumber - 1,
                                            week * 7 - mondayOffset + 1,
                                        ),
                                    );
                                    return (
                                        <tr key={start.toISOString()}>
                                            {weekdays.map((day, index) => {
                                                const date = new Date(
                                                    Date.UTC(
                                                        year,
                                                        monthNumber - 1,
                                                        week * 7 +
                                                            index -
                                                            mondayOffset +
                                                            1,
                                                    ),
                                                );
                                                return (
                                                    <td
                                                        key={day}
                                                        className={
                                                            date.getUTCMonth() ===
                                                            monthNumber - 1
                                                                ? 'text-foreground'
                                                                : 'text-muted-foreground'
                                                        }
                                                    >
                                                        <time
                                                            dateTime={date
                                                                .toISOString()
                                                                .slice(0, 10)}
                                                        >
                                                            {date.getUTCDate()}
                                                        </time>
                                                    </td>
                                                );
                                            })}
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    ) : (
                        <p className="sr-only">Loading local calendar…</p>
                    )}
                </div>
                <p id="calendar-note" className="sr-only">
                    Dates cannot be selected; event availability is not shown.
                </p>
            </Card>
        </section>
    );
}
