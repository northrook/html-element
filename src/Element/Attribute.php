<?php

declare( strict_types = 1 );

namespace Northrook\HTML\Element;

use Northrook\HTML\AbstractElement;
use Northrook\Logger\Log;
use Northrook\Trait\PropertyAccessor;
use Northrook\HTML\Element;
use Stringable, LogicException;
use function Northrook\normalizeKey;


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
     * @param string            $attribute   // The attribute name; id, class, style, etc
     * @param Attributes        $attributes  // The parent attributes
     * @param ?AbstractElement  $element     // The parent element, if any
     */
    public function __construct(
        private string           $attribute,
        private Attributes       $attributes,
        private ?AbstractElement $element = null,
    ) {}

    /**
     *
     * @param string  $property  Only used for the `value` property
     *
     * @return ?string The value of the attribute, or null if the attribute is empty or invalid.
     */
    public function __get( string $property ) : ?string
    {
        return match ( $property ) {
            'value' => $this->__toString() ?: null,
            default => throw new LogicException( 'Invalid property: ' . $property ),
        };
    }

    /**
     * Add a value to the attribute.
     *
     * @param null|string|array  $value    The value to add
     * @param bool               $prepend  Optionally prepend classes and styles
     *
     * @return Attributes|Element
     */
    public function add( null | string | array $value, bool $prepend = false ) : Attributes | AbstractElement
    {
        $this->attributes->add( $this->attribute, $value, $prepend );
        return $this->element ?: $this->attributes;
    }

    /**
     * Set the value of the attribute.
     *
     * @param string[]  $value  The value to set, This overrides the existing value.
     *
     * @return Attributes|Element
     */
    public function set( string  ...$value ) : Attributes | AbstractElement
    {
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
    public function has( string | array $value ) : bool
    {
        return $this->attributes->has( $this->attribute, $value );
    }

    /**
     * Stringify the attribute value.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->attributes->get( $this->attribute ) ?? '';
    }

    /**
     * @param string[]  $string
     * @param string    $separator
     *
     * @return string
     */
    public static function id( string | array $string, string $separator = '-' ) : string
    {
        // TODO : Check if $id is already in use, do this in a Core class
        // TODO : Get ASCII lang from Core\Settings
        return normalizeKey( $string, $separator );
    }

    public static function classes( null | string | array ...$attribute ) : array
    {
        $classes = [];
        foreach ( Attribute::explode( $attribute, ' ' ) as $class ) {
            $classes[] = \trim( $class, " \t\n\r\0\x0B," );
        }
        return $classes;
    }

    public static function styles( string | array $attribute ) : array
    {
        $styles = [];

        $attribute = \is_string( $attribute ) ? Attribute::explode( $attribute, ';' ) : $attribute;

        foreach ( $attribute as $property => $value ) {
            if ( \is_int( $property ) ) {
            dump( $property, $value );
                if ( !\str_contains( $value, ':' ) ) {
                    Log::Error(
                        'The style {key} was parsed, but {error}. The style was skipped.',
                        [
                            'key'   => $property,
                            'error' => 'has no declaration separator',
                            'value' => $value,
                        ],
                    );
                    continue;
                }
                [ $property, $value ] = \explode( ':', $value, 2 );
            }

            $styles[ \trim( $property, " \t\n\r\0\x0B," ) ] = \trim( $value, " \t\n\r\0\x0B,;" );
        }
        return $styles;
    }

    private static function explode( null | string | array $attribute, string $separator ) : array
    {
        if ( !$attribute ) {
            return [];
        }

        if ( \is_string( $attribute ) ) {
            $attributes = \explode( $separator, $attribute );
        }
        else {
            $attributes = [];

            foreach ( $attribute as $attr ) {
                $attributes = [ ...$attributes, ...Attribute::explode( $attr, $separator ) ];
            }
        }

        return \array_filter( $attributes );
    }

}