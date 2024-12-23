<?php

namespace Northrook\HTML\Element;

use JetBrains\PhpStorm\Deprecated;
use Northrook\HTML\Element;
use Support\Arr;
use function String\filterUrl;


trigger_deprecation(
        'html-element',
        '@Element::use',
        StaticElements::class,
);


#[Deprecated]
trait StaticElements
{
    public static function link( string $href, array $attributes = [] ) : Element
    {
        $attributes['href'] = filterUrl( $href );
        return new Element( 'link', $attributes );
    }

    public static function meta(
        ?string           $name = null,
        ?string           $property = null,
        null|string|array $content = null,
                       ...$attributes,
    ) : Element {
        return new Element( 'meta' );
    }

    public static function ol( mixed $content, array $attributes = [] ) : Element
    {
        return new Element( 'ol', $attributes, $content );
    }

    public static function ul( mixed $content, array $attributes = [] ) : Element
    {
        return new Element( 'ul', $attributes, $content );
    }

    public static function li( mixed $content, array $attributes = [] ) : Element
    {
        return new Element( 'li', $attributes, $content );
    }

    public static function a(
        mixed             $content,
        string            $href,
        ?string           $id = null,
        null|string|array $class = null,
        null|string|array $style = null,
        ?string           $target = null,
        ?string           $property = null,
        ?string        ...$attribute,
    ) : Element {
        return new Element(
            'a',
            static::resolveVariables( \get_defined_vars() ),
            $content,
        );
    }

    public static function button( mixed $content, array $attributes = [] ) : Element
    {
        return new Element( 'button', $attributes, $content );
    }

    /**
     * @param array $variables
     *
     * @return array
     */
    final protected static function resolveVariables( array $variables ) : array
    {
        \array_shift( $variables );
        $attributes = \array_pop( $variables ) ?? [];
        return Arr::filter( [...$variables, ...$attributes] );
    }
}
