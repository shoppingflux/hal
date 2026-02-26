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

use Stringable;

/**
 * The HalLink class
 *
 * @package Nocarrier
 * @author Ben Longden <ben@nocarrier.co.uk>
 */
class HalLink implements Stringable
{
    /**
     * The \Nocarrier\HalLink object.
     *
     * Supported attributes in Hal (specification section 5).
     *
     * @param string $uri
     *   The URI represented by this link.
     * @param array $attributes
     *   Any additional attributes.
     */
    public function __construct(
        /**
         * The URI represented by this HalLink.
         */
        protected string $uri,
        /**
         * Any attributes on this link.
         *
         * array(
         *  'templated' => 0,
         *  'type' => 'application/hal+json',
         *  'deprecation' => 1,
         *  'name' => 'latest',
         *  'profile' => 'http://.../profile/order',
         *  'title' => 'The latest order',
         *  'hreflang' => 'en'
         * )
         */
        protected array $attributes,
    ) {
    }

    /**
     * Return the URI from this link.
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Returns the attributes for this link.
     *
     * return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * The string representation of this link (the URI).
     *
     * return string
     */
    public function __toString(): string
    {
        return $this->uri;
    }
}
