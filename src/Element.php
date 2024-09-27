<?php

declare( strict_types = 1 );

namespace Northrook\HTML;

use Northrook\Trait\PropertyAccessor;
use Northrook\HTML\Element\{Attribute, AttributeMethods, Attributes, DefaultAttributes, Tag};
use function Northrook\arrayFilter;
use function Northrook\filterUrl;

/**
 * @property-read string    $html
 * @property-read Attribute $class
 * @property-read Attribute $style
 *
 * @method static string classes( string | array ...$classes )
 * @method static string styles( string | array ...$styles )
 *
 * @method static string meta( ?string $name = null, ?string $property = null, mixed $content )
 */
class Element extends AbstractElement
{
    use PropertyAccessor, AttributeMethods, DefaultAttributes;


    /** @var string Rendered HTML */
    protected string $html;

    protected readonly Tag     $tag;
    public readonly Attributes $attributes;

    /**
     *
     * @param string  $tag  =  [ 'div', 'body', 'html', 'li', 'dropdown', 'menu', 'modal', 'field', 'fieldset', 'legend', 'label', 'option', 'select', 'input', 'textarea', 'form', 'tooltip', 'section', 'main', 'header', 'footer', 'div', 'span', 'p', 'ul', 'a', 'img', 'button', 'i', 'strong', 'em', 'sup', 'sub', 'br', 'hr', 'h', 'h1', 'h2', 'h3', 'h4', 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' ][$any]
     * @param array   $attributes
     * @param mixed   $content
     */
    public function __construct(
            string $tag = 'div',
            array  $attributes = [],
            mixed  $content = null,
    )
    {
        $this
                ->tag( $tag )
                ->assignAttributes( $attributes )
                ->content( $content )
        ;
    }

    /**
     * @param string  $property
     *
     * @return null|Attribute|Attributes|Tag
     */
    public function __get( string $property ) : Attribute | Attributes | Tag | null
    {
        // __get is mainly used to facilitate editing attributes

        return match ( $property ) {
            'tag'            => $this->tag,
            'class', 'style' => $this->attributes->edit( $property ),
            default          => null
        };
    }

    public static function __callStatic( string $name, array $arguments )
    {
        return match ( $name ) {
            'classes' => \implode( ' ', Attribute::classes( ...$arguments ) ),
            'styles'  => \implode( '; ', Attribute::styles( ...$arguments ) ),
            'meta'    => (string) new Element( 'meta', arrayFilter( $arguments ) ),
            default   => null
        };
    }

    public static function link( string $href, array $attributes = [] ) : Element
    {
        $attributes[ 'href' ] = filterUrl( $href );
        return new Element( 'link', $attributes );
    }

    public static function a(
            mixed                 $content,
            string                $href,
            null | string         $id = null,
            null | string | array $class = null,
            null | string | array $style = null,
            null | string         $target = null,
            null | string         $property = null,
            ?string               ...$attribute
    ) : Element
    {
        return new Element(
                'a', static::resolveVariables( \get_defined_vars() ), $content,
        );
    }

    public static function button(
            mixed $content,
            array $attributes = [],
    ) : Element
    {
        return new Element(
                'button', $attributes, $content,
        );
    }

    public static function ol(
            mixed $content,
            array $attributes = [],
    ) : Element
    {
        return new Element( 'ol', $attributes, $content );
    }

    public static function ul(
            mixed $content,
            array $attributes = [],
    ) : Element
    {
        return new Element( 'ul', $attributes, $content );
    }

    public static function li(
            mixed $content,
            array $attributes = [],
    ) : Element
    {
        return new Element(
                'li', $attributes, $content,
        );
    }

    final protected static function resolveVariables( array $variables ) : array
    {
        \array_shift( $variables );
        $attributes = \array_pop( $variables );
        return arrayFilter(
                [
                        ...$variables,
                        ...$attributes,
                ],
        );
    }
}