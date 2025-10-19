# Font Picker for Kirby

This Kirby plugin adds a welcoming font picker to the Panel so editors can drop in a favorite web font without touching code. It focuses on simplicity, opinionated defaults, and privacy-friendly integrations like Bunny Fonts to keep typography tweaks approachable for anyone working with predefined templates or themes.

## Installation

### Composer

```bash
composer require lemmon/kirby-fontpicker
```

### Git Submodule

```bash
git submodule add https://github.com/lemmon/kirby-plugin-fontpicker.git site/plugins/fontpicker
```

### Manual

[Download the plugin](https://api.github.com/repos/lemmon/kirby-plugin-fontpicker/zipball) and extract it to `/site/plugins/fontpicker`.

## Usage

Add the field to a blueprint and configure defaults as needed:

```yaml
fields:
    headline:
        label: Headline
        type: text
    website_font:
        label: Font
        type: fontpicker
        placeholder: "https://fonts.bunny.net/family/roboto"
```

Use the stored value to inject fonts and CSS variables in your templates or snippets. A typical pattern is to inject the link tag in a snippet used by `head.php` and share variables via Kirby’s global data or site options.

```php
<?php if ($font = $page->website_font())->isValidFont(): ?>
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link rel="stylesheet" href="<?= $font->toFontStylesheetUrl() ?>">
  <style>
    :root {
      --website-font: "<?= $font->toFontFamilyName() ?>";
    }
  </style>
<?php endif; ?>
```

Reference the shared variable in your styles (fallback to theme defaults when unset):

```css
html {
    font-family: var(--website-font, var(--default-font));
}
```

## Configuration

| Option                                   | Default | Purpose                                                                                                                                                                 |
| ---------------------------------------- | ------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `lemmon.fontpicker.weights`              | `null`  | Restrict the Bunny weights emitted in the stylesheet URL. Leave `null` to request every available weight; set to values like `[400, 700]` to keep the CSS lean.         |
| `lemmon.fontpicker.cacheTtl`             | `10080` | Cache the Bunny catalog for the given number of minutes (default seven days). Set to `0` to skip caching.                                                               |
| `lemmon.fontpicker.includeItalic`        | `true`  | Control whether italic variants are requested when available. Set to `false` to emit upright styles only (fonts with no upright style still include italic for safety). |
| `lemmon.fontpicker.disableRemoteCatalog` | `false` | Skip fetching `https://fonts.bunny.net/list` and rely solely on the bundled catalog snapshot. Useful for offline or air-gapped environments.                            |

## License

MIT License. See `LICENSE` (add one if your project does not already include it) for details.

---

Questions, issues, or ideas? File them in the repository or reach out; this plugin is designed to be extended.

## Roadmap

-   [ ] Turn pasted Bunny Fonts links into ready-to-use typography tweaks.
-   [ ] Let editors enter a simple font name or slug instead of a full URL.
-   [ ] Offer variable-style font choices once Bunny Fonts makes them dependable.
-   [ ] Combine multiple font selections into a single Bunny stylesheet link for leaner page heads.
