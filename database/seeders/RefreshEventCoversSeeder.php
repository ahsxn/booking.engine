<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RefreshEventCoversSeeder extends Seeder
{
    /** @var array<string, array{legacy: string, target: string}> */
    private const array COVERS = [
        'paper-trails' => [
            'legacy' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MDAgNTAwIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzI4NGQ0MiIvPjxjaXJjbGUgY3g9IjY0MCIgY3k9IjkwIiByPSIxMzAiIGZpbGw9IiNjNTgyNDUiLz48cGF0aCBkPSJNMTYwIDE3MHExMjAtNDAgMjQwIDIwIDEyMC02MCAyNDAtMjB2MjEwcS0xMjAtNDAtMjQwIDIwLTEyMC02MC0yNDAtMjB6IiBmaWxsPSIjZjJkZmI4Ii8+PHBhdGggZD0iTTQwMCAxOTB2MjEwTTIwMCAyMjBsMTQwIDI1bS0xNDAgMzAgMTQwIDI1bTEyMC01NSAxNDAtMjVtLTE0MCA4MCAxNDAtMjUiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzI4NGQ0MiIgc3Ryb2tlLXdpZHRoPSI4Ii8+PC9zdmc+',
            'target' => '/images/events/paper-trails-photo-v1.png',
        ],
        'city-in-the-margins' => [
            'legacy' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MDAgNTAwIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzM1M2M2NCIvPjxwYXRoIGQ9Ik04MCA0MDBWMjEwaDgwVjEzMGgxMDB2MjcwbTUwIDBWOTBoMTMwdjMxMG01MCAwVjE4MGg4MFYxMTBoMTAwdjI5MCIgZmlsbD0iI2IyYTFjYiIvPjxwYXRoIGQ9Ik0xNzAgMzIwcTExMC0zNSAyMzAgMjAgMTIwLTU1IDIzMC0yMHYxMTBxLTExMC0zNS0yMzAgMjAtMTIwLTU1LTIzMC0yMHoiIGZpbGw9IiNmM2RiYTYiLz48cGF0aCBkPSJNNDAwIDM0MHYxMTAiIHN0cm9rZT0iIzM1M2M2NCIgc3Ryb2tlLXdpZHRoPSI4Ii8+PC9zdmc+',
            'target' => '/images/events/city-in-the-margins-photo-v1.png',
        ],
        'lantern-pages' => [
            'legacy' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MDAgNTAwIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iIzE3MmQ0MCIvPjxjaXJjbGUgY3g9IjQwMCIgY3k9IjIyMCIgcj0iMTcwIiBmaWxsPSIjNjU1MDM1Ii8+PHJlY3QgeD0iMzIwIiB5PSIxMzAiIHdpZHRoPSIxNjAiIGhlaWdodD0iMjIwIiByeD0iMjUiIGZpbGw9IiNmMmM1NmYiLz48cGF0aCBkPSJNMzUwIDEzMHYtMzVxNTAtNTUgMTAwIDB2MzVtLTUwIDMwdjE2ME0yMTAgMzkwcTkwLTMwIDE5MCAyMCAxMDAtNTAgMTkwLTIwIiBmaWxsPSJub25lIiBzdHJva2U9IiNmNmU2YmYiIHN0cm9rZS13aWR0aD0iMTIiLz48L3N2Zz4=',
            'target' => '/images/events/lantern-pages-photo-v1.png',
        ],
        'new-year-new-chapter' => [
            'legacy' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MDAgNTAwIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjUwMCIgZmlsbD0iI2Q5ZThlMCIvPjxjaXJjbGUgY3g9IjYxMCIgY3k9IjEyMCIgcj0iNjUiIGZpbGw9IiNkYWE0NWIiLz48cGF0aCBkPSJNMCAzNDBxMjAwLTEwMCA0MDAgMCAyMDAgMTAwIDQwMCAwdjE2MEgweiIgZmlsbD0iIzZkOTg5MCIvPjxwYXRoIGQ9Ik0xODAgMTgwcTExMC00MCAyMjAgMjAgMTEwLTYwIDIyMC0yMHYyMDBxLTExMC00MC0yMjAgMjAtMTEwLTYwLTIyMC0yMHoiIGZpbGw9IiNmZmY1ZGYiLz48cGF0aCBkPSJNNDAwIDIwMHYyMDBtLTE1MC0xNDBoOTBtMTIwIDBoOTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0iIzMxNTc1YiIgc3Ryb2tlLXdpZHRoPSI4Ii8+PC9zdmc+',
            'target' => '/images/events/new-year-new-chapter-photo-v1.png',
        ],
    ];

    public function run(): void
    {
        $covers = self::COVERS;
        ksort($covers);
        foreach ($covers as $cover) {
            $path = public_path(ltrim($cover['target'], '/'));
            $image = is_file($path) ? @getimagesize($path) : false;
            $bytes = is_file($path) ? filesize($path) : false;

            if ($image === false || $image[0] !== 1024 || $image[1] !== 1024
                || $image['mime'] !== 'image/png'
                || $bytes === false || $bytes === 0 || $bytes > 2097152) {
                throw new RuntimeException('Missing or invalid event cover: '.$cover['target']);
            }
        }

        $counts = DB::transaction(function () use ($covers): array {
            $counts = ['updated' => 0, 'alreadyNew' => 0, 'custom' => 0, 'missing' => 0];
            foreach ($covers as $slug => $cover) {
                $updated = DB::table('events')
                    ->where('slug', $slug)
                    ->where('cover_image_url', $cover['legacy'])
                    ->update(['cover_image_url' => $cover['target']]);

                if ($updated > 0) {
                    $counts['updated']++;

                    continue;
                }

                $current = DB::table('events')->where('slug', $slug)->value('cover_image_url');
                $state = $current === null ? 'missing' : ($current === $cover['target'] ? 'alreadyNew' : 'custom');
                $counts[$state]++;
            }

            return $counts;
        }, attempts: 3);

        $this->command?->info(sprintf(
            'Event covers: %d updated, %d already-new, %d custom/skipped, %d missing.',
            $counts['updated'],
            $counts['alreadyNew'],
            $counts['custom'],
            $counts['missing'],
        ));
    }
}
