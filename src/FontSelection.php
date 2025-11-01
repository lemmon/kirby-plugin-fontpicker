<?php

namespace Lemmon\Fontpicker;

/**
 * Value object that represents a font selection and exposes helpers for
 * building Bunny Fonts stylesheet URLs and CSS variable output.
 */
class FontSelection
{
    protected string $value;

    /**
     * Catalog entry for the resolved font. Null when the selection is invalid.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $entry;

    /**
     * CSS variable name configured for this selection.
     */
    protected ?string $cssVariable = null;

    /**
     * Fallback tokens (var() references or raw strings) to use when rendering CSS variables.
     *
     * @var array<int, string>
     */
    protected array $cssFallbacks = [];

    /**
     * Weight filter to apply when constructing stylesheet URLs.
     *
     * @var array<int, int>|null
     */
    protected ?array $weightFilter;

    /**
     * Whether to include italic styles when available.
     */
    protected bool $includeItalics;

    public function __construct(
        string $value,
        ?array $entry,
        ?array $defaultWeights = null,
        bool $defaultIncludeItalics = true
    ) {
        $this->value = $value;
        $this->entry = $entry;
        $this->includeItalics = $defaultIncludeItalics;
        $this->weightFilter = $this->normalizeWeightFilter($defaultWeights);
    }

    /**
     * Whether the selection resolved to a known catalog entry.
     */
    public function isValid(): bool
    {
        return $this->entry !== null && !empty($this->entry['slug']);
    }

    /**
     * Returns the Bunny Fonts slug for this selection when available.
     */
    public function getSlug(): ?string
    {
        if (!$this->isValid()) {
            return null;
        }

        return $this->entry['slug'] ?? null;
    }

    /**
     * Returns the font family name or null when the selection is invalid.
     */
    public function getFamilyName(): ?string
    {
        if (!$this->isValid()) {
            return null;
        }

        $family = $this->entry['familyName'] ?? null;

        return is_string($family) && $family !== '' ? $family : null;
    }

    /**
     * Return the Bunny Fonts stylesheet URL describing this selection.
     */
    public function toStylesheetUrl(): ?string
    {
        $descriptor = $this->toStylesheetDescriptor();

        if ($descriptor === null) {
            return null;
        }

        $familyParam = rawurlencode($descriptor['slug']) . ':' . implode(',', $descriptor['tokens']);

        return 'https://fonts.bunny.net/css?family=' . $familyParam;
    }

    /**
     * Returns the Bunny Fonts family descriptor (slug + tokens).
     *
     * @return array{slug: string, tokens: array<int, string>}|null
     */
    public function toStylesheetDescriptor(): ?array
    {
        $slug = $this->getSlug();

        if ($slug === null) {
            return null;
        }

        $tokens = $this->buildStylesheetTokens();

        if ($tokens === []) {
            return null;
        }

        return [
            'slug' => $slug,
            'tokens' => $tokens,
        ];
    }

    /**
     * Register a CSS variable that should reference this font family.
     */
    public function withCssVariable(string $variable): self
    {
        $variable = trim($variable);

        if ($variable === '') {
            return $this;
        }

        $this->cssVariable = $variable;

        return $this;
    }

    /**
     * Register fallback tokens for the configured CSS variable.
     *
     * @param array<string>|string ...$fallbacks
     */
    public function withCssFallbacks(array|string ...$fallbacks): self
    {
        if ($this->cssVariable === null) {
            return $this;
        }

        $tokens = $this->cssFallbacks;

        foreach ($fallbacks as $fallback) {
            if (is_array($fallback)) {
                foreach ($fallback as $nested) {
                    $normalizedNested = $this->normalizeCssFallback((string) $nested);

                    if ($normalizedNested !== null) {
                        $tokens[] = $normalizedNested;
                    }
                }

                continue;
            }

            $normalized = $this->normalizeCssFallback($fallback);

            if ($normalized !== null) {
                $tokens[] = $normalized;
            }
        }

        $this->cssFallbacks = array_values(array_unique($tokens));

        return $this;
    }

    /**
     * Limit the requested font weights. Accepts scalars or arrays.
     *
     * @param int|string|array<int, int|string> ...$weights
     */
    public function withWeights(...$weights): self
    {
        $flattened = [];

        foreach ($weights as $weight) {
            if (is_array($weight)) {
                foreach ($weight as $nested) {
                    $flattened[] = $nested;
                }

                continue;
            }

            $flattened[] = $weight;
        }

        $normalized = $this->normalizeWeights($flattened);
        $this->weightFilter = $normalized === [] ? null : $normalized;

        return $this;
    }

    /**
     * Override the italics preference for this selection.
     */
    public function withItalics(bool $include): self
    {
        $this->includeItalics = $include;

        return $this;
    }

    /**
     * Render CSS variables for this selection when configured.
     */
    public function renderCssVariables(): ?string
    {
        $definition = $this->getCssVariableDefinition();

        if ($definition === null) {
            return null;
        }

        $line = '    ' . $definition['variable'] . ': ' . implode(', ', $definition['values']) . ';';

        return "<style>\n:root {\n" . $line . "\n}\n</style>";
    }

    /**
     * Return the configured CSS variable definition.
     *
     * @return array{variable: string, values: array<int, string>}|null
     */
    public function getCssVariableDefinition(): ?array
    {
        if ($this->cssVariable === null) {
            return null;
        }

        $values = $this->buildCssVariableValues();

        if ($values === []) {
            return null;
        }

        return [
            'variable' => $this->cssVariable,
            'values' => $values,
        ];
    }

    /**
     * Render the Bunny Fonts stylesheet link (optionally including preconnect).
     */
    public function renderStylesheetLink(bool $preconnect = true): ?string
    {
        $url = $this->toStylesheetUrl();

        if ($url === null) {
            return null;
        }

        $parts = [];

        if ($preconnect) {
            $parts[] = '<link rel="preconnect" href="https://fonts.bunny.net">';
        }

        $parts[] = '<link rel="stylesheet" href="' . $url . '">';

        return implode("\n", $parts);
    }

    /**
     * Render combined output (stylesheet link + CSS variables).
     */
    public function render(bool $preconnect = true): ?string
    {
        $link = $this->renderStylesheetLink($preconnect);
        $variables = $this->renderCssVariables();

        if ($link === null && $variables === null) {
            return null;
        }

        $parts = array_filter([$link, $variables]);

        return implode("\n", $parts);
    }

    /**
     * Return the original user-provided value for reference.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Resolved weight tokens after applying overrides and defaults.
     *
     * @return array<int, int>
     */
    protected function resolveWeights(): array
    {
        if (!$this->isValid()) {
            return [];
        }

        $weights = $this->normalizeWeights($this->entry['weights'] ?? []);

        if ($weights === []) {
            return [];
        }

        $filter = $this->weightFilter;

        if ($filter !== null && $filter !== []) {
            $filtered = array_values(array_intersect($weights, $filter));

            if ($filtered !== []) {
                $weights = $filtered;
            }
        }

        return $weights;
    }

    /**
     * Normalize catalog weight values into sorted integers.
     *
     * @param array<int, mixed> $weights
     *
     * @return array<int, int>
     */
    protected function normalizeWeights(array $weights): array
    {
        $normalized = [];

        foreach ($weights as $weight) {
            $intWeight = (int) $weight;

            if ($intWeight > 0) {
                $normalized[$intWeight] = $intWeight;
            }
        }

        if ($normalized === []) {
            return [];
        }

        ksort($normalized, SORT_NUMERIC);

        return array_values($normalized);
    }

    /**
     * Normalize configured weight filters.
     *
     * @param array<int, mixed>|null $weights
     *
     * @return array<int, int>|null
     */
    protected function normalizeWeightFilter(?array $weights): ?array
    {
        if ($weights === null) {
            return null;
        }

        $normalized = $this->normalizeWeights($weights);

        return $normalized === [] ? null : $normalized;
    }

    /**
     * Convert a fallback token into a CSS-friendly value.
     */
    protected function normalizeCssFallback(string $token): ?string
    {
        $token = trim($token);

        if ($token === '') {
            return null;
        }

        if (str_starts_with($token, '--')) {
            return 'var(' . $token . ')';
        }

        return $token;
    }

    /**
     * Build the Bunny Fonts tokens (e.g., 400,400i,700) for this selection.
     *
     * @return array<int, string>
     */
    protected function buildStylesheetTokens(): array
    {
        $weights = $this->resolveWeights();

        if ($weights === []) {
            return [];
        }

        $styles = array_map(
            static fn ($style) => strtolower((string) $style),
            $this->entry['styles'] ?? []
        );

        $hasNormalStyle = in_array('normal', $styles, true);
        $hasItalicStyle = in_array('italic', $styles, true);
        $useItalics = $hasItalicStyle && ($this->includeItalics || !$hasNormalStyle);

        $tokens = [];

        foreach ($weights as $weight) {
            if ($hasNormalStyle || !$hasItalicStyle) {
                $tokens[] = (string) $weight;
            }

            if ($useItalics) {
                $tokens[] = $weight . 'i';
            }
        }

        return array_values(array_unique($tokens));
    }

    /**
     * Assemble the CSS variable value list (family + fallbacks).
     *
     * @return array<int, string>
     */
    protected function buildCssVariableValues(): array
    {
        if ($this->cssVariable === null) {
            return [];
        }

        $values = [];

        if ($this->isValid()) {
            $family = $this->getFamilyName();

            if ($family !== null) {
                $values[] = '"' . addcslashes($family, '"\\') . '"';
            }
        }

        foreach ($this->cssFallbacks as $fallback) {
            $values[] = $fallback;
        }

        return $values;
    }
}
