# Technical Notes

## Bunny Fonts Catalog
- `Lemmon\\Fontpicker\\Catalog` loads `https://fonts.bunny.net/list` using `Kirby\Toolkit\Remote::get()` with a bundled `data/catalog.json` fallback. Results are cached via `kirby()->cache('lemmon.fontpicker.catalog')` for `option('lemmon.fontpicker.cacheTtl', 10080)` minutes (seven days by default). A TODO in `index.php` tracks enhancing cache invalidation and error reporting.
- Catalog entries include `weights`, `styles`, and an `isVariable` flag. The field methods rely on these keys to decide how to build stylesheet URLs and expose font metadata.
- `Lemmon\\Fontpicker\\FontSelection` wraps a single field value and exposes helpers such as `isValid()`, `getFamilyName()`, `toStylesheetUrl()`, HTML renderers (`renderStylesheetLink(bool $preconnect = true)`, `renderCssVariables()`, `render(bool $preconnect = true)`), and fluent modifiers (`withCssVariable()`, `withCssFallbacks()`, `withWeights()`, `withItalics()`). Field methods now expose only `$field->toFont()`; legacy shortcuts were removed.
- CSS variable helpers manage a single custom property per selection: call `withCssVariable('--font-default')` and optionally `withCssFallbacks('--font-sans', 'serif')` before rendering with `renderCssVariables()`.
- `Catalog::parse()` accepts Bunny URLs, raw slugs, family names, and arbitrary strings that can be slugified via `Kirby\Toolkit\Str::slug()`.

## Stylesheet URL Builder
- `FontSelection::toStylesheetUrl()` accepts user input like `https://fonts.bunny.net/family/roboto-mono`, validates the host/path, extracts the slug, and looks it up in the catalog.
- Static fonts enumerate every weight/style combination as tokens (for example, `400,400i,700`). The resulting URL follows `https://fonts.bunny.net/css?family=<slug>:<tokens>`.
- Variable font support is deferred until Bunny Fonts documents reliable `ital,wght@...` syntax. See the TODO near the token builder.
- When `option('lemmon.fontpicker.weights')` is an array, only matching weights are kept (falling back to all weights if the intersection is empty). Italic variants are controlled by `option('lemmon.fontpicker.includeItalics')`; italics remain enabled if no upright style exists.
- Bunny Fonts currently returns all language subsets; filtering via query parameters is not supported, so the plugin does not restrict subsets.

## Future Enhancements
- Provide manual cache refresh commands and surface errors when both remote and fallback fail.
- Allow direct font name or slug input by normalizing values before lookup.
- Extend font validation to support slug/name inputs once parsing grows beyond Bunny URLs, and surface validation errors to editors.
