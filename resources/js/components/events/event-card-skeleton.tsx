import { Card } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

export default function EventCardSkeleton({ count = 1 }: { count?: number }) {
    return (
        <div aria-hidden="true" className="space-y-8">
            {Array.from({ length: count }, (_, index) => (
                <div
                    key={index}
                    data-slot="event-card-skeleton"
                    className="event-feed-item grid gap-3"
                >
                    <div className="flex h-5 items-center gap-2">
                        <Skeleton className="h-3 w-14 bg-muted motion-reduce:animate-none" />
                        <Skeleton className="h-3 w-20 bg-muted motion-reduce:animate-none" />
                    </div>
                    <Card className="event-card gap-0 rounded-2xl border-0 p-3.5 shadow-none sm:p-4">
                        <div className="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-3 sm:gap-5">
                            <div className="grid min-w-0 gap-2">
                                <Skeleton className="h-4 w-10 bg-muted motion-reduce:animate-none" />
                                <Skeleton className="h-6 w-full bg-muted motion-reduce:animate-none" />
                                <Skeleton className="h-3 w-3/4 bg-muted motion-reduce:animate-none" />
                                <Skeleton className="h-3 w-2/3 bg-muted motion-reduce:animate-none" />
                                <Skeleton className="h-5 w-20 rounded-full bg-muted motion-reduce:animate-none" />
                                <Skeleton className="mt-1 h-5 w-16 bg-muted motion-reduce:animate-none" />
                            </div>
                            <Skeleton className="aspect-square w-20 rounded-lg bg-muted motion-reduce:animate-none min-[375px]:w-24 sm:w-28 lg:w-36" />
                        </div>
                    </Card>
                </div>
            ))}
        </div>
    );
}
