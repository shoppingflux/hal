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

use SimpleXMLElement;

/**
 * HalXmlRenderer
 *
 * @uses HalRenderer
 * @package Nocarrier
 * @author Ben Longden <ben@nocarrier.co.uk>
 */
class HalXmlRenderer implements HalRenderer
{
    public function render(Hal $resource, bool $pretty, bool $encode = true): false|string
    {
        $doc = new SimpleXMLElement('<resource></resource>');

        if (null !== $resource->getUri()) {
            $doc->addAttribute('href', $resource->getUri());
        }

        $this->linksForXml($doc, $resource->getLinks());
        $this->arrayToXml($resource->getData(), $doc);

        foreach ($resource->getResources() as $rel => $resources) {
            $this->resourcesForXml($doc, $rel, $resources);
        }

        $dom = dom_import_simplexml($doc);

        if ($pretty) {
            $dom->ownerDocument->preserveWhiteSpace = false;
            $dom->ownerDocument->formatOutput       = true;
        }

        return $dom->ownerDocument->saveXML();
    }

    /**
     * linksForXml
     *
     * Add links in hal+xml format to a SimpleXmlElement object.
     *
     * @return void
     */
    protected function linksForXml(SimpleXmlElement $doc, HalLinkContainer $container)
    {
        foreach ($container as $rel => $links) {
            foreach ($links as $link) {
                $element = $doc->addChild('link');
                $element->addAttribute('rel', $rel);
                $element->addAttribute('href', $link->getUri());

                foreach ($link->getAttributes() as $attribute => $value) {
                    $element->addAttribute($attribute, $value);
                }
            }
        }
    }

    /**
     * arrayToXml
     *
     * @param array $data
     * @param mixed $parent
     * @access protected
     * @return void
     */
    protected function arrayToXml($data, SimpleXmlElement $element, $parent = null)
    {
        foreach ($data as $key => $value) {
            if (is_iterable($value)) {
                if (! is_numeric($key)) {
                    if (count($value) > 0 && isset($value[0])) {
                        $this->arrayToXml($value, $element, $key);
                    } else {
                        $subnode = $element->addChild($key);
                        $this->arrayToXml($value, $subnode, $key);
                    }
                } else {
                    $subnode = $element->addChild($parent);
                    $this->arrayToXml($value, $subnode, $parent);
                }
            } elseif (! is_numeric($key)) {
                if (str_starts_with($key, '@')) {
                    $element->addAttribute(substr($key, 1), $value);
                } elseif ($key === 'value' && count($data) === 1) {
                    $element[0] = $value;
                } elseif (is_bool($value)) {
                    $element->addChild($key, (int) $value);
                } else {
                    $element->addChild($key, htmlspecialchars((string) $value, ENT_QUOTES));
                }
            } else {
                $element->addChild($parent, htmlspecialchars((string) $value, ENT_QUOTES));
            }
        }
    }

    /**
     * resourcesForXml
     *
     * Add resources in hal+xml format (identified by $rel) to a
     * SimpleXmlElement object.
     *
     * @param mixed $rel
     * @param mixed $resources
     */
    protected function resourcesForXml(SimpleXmlElement $doc, $rel, $resources)
    {
        if (! is_array($resources)) {
            $resources = [$resources];
        }

        foreach ($resources as $resource) {
            $element = $doc->addChild('resource');
            $element->addAttribute('rel', $rel);

            if ($resource) {
                if (null !== $resource->getUri()) {
                    $element->addAttribute('href', $resource->getUri());
                }

                $this->linksForXml($element, $resource->getLinks());

                foreach ($resource->getResources() as $innerRel => $innerRes) {
                    $this->resourcesForXml($element, $innerRel, $innerRes);
                }

                $this->arrayToXml($resource->getData(), $element);
            }
        }
    }
}
