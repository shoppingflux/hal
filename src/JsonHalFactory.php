<?php

namespace Nocarrier;

use Nocarrier\Hal;
use RuntimeException;

class JsonHalFactory
{
    /**
     * Decode a application/hal+json document into a Nocarrier\Hal object.
     */
    public static function fromJson(Hal $hal, string $text, int $depth = 0): Hal
    {
        [$uri, $links, $embedded, $data] = self::prepareJsonData($text);
        $hal->setUri($uri)->setData($data);
        self::addJsonLinkData($hal, $links);

        if ($depth > 0) {
            self::setEmbeddedResources($hal, $embedded, $depth);
        }

        $hal->setShouldStripAttributes(false);

        return $hal;
    }

    /**
     * @return array<int, mixed>
     */
    private static function prepareJsonData(string $text): array
    {
        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('The $text parameter must be valid JSON');
        }

        $uri = $data['_links']['self']['href'] ?? '';
        unset($data['_links']['self']);

        $links = $data['_links'] ?? [];
        unset($data['_links']);

        $embedded = $data['_embedded'] ?? [];
        unset($data['_embedded']);

        return [$uri, $links, $embedded, $data];
    }

    /**
     * @param array<int, mixed> $container
     */
    private static function addJsonLinkData(Hal $hal, array $container): void
    {
        foreach ($container as $rel => $links) {
            if (! isset($links[0]) || ! is_array($links[0])) {
                $links = [$links];
            }

            foreach ($links as $link) {
                $href = $link['href'];
                unset($link['href']);
                $hal->addLink($rel, $href, $link);
            }
        }
    }

    private static function setEmbeddedResources(Hal $hal, array $embedded, int $depth): void
    {
        foreach ($embedded as $rel => $embed) {
            $isIndexed = array_values($embed) === $embed;
            $className = $hal::class;

            if (! $isIndexed) {
                $hal->setResource($rel, self::fromJson(new $className(), json_encode($embed), $depth - 1));
            } else {
                foreach ($embed as $resource) {
                    $hal->addResource($rel, self::fromJson(new $className(), json_encode($resource), $depth - 1));
                }
            }
        }
    }
}
