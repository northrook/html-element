<?php

declare(strict_types=1);

namespace Northrook\HTML;

use Northrook\HTML\Element\{Attribute, AttributeMethods, Attributes, DefaultAttributes, StaticElements, Tag};
use Interface\Printable;
use JetBrains\PhpStorm\Deprecated;
use Support\PropertyAccessor;

trigger_deprecation(
    'html-element',
    '@dev',
    Element::class,
);

/**
 * @property-read string    $tag
 * @property-read string    $html
 * @property-read Attribute $class
 * @property-read Attribute $style
 */
#[Deprecated]
class Element extends AbstractElement
{
    use PropertyAccessor, AttributeMethods, DefaultAttributes, StaticElements;

    /** @var string Rendered HTML */
    protected string $html;

    protected readonly Tag $tag;

    public readonly Attributes $attributes;

    /**
     * @param string                                                         $tag        =  [ 'div', 'body', 'html', 'li', 'dropdown', 'menu', 'modal', 'field', 'fieldset', 'legend', 'label', 'option', 'select', 'input', 'textarea', 'form', 'tooltip', 'section', 'main', 'header', 'footer', 'div', 'span', 'p', 'ul', 'a', 'img', 'button', 'i', 'strong', 'em', 'sup', 'sub', 'br', 'hr', 'h', 'h1', 'h2', 'h3', 'h4', 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' ][$any]
     * @param array<string, mixed>                                           $attributes
     * @param null|array<array-key, Element|Printable|string>|Element|string $content
     */
    public function __construct(
        string $tag = 'div',
        array  $attributes = [],
        mixed  $content = null,
    ) {
        $this
            ->tag( $tag )
            ->assignAttributes( $attributes )
            ->content( $content );
    }

    /**
     * @param string $property
     *
     * @return null|Attribute|Attributes|string
     */
    public function __get( string $property ) : Attribute|Attributes|string|null
    {
        // __get is mainly used to facilitate editing attributes

        return match ( $property ) {
            'tag' => $this->tag->name,
            'class', 'style' => $this->attributes->edit( $property ),
            default => null,
        };
    }

    /**
     * @param array<string, string>|string $classes
     *
     * @return string
     */
    public static function classes( string|array $classes ) : string
    {
        return \implode( ' ', Attribute::classes( $classes ) );
    }

    /**
     * @param array<string, string>|string $styles
     *
     * @return string
     */
    public static function styles( string|array $styles ) : string
    {
        return \implode( '; ', Attribute::styles( $styles ) );
    }
}
