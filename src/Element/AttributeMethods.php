<?php

declare(strict_types=1);

namespace Northrook\HTML\Element;

trigger_deprecation(
    'html-element',
    '@Element::use',
    AttributeMethods::class,
);

trait AttributeMethods
{
    public readonly Attributes $attributes;

    final public function id( string $set ) : static
    {
        $this->attributes->set( 'id', $set );
        return $this;
    }

    /**
     * Add classes to this Element.
     *
     * - Prepend classes using the named argument `prepend: true`.
     *
     * @param bool|string ...$add
     *
     * @return $this
     */
    final public function class( string|bool ...$add ) : static
    {
        $prepend = \array_key_exists( 'prepend', $add );
        unset( $add['prepend'] );

        $this->attributes->add( 'class', $add, $prepend );
        return $this;
    }

    /**
     * Add inline styles to this Element.
     *
     * - Prepend styles using the named argument `prepend: true`.
     *
     * @param bool|string ...$add
     *
     * @return $this
     */
    final public function style( string|bool ...$add ) : static
    {
        $prepend = \array_key_exists( 'prepend', $add );
        unset( $add['prepend'] );

        $this->attributes->add( 'style', $add, $prepend );
        return $this;
    }
}
