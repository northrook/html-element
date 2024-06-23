<?php

namespace Northrook\HTML;

/**
 * @internal
 */
final readonly class Attribute implements \Stringable
{

    public function __construct(
        private string     $attribute,
        private Attributes $element,
    ) {}

    public function add( string | array $attribute ) : self {
        $this->element->add( $this->attribute, $attribute );
        return $this;
    }

    public function set( string | array $attribute ) : self {
        $this->element->set( $this->attribute, $attribute );
        return $this;
    }

    public function has( string | array $value ) : bool {
        return $this->element->has( $this->attribute, $value );

    }

    public function __toString() : string {
        return $this->element->get( $this->attribute );
    }
}