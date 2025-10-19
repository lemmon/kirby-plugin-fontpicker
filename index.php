<?php

require_once __DIR__ . '/src/Catalog.php';

use Lemmon\Fontpicker\Catalog;

Kirby::plugin('lemmon/fontpicker', [
    'options' => [
        'weights' => null,
        'includeItalic' => true,
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
                'help' => function ($help = 'Paste the Bunny Fonts family page you want to use.') {
                    return $help;
                },
            ],
        ],
    ],
    'fieldMethods' => [
        'toFontStylesheetUrl' => function ($field) {
            $value = trim((string) $field->value());

            if (empty($value)) {
                return null;
            }

            $font = Catalog::parse($value);

            if ($font === null) {
                return null;
            }
            $slug = $font['slug'] ?? null;

            if ($slug === null) {
                return null;
            }
            $weights = $font['weights'] ?? [];
            $styles = array_map('strtolower', $font['styles'] ?? []);

            if (empty($weights)) {
                return null;
            }

            $weights = array_unique(array_map('intval', $weights));
            sort($weights, SORT_NUMERIC);

            $configuredWeights = option('lemmon.fontpicker.weights');

            if (is_array($configuredWeights)) {
                $normalizedConfigured = [];

                foreach ($configuredWeights as $configuredWeight) {
                    $configuredWeight = (int) $configuredWeight;

                    if ($configuredWeight > 0) {
                        $normalizedConfigured[] = $configuredWeight;
                    }
                }

                $normalizedConfigured = array_unique($normalizedConfigured);
                $filteredWeights = array_values(array_intersect($weights, $normalizedConfigured));

                if (!empty($filteredWeights)) {
                    $weights = $filteredWeights;
                }
            }

            $hasNormal = in_array('normal', $styles, true);
            $hasItalic = in_array('italic', $styles, true);
            $includeItalic = option('lemmon.fontpicker.includeItalic', true);
            $useItalic = $hasItalic && ($includeItalic || !$hasNormal);

            // TODO: Revisit variable font descriptors once Bunny Fonts publishes reliable ital/wght URLs.
            // Assemble the Bunny Fonts family descriptor (e.g., 400,400i,700).
            $tokens = [];

            foreach ($weights as $weight) {
                if ($hasNormal || !$hasItalic) {
                    $tokens[] = (string) $weight;
                }

                if ($useItalic) {
                    $tokens[] = $weight . 'i';
                }
            }

            if (empty($tokens)) {
                return null;
            }

            $familyParam = rawurlencode($slug) . ':' . implode(',', $tokens);
            return 'https://fonts.bunny.net/css?family=' . $familyParam;
        },
        'toFontFamilyName' => function ($field) {
            $font = Catalog::parse((string) $field->value());

            if ($font === null) {
                return null;
            }

            return $font['familyName'] ?? null;
        },
        'isValidFont' => function ($field) {
            $value = trim((string) $field->value());

            if ($value === '') {
                return false;
            }

            // Allow direct slug matches once implemented; for now rely on catalog parsing.
            return Catalog::parse($value) !== null;
        },
    ],
]);
