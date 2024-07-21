<?php

declare( strict_types = 1 );

namespace Northrook\HTML\Element;

use Northrook\Core\Trait\PropertyAccessor;
use Northrook\HTML\Element;
use Stringable, LogicException;

/**
 * @property-read string $value
 *
 * @internal
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final readonly class Attribute implements Stringable
{
    use PropertyAccessor;

    /**
     * @param string      $attribute   // The attribute name; id, class, style, etc
     * @param Attributes  $attributes  // The parent attributes
     * @param ?Element    $element     // The parent element, if any
     */
    public function __construct(
        private string     $attribute,
        private Attributes $attributes,
        private ?Element   $element = null,
    ) {}

    /**
     *
     * @param string  $property  Only used for the `value` property
     *
     * @return ?string The value of the attribute, or null if the attribute is empty or invalid.
     */
    public function __get( string $property ) : ?string {
        return match ( $property ) {
            'value' => $this->__toString() ?: null,
            default => throw new LogicException( 'Invalid property: ' . $property ),
        };
    }

    /**
     * Add a value to the attribute.
     *
     * @param string|array  $value    The value to add
     * @param bool          $prepend  Optionally prepend classes and styles
     *
     * @return Attributes|Element
     */
    public function add( string | array $value, bool $prepend = false ) : Attributes | Element {
        $this->attributes->add( $this->attribute, $value, $prepend );
        return $this->element ?: $this->attributes;
    }

    /**
     * Set the value of the attribute.
     *
     * @param string|array  $value  The value to set, This overrides the existing value.
     *
     * @return Attributes|Element
     */
    public function set( string | array $value ) : Attributes | Element {
        $this->attributes->set( $this->attribute, $value );
        return $this->element ?: $this->attributes;
    }

    /**
     * Check if the attribute has a given value.
     *
     * @param string|array  $value
     *
     * @return bool
     */
    public function has( string | array $value ) : bool {
        return $this->attributes->has( $this->attribute, $value );

    }

    /**
     * Stringify the attribute value.
     *
     * @return string
     */
    public function __toString() : string {
        return $this->attributes->get( $this->attribute ) ?? '';
    }

}