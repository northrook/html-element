<?php

namespace Northrook\HTML\Element;

use Countable, Stringable;
use Northrook\Core\Trait\PropertyAccessor;
use Northrook\HTML\Element;

/**
 * @property-read Attribute $id
 * @property-read Attribute $class
 * @property-read Attribute $style
 */
final class Attributes implements Countable, Stringable
{
    use PropertyAccessor;

    /**
     * @var array{string, string|array<string>}
     */
    private array $attributes = [];

    public function __construct(
        array                     $attributes = [],
        private readonly ?Element $parent = null,
    ) {
        foreach ( $attributes as $name => $value ) {
            $this->add( $name, $value );
        }
    }

    public function get( string $name ) : ?string {
        $attribute = match ( $name ) {
            'class' => $this->getClasses( $this->attributes[ 'class' ] ?? [] ),
            'style' => $this->getStyles( $this->attributes[ 'style' ] ?? [] ),
            default => $this->attributes[ $name ] ?? null,
        };

        return is_array( $attribute ) ? implode( ' ', $attribute ) : $attribute;
    }

    public function __get( string $property ) : ?Attribute {
        return match ( $property ) {
            'id', 'class', 'style' => $this->edit( $property ),
            default                => null,
        };
    }

    public function edit( string $attribute ) : Attribute {
        return new Attribute( $attribute, $this, $this->parent );
    }

    /**
     * Add attributes to this element.
     *
     * - Will not overwrite existing.
     * - Use `set` to overwrite existing attributes.
     *
     * @param string  $name
     * @param mixed   $value
     * @param bool    $prepend
     *
     * @return $this
     */
    public function add( string $name, mixed $value, bool $prepend = false ) : self {

        if ( isset( $this->attributes[ $name ] ) && !( $name === 'class' || $name === 'style' ) ) {
            return $this;
        }

        $this->attributes[ $name ] = match ( $name ) {
            'id'    => Element::id( $value ),
            'class' => Element::classes( ... $this->getAttribute( 'class', $value, $prepend ) ),
            'style' => Element::styles( ... $this->getAttribute( 'style', $value, $prepend ) ),
            default => $value,
        };
        return $this;
    }

    private function getAttribute( string $name, mixed $value, bool $prepend ) : array {
        return match ( $prepend ) {
            true  => [ $value, ...$this->attributes[ $name ] ?? [] ],
            false => [ ...$this->attributes[ $name ] ?? [], $value ],
        };
    }

    /**
     * Sets a given attribute.
     *
     *  - Will not overwrite existing attributes by default.
     * - Set $overwrite` to true to overwrite existing attributes.
     *
     * @param string  $name
     * @param mixed   $value
     *
     * @return $this
     */
    public function set( string $name, mixed $value ) : self {

        $this->attributes[ $name ] = match ( $name ) {
            'id'    => Element::id( $value ),
            'class' => Element::classes( $value ),
            'style' => Element::styles( $value ),
            default => $value,
        };

        return $this;
    }

    /**
     * Check if a given attribute exists, and optionally check its value.
     *
     * @param string   $attribute
     * @param ?string  $value  [optional]
     *
     * @return bool
     */
    public function has( string $attribute, ?string $value = null ) : bool {

        // Bail early if the attribute does not exist
        if ( !isset( $this->attributes[ $attribute ] ) ) {
            return false;
        }

        // Check against class property only
        if ( $value === null ) {
            return array_key_exists( $attribute, $this->attributes );
        }

        // Check if class exists
        if ( 'class' === $attribute ) {
            return in_array( $value, $this->attributes[ 'class' ] ?? [], true );
        }

        // Check against style attribute
        if ( 'style' === $attribute ) {

            // If the value could be a full style declaration, check against that
            if ( str_contains( $value, ':' ) ) {
                [ $style, $value ] = explode( ':', $value );
                return $this->attributes[ 'style' ][ $style ] === $value;
            }

            return array_key_exists( $value, $this->attributes[ 'style' ] ?? [] );
        }

        return in_array( $value, $this->attributes[ $attribute ], true );
    }

    private function getClasses( array $classes ) : array {
        return array_flip( array_flip( $classes ) );
    }

    private function getStyles( array $styles ) : array {
        foreach ( $styles as $style => $val ) {
            $styles[ $style ] = "$style: $val;";
        }
        return $styles;
    }

    private function getAttributes() : array {
        $attributes = [];

        foreach ( $this->attributes as $attribute => $value ) {

            // Skip empty arrays
            if ( is_array( $value ) && empty( $value ) ) {
                continue;
            }

            // Format style attribute
            if ( $value && 'style' === $attribute ) {
                $value = $this->getStyles( $value );
                // dump( $value );
            }


            // Deduplicate class attribute
            if ( $value && 'class' === $attribute ) {
                $value = $this->getClasses( $value );
            }

            // Convert types to string
            $value = match ( gettype( $value ) ) {
                'string'  => $value,
                'boolean' => $value ? 'true' : 'false',
                'array'   => implode( ' ', array_filter( $value ) ),
                'object'  => method_exists( $value, '__toString' ) ? $value->__toString() : null,
                'NULL'    => null,
                default   => (string) $value,
            };

            // Check if the attribute is considered a boolean
            if ( null === $value || $this->isBooleanAttribute( $attribute ) ) {
                $attributes[ $attribute ] = $attribute;
            }

            // Discard empty values, assign the attribute="value" as string
            else {
                $attributes[ $attribute ] = "$attribute=\"$value\"";
            }
        }

        return Attributes::sort( $attributes );
    }

    public function toArray() : array {
        return $this->getAttributes();
    }

    public function count() : int {
        return count( $this->attributes );
    }

    public function __toString() : string {
        return implode( ' ', $this->getAttributes() );
    }

    private function isBooleanAttribute( string $attribute ) : bool {
        return in_array( $attribute, [ 'disabled', 'readonly', 'required', 'checked', 'hidden', 'autofocus', ], true );
    }

    public static function sort(
        array  $attributes,
        ?array $order = null,
        ?array $sortByList = null,
    ) : array {

        $sortByList ??= [
            'id',
            'href',
            'src',
            'rel',
            'name',
            'type',
            'value',
            'class',
            'style',
        ];
        $sort       = [];

        foreach ( $order ?? $sortByList as $value ) {
            if ( array_key_exists( $value, $attributes ) ) {
                $sort[ $value ] = $attributes[ $value ];
            }
        }

        return array_merge( $sort, $attributes );
    }
}