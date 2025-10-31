<?php

require_once __DIR__ . '/src/Catalog.php';
require_once __DIR__ . '/src/FontSelection.php';

use Lemmon\Fontpicker\Catalog;
use Lemmon\Fontpicker\FontSelection;

Kirby::plugin('lemmon/fontpicker', [
    'options' => [
        'weights' => null,
        'includeItalics' => true,
        'disableRemoteCatalog' => false,
        'cache.catalog' => [
            'active' => true,
            'type' => 'file',
        ],
        'cacheTtl' => Catalog::CACHE_DEFAULT_TTL,
    ],
    'fields' => [
        'fontpicker' => [
            'extends' => 'text',
            'props' => [
                'placeholder' => function ($placeholder = 'https://fonts.bunny.net/family/roboto') {
                    return $placeholder;
                },
                'help' => function ($help = 'Paste the Bunny Fonts family URL you want to use. You can explore all fonts at (link: https://fonts.bunny.net/ target: _blank).') {
                    return $help;
                },
            ],
        ],
    ],
    'fieldMethods' => [
        'toFont' => function ($field) {
            $value = (string) $field->value();
            $entry = Catalog::parse($value);
            $defaultWeights = option('lemmon.fontpicker.weights');
            $includeItalics = option('lemmon.fontpicker.includeItalics', true);

            return new FontSelection(
                $value,
                $entry,
                is_array($defaultWeights) ? $defaultWeights : null,
                (bool) $includeItalics,
            );
        },
    ],
]);
