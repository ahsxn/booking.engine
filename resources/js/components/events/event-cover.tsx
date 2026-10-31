import { useState } from 'react';

export default function EventCover({
    src,
    title,
}: {
    src: string;
    title: string;
}) {
    const [failedSource, setFailedSource] = useState('');

    return (
        <div className="flex aspect-square w-20 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted min-[375px]:w-24 sm:w-28 lg:w-36">
            {!src || failedSource === src ? (
                <span className="p-2 text-center text-xs leading-relaxed text-muted-foreground">
                    Cover unavailable
                    <span className="sr-only"> for {title}</span>
                </span>
            ) : (
                <img
                    src={src}
                    alt=""
                    width={128}
                    height={128}
                    loading="lazy"
                    decoding="async"
                    className="size-full object-contain"
                    onError={() => setFailedSource(src)}
                />
            )}
        </div>
    );
}
