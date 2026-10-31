import { useState } from 'react';

export default function EventCover({
    src,
    title,
    variant = 'listing',
}: {
    src: string;
    title: string;
    variant?: 'listing' | 'detail';
}) {
    const [failedSource, setFailedSource] = useState('');

    return (
        <div
            className={
                variant === 'detail'
                    ? 'event-detail-cover flex aspect-square w-full items-center justify-center overflow-hidden rounded-[14px] bg-muted'
                    : 'flex aspect-square w-20 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted min-[375px]:w-24 sm:w-28 lg:w-36'
            }
        >
            {!src || failedSource === src ? (
                <span className="p-2 text-center text-xs leading-relaxed text-muted-foreground">
                    Cover unavailable
                    <span className="sr-only"> for {title}</span>
                </span>
            ) : (
                <img
                    src={src}
                    alt=""
                    width={variant === 'detail' ? 1024 : 128}
                    height={variant === 'detail' ? 1024 : 128}
                    loading={variant === 'detail' ? 'eager' : 'lazy'}
                    decoding="async"
                    className="size-full object-contain"
                    onError={() => setFailedSource(src)}
                />
            )}
        </div>
    );
}
