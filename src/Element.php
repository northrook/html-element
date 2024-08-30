<?php

declare( strict_types = 1 );

namespace Northrook\HTML;

use Northrook\Trait\PropertyAccessor;
use Northrook\HTML\Element\{Attribute, AttributeMethods, Attributes, DefaultAttributes, Tag};


/**
 * @property-read string    $html
 * @property-read Attribute $class
 * @property-read Attribute $style
 *
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
}