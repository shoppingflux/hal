<?php

namespace Nocarrier;

use Exception;
use RuntimeException;
use SimpleXMLElement;

class XmlHalFactory
{
    /**
     * Decode a application/hal+xml document into a Nocarrier\Hal object.
     *
     * @throws RuntimeException
     */
    public static function fromXml(Hal $hal, SimpleXMLElement|string $data, int $depth = 0): Hal
    {
        if (! $data instanceof SimpleXMLElement) {
            try {
                $data = new SimpleXMLElement($data);
            } catch (Exception $e) {
                throw new RuntimeException('The $data parameter must be valid XML', $e->getCode(), $e);
            }
        }

        $children = $data->children();
        $links    = clone $children->link;
        unset($children->link);

        $embedded = clone $children->resource;
        unset($children->resource);

        $hal->setUri((string) $data->attributes()->href);
        $hal->setData((array) $children);

        foreach ($links as $links) {
            if (! is_array($links)) {
                $links = [$links];
            }

            foreach ($links as $link) {
                [$rel, $href, $attributes] = self::extractKnownData($link);
                $hal->addLink($rel, $href, $attributes);
            }
        }

        if ($depth > 0) {
            foreach ($embedded as $embed) {
                [$rel, $href, $attributes] = self::extractKnownData($embed);
                $hal->addResource($rel, self::fromXml($embed, $depth - 1));
            }
        }

        $hal->setShouldStripAttributes(false);

        return $hal;
    }

    /**
     * @return array<int, mixed>
     */
    private static function extractKnownData($data): array
    {
        $attributes = (array) $data->attributes();
        $attributes = $attributes['@attributes'];
        $rel        = $attributes['rel'];
        $href       = $attributes['href'];
        unset($attributes['rel'], $attributes['href']);

        return [$rel, $href, $attributes];
    }
}
