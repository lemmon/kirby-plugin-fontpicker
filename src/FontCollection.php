<?php

namespace Lemmon\Fontpicker;

/**
 * Aggregates multiple FontSelection instances into a single Bunny request.
 */
class FontCollection
{
    /**
     * @var array<int, FontSelection>
     */
    protected array $selections = [];

    public function __construct(FontSelection ...$selections)
    {
        $this->add(...$selections);
    }

    /**
     * Create a collection from the given selections or iterables.
     *
     * @param iterable<int, mixed>|FontSelection ...$items
     */
    public static function make(...$items): self
    {
        if (count($items) === 1 && is_iterable($items[0]) && !$items[0] instanceof FontSelection) {
            $items = iterator_to_array(self::flattenIterable($items[0]), false);
        }

        return self::from($items);
    }

    /**
     * Build a collection from an iterable input.
     *
     * @param iterable<int, mixed> $items
     */
    public static function from(iterable $items): self
    {
        $collection = new self();
        $collection->merge($items);

        return $collection;
    }

    /**
     * Append selections to the collection.
     */
    public function add(FontSelection ...$selections): self
    {
        foreach ($selections as $selection) {
            $this->selections[] = $selection;
        }

        return $this;
    }

    /**
     * Merge an iterable of selections (or nested iterables) into the collection.
     *
     * @param iterable<int, mixed> $items
     */
    public function merge(iterable $items): self
    {
        foreach (self::flattenIterable($items) as $item) {
            if ($item instanceof FontSelection) {
                $this->selections[] = $item;
            }
        }

        return $this;
    }

    /**
     * Whether the collection has no fonts and no CSS variables to render.
     */
    public function isEmpty(): bool
    {
        return $this->collectDescriptors() === [] && $this->collectCssVariables() === [];
    }

    /**
     * Combined Bunny Fonts URL for every valid selection.
     */
    public function toStylesheetUrl(): ?string
    {
        $families = $this->collectDescriptors();

        if ($families === []) {
            return null;
        }

        $descriptors = [];

        foreach ($families as $slug => $tokens) {
            if ($tokens === []) {
                continue;
            }

            $descriptors[] = rawurlencode($slug) . ':' . implode(',', $tokens);
        }

        if ($descriptors === []) {
            return null;
        }

        return 'https://fonts.bunny.net/css?family=' . implode('|', $descriptors);
    }

    /**
     * Render stylesheet link(s) for the combined fonts.
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
     * Render the combined stylesheet link and CSS variable output.
     */
    public function render(bool $preconnect = true): ?string
    {
        $link = $this->renderStylesheetLink($preconnect);
        $variables = $this->renderCssVariables();

        if ($link === null && $variables === null) {
            return null;
        }

        return implode("\n", array_filter([$link, $variables]));
    }

    /**
     * Render CSS variables aggregated from every selection.
     */
    public function renderCssVariables(): ?string
    {
        $definitions = $this->collectCssVariables();

        if ($definitions === []) {
            return null;
        }

        $lines = [];

        foreach ($definitions as $variable => $values) {
            if ($values === []) {
                continue;
            }

            $lines[] = '    ' . $variable . ': ' . implode(', ', $values) . ';';
        }

        if ($lines === []) {
            return null;
        }

        return "<style>\n:root {\n" . implode("\n", $lines) . "\n}\n</style>";
    }

    /**
     * @return array<int, FontSelection>
     */
    public function selections(): array
    {
        return $this->selections;
    }

    /**
     * Gather font descriptors keyed by slug with merged tokens.
     *
     * @return array<string, array<int, string>>
     */
    protected function collectDescriptors(): array
    {
        $families = [];

        foreach ($this->selections as $selection) {
            $descriptor = $selection->toStylesheetDescriptor();

            if ($descriptor === null) {
                continue;
            }

            $slug = $descriptor['slug'];
            $tokens = $descriptor['tokens'];

            if (!isset($families[$slug])) {
                $families[$slug] = $tokens;
                continue;
            }

            $families[$slug] = array_values(array_unique(array_merge($families[$slug], $tokens)));
        }

        return $families;
    }

    /**
     * Collect CSS variable definitions from each selection.
     *
     * @return array<string, array<int, string>>
     */
    protected function collectCssVariables(): array
    {
        $definitions = [];

        foreach ($this->selections as $selection) {
            $definition = $selection->getCssVariableDefinition();

            if ($definition === null) {
                continue;
            }

            $definitions[$definition['variable']] = $definition['values'];
        }

        return $definitions;
    }

    /**
     * Flatten arbitrarily nested iterables for merge/make helpers.
     *
     * @param iterable<int, mixed> $items
     *
     * @return \Generator<int, mixed>
     */
    protected static function flattenIterable(iterable $items): \Generator
    {
        foreach ($items as $item) {
            if ($item instanceof FontSelection || !is_iterable($item)) {
                yield $item;
                continue;
            }

            yield from self::flattenIterable($item);
        }
    }
}
