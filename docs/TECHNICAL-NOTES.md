# Technical Notes

## Bunny Fonts Catalog
- `Lemmon\\Fontpicker\\Catalog` loads `https://fonts.bunny.net/list` using `Kirby\Toolkit\Remote::get()` with a bundled `data/catalog.json` fallback. Results are cached via `kirby()->cache('lemmon.fontpicker.catalog')` for `option('lemmon.fontpicker.cacheTtl', 10080)` minutes (seven days by default). A TODO in `index.php` tracks enhancing cache invalidation and error reporting.
- Catalog entries include `weights`, `styles`, and an `isVariable` flag. The field methods rely on these keys to decide how to build stylesheet URLs and expose font metadata.

## Stylesheet URL Builder
- `toFontStylesheetUrl()` accepts user input like `https://fonts.bunny.net/family/roboto-mono`, validates the host/path, extracts the slug, and looks it up in the catalog.
- Static fonts enumerate every weight/style combination as tokens (for example, `400,400i,700`). The resulting URL follows `https://fonts.bunny.net/css?family=<slug>:<tokens>`.
- Variable font support is deferred until Bunny Fonts documents reliable `ital,wght@...` syntax. See the TODO near the token builder.
- When `option('lemmon.fontpicker.weights')` is an array, only matching weights are kept (falling back to all weights if the intersection is empty). Italic variants are controlled by `option('lemmon.fontpicker.includeItalic')`; italics remain enabled if no upright style exists.
- Bunny Fonts currently returns all language subsets; filtering via query parameters is not supported, so the plugin does not restrict subsets.

## Future Enhancements
- Provide manual cache refresh commands and surface errors when both remote and fallback fail.
- Allow direct font name or slug input by normalizing values before lookup.
- Extend `isValidFont` to support slug/name inputs once parsing grows beyond Bunny URLs, and surface validation errors to editors.
