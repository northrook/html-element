<?php

namespace Northrook\HTML\Element;

use Northrook\HTML\Element;

trait StaticElements
{
    public static function meta(
        ?string           $name = null,
        ?string           $property = null,
        null|string|array $content = null,
            ... $attributes
    ) : Element {

        return new Element( 'meta' );
    }

    private static function staticArguments( ... $arguments ) : array
    {
        return [];
    }
}