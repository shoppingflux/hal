<?php

/**
 * This file is part of the Hal library
 *
 * (c) Ben Longden <ben@nocarrier.co.uk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Nocarrier
 */

namespace Nocarrier;

/**
 * HalJsonRenderer
 *
 * @uses HalRenderer
 * @package Nocarrier
 * @author Ben Longden <ben@nocarrier.co.uk>
 */
class HalJsonRenderer implements HalRenderer
{
    public function render(Hal $resource, bool $pretty, bool $encode = true): false|array|string
    {
        $options = 0;

        if (version_compare(PHP_VERSION, '5.4.0') >= 0 && $pretty) {
            $options = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
        }

        $arrayForJson = $this->arrayForJson($resource);

        if ($encode) {
            return json_encode($arrayForJson, $options);
        }

        return $arrayForJson;
    }

    /**
     * Return an array (compatible with the hal+json format) representing
     * associated links.
     *
     * @param mixed $uri
     * @param array $links
     */
    protected function linksForJson($uri, $links, $arrayLinkRels): array
    {
        $data = [];

        if (null !== $uri) {
            $data['self'] = ['href' => $uri];
        }

        foreach ($links as $rel => $links) {
            if (count($links) === 1 && $rel !== 'curies' && ! in_array($rel, $arrayLinkRels)) {
                $data[$rel] = ['href' => $links[0]->getUri()];

                foreach ($links[0]->getAttributes() as $attribute => $value) {
                    $data[$rel][$attribute] = $value;
                }
            } else {
                $data[$rel] = [];

                foreach ($links as $link) {
                    $item = ['href' => $link->getUri()];

                    foreach ($link->getAttributes() as $attribute => $value) {
                        $item[$attribute] = $value;
                    }

                    $data[$rel][] = $item;
                }
            }
        }

        return $data;
    }

    /**
     * Return an array (compatible with the hal+json format) representing
     * associated resources.
     *
     * @param mixed $resources
     * @return array
     */
    protected function resourcesForJson($resources)
    {
        if (! is_array($resources)) {
            return $this->arrayForJson($resources);
        }

        $data = [];

        foreach ($resources as $resource) {
            $res = $this->arrayForJson($resource);

            if (! empty($res)) {
                $data[] = $res;
            }
        }

        return $data;
    }

    /**
     * Remove the @ prefix from keys that denotes an attribute in XML. This
     * cannot be represented in JSON, so it's effectively ignored.
     *
     * @param array $data
     *   The array to strip @ from the keys.
     */
    protected function stripAttributeMarker(array $data): array
    {
        foreach ($data as $key => $value) {
            if (str_starts_with((string) $key, '@xml:')) {
                $data[substr((string) $key, 5)] = $value;
                unset($data[$key]);
            } elseif (str_starts_with((string) $key, '@')) {
                $data[substr((string) $key, 1)] = $value;
                unset($data[$key]);
            }

            if (is_array($value)) {
                $data[$key] = $this->stripAttributeMarker($value);
            }
        }

        return $data;
    }

    /**
     * Return an array (compatible with the hal+json format) representing the
     * complete response.
     *
     * @return array
     */
    protected function arrayForJson(Hal $resource = null)
    {
        if ($resource == null) {
            return [];
        }

        $data = $resource->getData();

        if ($resource->getShouldStripAttributes()) {
            $data = $this->stripAttributeMarker($data);
        }

        $links = $this->linksForJson($resource->getUri(), $resource->getLinks(), $resource->getArrayLinkRels());

        if (count($links)) {
            $data['_links'] = $links;
        }

        foreach ($resource->getRawResources() as $rel => $resources) {
            if (count($resources) === 1 && ! in_array($rel, $resource->getArrayResourceRels())) {
                $resources = $resources[0];
            }

            $data['_embedded'][$rel] = $this->resourcesForJson($resources);
        }

        return $data;
    }
}
