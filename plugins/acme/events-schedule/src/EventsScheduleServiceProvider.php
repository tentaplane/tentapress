<?php

declare(strict_types=1);

namespace Acme\EventsSchedule;

use Illuminate\Support\ServiceProvider;
use TentaPress\Blocks\Registry\BlockDefinition;
use TentaPress\Blocks\Registry\BlockRegistry;

final class EventsScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! class_exists(BlockRegistry::class)) {
            return;
        }

        if (! $this->app->bound(BlockRegistry::class)) {
            return;
        }

        $registry = $this->app->make(BlockRegistry::class);

        $registry->register(new BlockDefinition(
            type: 'acme/events-schedule',
            name: 'Events Schedule',
            description: 'A simple schedule list for upcoming events.',
            version: 1,
            fields: [
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                ],
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'textarea',
                    'rows' => 3,
                ],
                [
                    'key' => 'events',
                    'label' => 'Events',
                    'type' => 'textarea',
                    'rows' => 6,
                    'help' => 'One per line: Date | Time | Title | Location.',
                ],
            ],
            defaults: [
                'title' => 'Upcoming events',
                'subtitle' => 'Join us for upcoming talks and sessions.',
                'events' => implode("\n", [
                    'Jun 24 | 9:00 AM | Opening keynote | Main Hall',
                    'Jun 24 | 1:00 PM | Product workshop | Lab 2',
                    'Jun 25 | 10:00 AM | Founder Q&A | Stage B',
                ]),
            ],
            example: [
                'props' => [
                    'title' => 'Upcoming events',
                    'subtitle' => 'Join us for upcoming talks and sessions.',
                    'events' => implode("\n", [
                        'Jun 24 | 9:00 AM | Opening keynote | Main Hall',
                        'Jun 24 | 1:00 PM | Product workshop | Lab 2',
                        'Jun 25 | 10:00 AM | Founder Q&A | Stage B',
                    ]),
                ],
            ],
            view: 'blocks.events-schedule',
        ));

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tentapress-blocks');
    }
}
