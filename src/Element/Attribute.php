<?php

declare(strict_types=1);

namespace Northrook\HTML\Element;

use Northrook\HTML\{AbstractElement, Element};
use JetBrains\PhpStorm\Deprecated;
use Northrook\Logger\Log;
use Support\{Normalize, PropertyAccessor};
use Stringable, LogicException;
use voku\helper\ASCII;

trigger_deprecation(
    'html-element',
    '@Element::internal',
    \Attribute::class,
);

/**
 * @internal
 *
 * @property-read string $value
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
#[Deprecated]
final readonly class Attribute implements Stringable
{
    use PropertyAccessor;

    /**
     * @param string           $attribute  // The attribute name; id, class, style, etc
     * @param Attributes       $attributes // The parent attributes
     * @param ?AbstractElement $element    // The parent element, if any
     */
    public function __construct(
        private string           $attribute,
        private Attributes       $attributes,
        private ?AbstractElement $element = null,
    ) {}

    /**
     * @param string $property Only used for the `value` property
     *
     * @return ?string the value of the attribute, or null if the attribute is empty or invalid
     */
    public function __get( string $property ) : ?string
    {
        return match ( $property ) {
            'value' => $this->__toString() ?: null,
            default => throw new LogicException( 'Invalid property: '.$property ),
        };
    }

    /**
     * Add a value to the attribute.
     *
     * @param null|array|string $value   The value to add
     * @param bool              $prepend Optionally prepend classes and styles
     *
     * @return AbstractElement|Attributes
     */
    public function add( null|string|array $value, bool $prepend = false ) : AbstractElement|Attributes
    {
        $this->attributes->add( $this->attribute, $value, $prepend );
        return $this->element ?? $this->attributes;
    }

    /**
     * Set the value of the attribute.
     *
     * @param string ...$value the value to set, This overrides the existing value
     *
     * @return AbstractElement|Attributes
     */
    public function set( string ...$value ) : AbstractElement|Attributes
    {
        $this->attributes->set( $this->attribute, $value );
        return $this->element ?? $this->attributes;
    }

    /**
     * Check if the attribute has a given value.
     *
     * @param array|string $value
     *
     * @return bool
     */
    public function has( string|array $value ) : bool
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
     * @param string[] $string
     * @param string   $separator
     *
     * @return string
     */
    public static function id( string|array $string, string $separator = '-' ) : string
    {
        if ( \class_exists( ASCII::class ) ) {
            $string = \is_array( $string ) ? \implode( $separator, $string ) : $string;
            return ASCII::to_slugify( $string, $separator );
        }

        return Normalize::key( $string, $separator );
    }

    public static function classes( null|string|array ...$attribute ) : array
    {
        $classes = [];

        foreach ( Attribute::explode( $attribute, ' ' ) as $class ) {
            $classes[] = \trim( $class, " \t\n\r\0\x0B," );
        }
        return $classes;
    }

    public static function styles( string|array $attribute ) : array
    {
        $styles = [];

        $attribute = \is_string( $attribute ) ? Attribute::explode( $attribute, ';' ) : $attribute;

        foreach ( $attribute as $property => $value ) {
            if ( \is_int( $property ) ) {
                if ( ! \str_contains( $value, ':' ) ) {
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
                [$property, $value] = \explode( ':', $value, 2 );
            }

            $styles[\trim( $property, " \t\n\r\0\x0B," )] = \trim( $value, " \t\n\r\0\x0B,;" );
        }
        return $styles;
    }

    /**
     * @param null|array|string $attribute
     * @param non-empty-string  $separator
     *
     * @return array
     */
    private static function explode( null|string|array $attribute, string $separator ) : array
    {
        if ( ! $attribute ) {
            return [];
        }

        if ( \is_string( $attribute ) ) {
            $attributes = \str_contains( $attribute, $separator ) ? \explode( $separator, $attribute ) : [$attribute];
        }
        else {
            $attributes = [];

            foreach ( $attribute as $attr ) {
                $attributes = [...$attributes, ...Attribute::explode( $attr, $separator )];
            }
        }

        return \array_filter( $attributes );
    }
}
