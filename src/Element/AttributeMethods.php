<?php

declare ( strict_types = 1 );

namespace Northrook\HTML\Element;

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
     * @param string|bool  ...$add
     *
     * @return $this
     */
    final public function class( string | bool ...$add ) : static
    {
        $this->attributes->add( 'class', $add );
        return $this;
    }

    /**
     * Add inline styles to this Element.
     *
     * - Prepend styles using the named argument `prepend: true`.
     *
     * @param string|bool  ...$add
     *
     * @return $this
     */
    final public function style( string | bool ...$add ) : static
    {
        $this->attributes->add( 'style', $add );
        return $this;
    }
}