# Repository Guidelines

## Plugin Purpose & Scope
- Provide a privacy-friendly `fontpicker` field in the Kirby Panel so editors can select or paste third-party web fonts (e.g., Bunny Fonts) without touching code.
- Automate injecting font resources and CSS variables into themes, keeping the experience opinionated and approachable for non-technical users.
- Accept end-user input such as full font family URLs (`https://fonts.bunny.net/family/roboto` today) and, in the future, plain font names (`Roboto Mono`) or slugs (`roboto-mono`), converting them into the resources needed site-side.
- Load Bunny Fonts metadata from the remote `fonts.bunny.net/list` endpoint with a local `data/catalog.json` fallback. Cache results via Kirby’s cache component (`lemmon.fontpicker.cacheTtl`) and add better error surfacing later.
- Revisit Bunny Fonts variable font support once their CSS endpoint offers reliable descriptors; static weight stacks are the safe default today.
- Configuration options currently cover weight whitelists, italics, catalog caching, and remote toggles; document additional controls in README as they appear.

## Project Structure & Module Organization
- `index.php` registers the Kirby plugin at `lemmon/fontpicker`. Extend this file with options, hooks, or fields as the plugin grows.
- `src/Catalog.php` encapsulates Bunny Fonts catalog fetching, caching, and lookup.
- `data/catalog.json` stores the bundled Bunny Fonts snapshot used as a fallback when remote fetches fail or are disabled.
- Create additional PHP modules inside `src/` (create the folder as needed). Keep blueprints and snippets under `blueprints/` and `snippets/` to mirror Kirby’s conventions.
- Store documentation assets in `docs/` and sample site data in `site/` when demonstrating plugin behavior.
- Keep AGENTS.md focused on contributor process; capture deeper implementation details in `docs/TECHNICAL-NOTES.md`.

## Build, Test, and Development Commands
- `kirby serve` — launches a local Kirby instance for manual verification when developing features. Run from a parent Kirby install that includes this plugin.
- `composer dump-autoload` — refreshes autoload metadata if you introduce namespaced classes.
- `npm run lint` — reserve this command once linting tooling is added (see Coding Style). Configure scripts in `package.json` when front-end assets are introduced.

## Coding Style & Naming Conventions
- Follow PSR-12 for PHP: four-space indentation, brace on the next line for classes and methods, and meaningful namespaces (e.g., `Lemmon\Fontpicker`).
- Name Kirby blueprints and snippets using lowercase single-word identifiers (`fontpicker.yml`, `fontpicker.php`) to align with the plugin’s `fontpicker` namespace.
- Stick to ASCII punctuation in code, docs, and comments (prefer `--` over an em dash) so diffs stay predictable.
- Document non-trivial helpers with concise docblocks. Prefer descriptive method names such as `resolveFontOptions`.
- Keep named parameters succinct (`$preconnect` over `$includePreconnect`) to make chained method calls readable.
- Reserve emojis for rare emphasis; moderate use is fine, but avoid emoji-driven lists.
- Use GitHub-style unchecked checkboxes (`- [ ]`) when documenting roadmap items to keep documentation consistent.

## Testing Guidelines
- Add PHPUnit under `tests/` when functionality emerges; start with configuration in `phpunit.xml.dist`.
- Name test classes after the class under test (`FontpickerFieldTest`). Execute locally with `vendor/bin/phpunit`.
- Maintain manual regression notes in `docs/testing.md` until automated coverage is available.

## Commit & Pull Request Guidelines
- Follow the Conventional Commits spec (`fix:`, `refactor:`, `docs:`) and keep messages in the imperative mood.
- Ensure each commit addresses a single concern; couple tests with implementation, but leave unrelated formatting for a separate change.
- Reference related issues in commit bodies using `Refs #123` when applicable.
- PRs must summarize intent, list functional changes, and include screenshots or GIFs showing updated Kirby panel experiences when UI is affected.
