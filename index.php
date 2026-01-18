<?php

require_once __DIR__ . '/src/Catalog.php';
require_once __DIR__ . '/src/FontSelection.php';
require_once __DIR__ . '/src/FontCollection.php';

use Lemmon\Fontpicker\Catalog;
use Lemmon\Fontpicker\FontSelection;
use Lemmon\Fontpicker\FontCollection;
use Kirby\Exception\InvalidArgumentException;

Kirby::plugin('lemmon/fontpicker', [
    'options' => [
        'weights' => null,
        'includeItalics' => true,
        'disableRemoteCatalog' => false,
        /**
         * Cache configuration.
         *
         * NOTE: Kirby maps plugin cache options from `cache.<name>` to
         * `kirby()->cache('<plugin>.<name>')` based on the plugin name.
         */
        'cache.catalog' => [
            'active' => true,
            'type' => 'file',
        ],
        'cacheTtl' => Catalog::CACHE_DEFAULT_TTL,
    ],
    'siteMethods' => [
        'fontCollection' => function (...$inputs) {
            if (empty($inputs)) {
                return new FontCollection();
            }

            return FontCollection::from($inputs);
        },
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
            'validations' => [
                'fontpicker' => function ($value) {
                    $value = trim((string) $value);

                    if ($value === '') {
                        return true;
                    }

                    $entry = Catalog::parse($value);

                    if ($entry === null) {
                        throw new InvalidArgumentException(
                            message: 'Font not found. Paste a Bunny Fonts family URL like https://fonts.bunny.net/family/roboto, or enter a family name or slug from the catalog.'
                        );
                    }

                    $family = $entry['familyName'] ?? null;

                    if (is_string($family) && $family !== '' && str_contains($family, '<')) {
                        throw new InvalidArgumentException(
                            message: 'Font entry looks unsafe. Choose a different font or refresh the catalog.'
                        );
                    }

                    return true;
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
