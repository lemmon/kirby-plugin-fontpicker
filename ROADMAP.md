# Roadmap

- [x] Finish `FontSelection` fluent API, including `withCssVariable()`, `withWeights()`, and `withItalics()` modifiers that no-op safely when a font is invalid.
- [ ] Introduce `$site->combineFonts()` helper that merges multiple selections into a single Bunny Fonts request and renders both stylesheet and CSS variable output.
- [ ] Ensure the combined renderer skips the Bunny link when every selection is invalid while still emitting fallback-only `<style>` tags when variables exist.
- [ ] Expose collection-level rendering helpers that emit a single `preconnect` tag while combining multiple selections into one Bunny request.
- [x] Normalize weight input to accept either scalars or arrays and document the preferred plural method names (`withWeights`, `withItalics`) to keep the fluent API consistent.
- [x] Remove legacy field shortcuts and update documentation/examples to rely exclusively on `$field->toFont()`.
