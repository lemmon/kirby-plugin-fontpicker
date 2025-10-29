# Roadmap

- [ ] Finish `FontSelection` fluent API, including `withCssVariable()`, `withWeights()`, and `withItalics()` modifiers that no-op safely when a font is invalid.
- [ ] Introduce `$site->combineFonts()` helper that merges multiple selections into a single Bunny Fonts request and renders both stylesheet and CSS variable output.
- [ ] Ensure the combined renderer skips the Bunny link when every selection is invalid while still emitting fallback-only `<style>` tags when variables exist.
- [ ] Expose granular rendering helpers such as `toStylesheetLink()` and `toCssVariables()` alongside a primary `render()` method for full output.
- [ ] Normalize weight input to accept either scalars or arrays and document the preferred plural method names (`withWeights`, `withItalics`) to keep the fluent API consistent.
- [ ] Audit existing field shortcuts (`toFontFamilyName()`, `isValidFont()`, etc.) and decide whether to consolidate them into the new `toFont()` value object or keep them as thin conveniences.
