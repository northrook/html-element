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

    final public function class( string ...$add ) : static
    {
        $this->attributes->add( 'class', $add );
        return $this;
    }

    final public function style( string ...$add ) : static
    {
        $this->attributes->add( 'style', $add );
        return $this;
    }
}