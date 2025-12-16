<?php

return [
    'name' => 'Anas Business Bundle',
    'description' => 'Custom Business Logic for AnasArabic.com (Fixtures, Campaigns, etc.)',
    'version' => '1.0.0',
    'author' => 'AnasArabic Team',
    'routes' => [
        'main' => [],
        'public' => [],
        'api' => [],
    ],
    'menu' => [],
    'services' => [
        'command' => [
            'mautic.anas_business.command.sync' => [
                'class' => \MauticPlugin\MauticAnasBusinessBundle\Command\SyncBusinessLogicCommand::class,
                'tag' => 'console.command',
            ],
        ],
        'events' => [],
        'forms' => [],
        'helpers' => [],
        'other' => [],
        'repositories' => [],
    ],
];
