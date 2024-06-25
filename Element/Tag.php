<?php

declare( strict_types = 1 );

namespace Northrook\HTML\Element;

use Stringable, LogicException;
use Northrook\Core\Trait\PropertyAccessor;

/**
 * @property-read string  $name
 * @property-read ?string $closingTag
 * @property-read bool    $isSelfClosing
 */
final class Tag implements Stringable
{
    use PropertyAccessor;

    public const TAGS = [
        'div', 'body', 'html', 'li', 'dropdown', 'menu', 'modal', 'field', 'fieldset', 'legend', 'label', 'option',
        'select', 'input', 'textarea', 'form', 'tooltip', 'section', 'main', 'header', 'footer', 'div', 'span', 'p',
        'ul', 'a', 'img', 'button', 'i', 'strong', 'em', 'sup', 'sub', 'br', 'hr', 'h', 'h1', 'h2', 'h3', 'h4',
    ];

    private const SELF_CLOSING = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source',
        'track', 'wbr',
    ];

    /**
     * @param string  $name  = [ 'div', 'body', 'html', 'li', 'dropdown', 'menu', 'modal', 'field', 'fieldset', 'legend', 'label', 'option', 'select', 'input', 'textarea', 'form', 'tooltip', 'section', 'main', 'header', 'footer', 'div', 'span', 'p', 'ul', 'a', 'img', 'button', 'i', 'strong', 'em', 'sup', 'sub', 'br', 'hr', 'h', 'h1', 'h2', 'h3', 'h4', 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' ][$any]
     */
    public function __construct( private string $name = 'div' ) {
        $this->set( $name );
    }

    public function __get( string $property ) : string | bool | null {
        return match ( $property ) {
            'name'          => $this->name,
            'closingTag'    => in_array( $this->name, Tag::SELF_CLOSING ) ? null : "</{$this->name}>",
            'isSelfClosing' => in_array( $this->name, Tag::SELF_CLOSING ),
            default         => throw new LogicException( 'Invalid property: ' . $property ),
        };
    }

    public function __toString() : string {
        return $this->name;
    }

    /**
     * @param string  $name  = [ 'div', 'body', 'html', 'li', 'dropdown', 'menu', 'modal', 'field', 'fieldset', 'legend', 'label', 'option', 'select', 'input', 'textarea', 'form', 'tooltip', 'section', 'main', 'header', 'footer', 'div', 'span', 'p', 'ul', 'a', 'img', 'button', 'i', 'strong', 'em', 'sup', 'sub', 'br', 'hr', 'h', 'h1', 'h2', 'h3', 'h4', 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' ][$any]
     *
     * @return Tag
     */
    public function set( string $name ) : Tag {
        $this->name = strtolower( trim( $name ) );
        return $this;
    }

    /**
     * @param string  $name  = [ 'div', 'body', 'html', 'li', 'dropdown', 'menu', 'modal', 'field', 'fieldset', 'legend', 'label', 'option', 'select', 'input', 'textarea', 'form', 'tooltip', 'section', 'main', 'header', 'footer', 'div', 'span', 'p', 'ul', 'a', 'img', 'button', 'i', 'strong', 'em', 'sup', 'sub', 'br', 'hr', 'h', 'h1', 'h2', 'h3', 'h4', 'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr' ][$any]
     *
     * @return bool
     */
    public function is( string $name ) : bool {
        return $this->name === $name;
    }

    /**
     * Check if the provided tag is a valid HTML tag.
     *
     * - Only checks native HTML tags.
     *
     * @param ?string  $string
     *
     * @return bool
     */
    public static function isValidTag( ?string $string = null ) : bool {
        return in_array( strtolower( $string ), [ ... Tag::TAGS, ... Tag::SELF_CLOSING ], true );
    }
}