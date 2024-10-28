<?php

namespace Northrook\HTML\Element;

use const Support\EMPTY_STRING;

trait DefaultAttributes
{
    protected readonly Tag $tag;

    public readonly Attributes $attributes;

    public function getAttributes() : array
    {
        if ( $this->tag->is( 'heading' ) ) {
            $this->attributes->set( 'class', ['heading', ...$this->attributes->get( 'class' )] );
        }

        if ( $this->tag->is( 'button' ) ) {
            $this->attributes->add( 'type', 'button' );
        }

        if ( $this->tag->is( 'input' ) ) {
            $this->attributes->add( 'type', 'text' );
        }

        if ( $this->tag->is( 'input' ) ) {
            $this->attributes->add( 'alt', EMPTY_STRING );
        }

        return $this->attributes->toArray();
    }
}
