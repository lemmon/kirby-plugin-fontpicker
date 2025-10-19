<?php

namespace Lemmon\Fontpicker;

use Kirby\Toolkit\Remote;
use Throwable;

/**
 * Catalog loader that fetches Bunny Fonts metadata with caching and fallback.
 */
class Catalog
{
    public const CACHE_KEY = 'catalog';
    public const CACHE_DEFAULT_TTL = 10080;
    public const REMOTE_URL = 'https://fonts.bunny.net/list';

    /**
     * Cached catalog contents for the current request.
     */
    protected static ?array $catalog = null;

    /**
     * Cached lookup results keyed by normalized slug.
     */
    protected static array $entries = [];

    /**
     * Return the entire catalog array.
     */
    public static function all(): array
    {
        if (self::$catalog !== null) {
            return self::$catalog;
        }

        $catalogPath = __DIR__ . '/../data/catalog.json';
        self::$catalog = self::load($catalogPath);

        return self::$catalog;
    }

    /**
     * Find a font by slug. Returns null when not present.
     */
    public static function find(string $slug): ?array
    {
        $slug = strtolower($slug);

        if (isset(self::$entries[$slug])) {
            return self::$entries[$slug];
        }

        $catalog = self::all();
        $entry = $catalog[$slug] ?? null;

        if ($entry !== null) {
            $entry['slug'] = $slug;
            self::$entries[$slug] = $entry;
        }

        return $entry;
    }

    /**
     * Parse an end-user value and return the matching catalog entry if possible.
     */
    public static function parse(string $value): ?array
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (isset(self::$entries[$value])) {
            return self::$entries[$value];
        }

        $normalized = strtolower($value);

        if (isset(self::$entries[$normalized])) {
            return self::$entries[$normalized];
        }

        $entry = self::find($normalized);

        if ($entry !== null) {
            self::$entries[$value] = $entry;
            return $entry;
        }

        $parsed = parse_url($value);

        if ($parsed === false) {
            return null;
        }

        $host = strtolower($parsed['host'] ?? '');

        if ($host !== 'fonts.bunny.net') {
            return null;
        }

        $path = $parsed['path'] ?? '';

        if (!preg_match('#^/family/([a-z0-9-]+)$#i', $path, $matches)) {
            return null;
        }

        $slug = strtolower($matches[1]);
        $entry = self::find($slug);

        if ($entry !== null) {
            self::$entries[$value] = $entry;
        }

        return $entry;
    }

    /**
     * Load catalog data from cache, remote, or bundled fallback (in that order).
     *
     * @internal Exposed for testing; use all()/find() from callers.
     */
    protected static function load(string $path): array
    {
        $catalog = [];
        $cacheTtl = max(0, (int) \option('lemmon.fontpicker.cacheTtl', self::CACHE_DEFAULT_TTL));
        $cache = null;
        $disableRemote = \option('lemmon.fontpicker.disableRemoteCatalog', false);

        if ($cacheTtl !== 0 && !$disableRemote && function_exists('kirby')) {
            $cache = \kirby()->cache('lemmon.fontpicker.catalog');
            $cachedCatalog = $cache->get(self::CACHE_KEY);

            if (is_array($cachedCatalog) && !empty($cachedCatalog)) {
                return $cachedCatalog;
            }
        }

        if (!$disableRemote) {
            try {
                $response = Remote::get(self::REMOTE_URL, ['timeout' => 5]);

                if ($response && $response->code() === 200) {
                    $decoded = $response->json();

                    if (is_array($decoded) && !empty($decoded)) {
                        $catalog = $decoded;
                    }
                }
            } catch (Throwable $exception) {
                // Remote failures are silently ignored; fallback handles recovery.
            }
        }

        if (empty($catalog) && is_file($path)) {
            $fallback = file_get_contents($path);
            $decodedFallback = json_decode($fallback ?: '[]', true);

            if (is_array($decodedFallback) && !empty($decodedFallback)) {
                $catalog = $decodedFallback;
            }
        }

        if (!empty($catalog) && $cache !== null && $cacheTtl !== 0) {
            $cache->set(self::CACHE_KEY, $catalog, $cacheTtl);
        }

        return is_array($catalog) ? $catalog : [];
    }
}
