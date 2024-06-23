<?php

declare( strict_types = 1 );

namespace Northrook\HTML;

use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Core\Trait\PropertyAccessor;

/**
 * @property-read string  $name
 * @property-read ?string $closingTag
 * @property-read bool    $isSelfClosing
 */
final class Tag implements \Stringable
{
    use PropertyAccessor;

    public const NAMES = [
        'div', 'body', 'html', 'li', 'dropdown', 'menu', 'modal', 'field', 'fieldset', 'legend', 'label', 'option',
        'select', 'input', 'textarea', 'form', 'tooltip', 'section', 'main', 'header', 'footer', 'div', 'span', 'p',
        'ul', 'a', 'img', 'button', 'i', 'strong', 'em', 'sup', 'sub', 'br', 'hr', 'h', 'h1', 'h2', 'h3', 'h4',
    ];

    private const SELF_CLOSING = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source',
        'track', 'wbr',
    ];

    public function __construct(
        #[ExpectedValues( self::NAMES )]
        private string $name = 'div',
    ) {
        $this->set( $name );
    }

    public function __get( string $property ) {
        return match ( $property ) {
            'name'         => $this->name,
            'closingTag'   => in_array( $this->name, Tag::SELF_CLOSING ) ? null : "</{$this->name}>",
            'isSelfClosing' => in_array( $this->name, Tag::SELF_CLOSING ),
            default        => null,
        };
    }

    /**
     * @param string  $name
     *
     * @return Tag
     */
    public function set(
        #[ExpectedValues( self::NAMES )]
        string $name,
    ) : Tag {
        $this->name = strtolower( $name );
        return $this;
    }

    public function __toString() : string {
        return $this->name;
    }

    public function is(
        #[ExpectedValues( self::NAMES )]
        string $name,
    ) : bool {
        return $this->name === $name;
    }

    public static function isValidTag( ?string $string = null ) : bool {
        return in_array( strtolower( $string ), self::NAMES );
    }
}