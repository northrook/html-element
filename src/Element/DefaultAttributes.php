<?php

declare(strict_types=1);

namespace Northrook\HTML\Element;

use Override;
use const Support\EMPTY_STRING;

trigger_deprecation(
        'html-element',
        '@Element::use',
        DefaultAttributes::class,
);

trait DefaultAttributes
{
    protected readonly Tag $tag;

    public readonly Attributes $attributes;

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getAttributes() : array
    {
        if ( $this->tag->is( 'heading' ) ) {
            $this->class( 'heading', prepend: true );
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
