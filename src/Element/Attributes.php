<?php

namespace Northrook\HTML\Element;

use Countable, Stringable;
use Northrook\HTML\AbstractElement;
use Northrook\Logger\Log;

final class Attributes implements Countable, Stringable
{
    /** @var array<string, mixed> */
    private array $attributes = [];

    /**
     * @param array            $attributes [optional] assigns provided attributes to this object
     * @param ?AbstractElement $parent
     */
    public function __construct(
        array                             $attributes = [],
        private readonly ?AbstractElement $parent = null,
    ) {
        $this->assign( $attributes );
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function assign( array $attributes ) : self
    {
        if ( ! empty( $this->attributes ) ) {
            foreach ( $attributes as $name => $value ) {
                $this->add( $name, $value );
            }
            return $this;
        }

        foreach ( $attributes as $name => $value ) {
            $this->set( $name, $value );
        }
        return $this;
    }

    public function add(
        string|array           $attribute = null,
        string|array|bool|null $value = null,
        bool                   $prepend = false,
    ) : self {
        if ( \is_string( $attribute ) ) {
            $attribute = [$attribute => $value];
        }

        foreach ( $attribute as $name => $value ) {
            $current = $this->attributes[$name] ?? false;

            if ( false === $current ) {
                $this->set( $name, $value );

                continue;
            }

            if ( \is_array( $value ) ) {
                if ( \array_key_exists( 'prepend', $value ) && true === $value['prepend'] ) {
                    $prepend = true;
                    unset( $value['prepend'] );
                }
            }

            $this->attributes[$name] = match ( $name ) {
                'class', 'classes' => $prepend
                        ? \array_merge( Attribute::classes( $value ), $this->attributes[$name] )
                        : \array_merge( $this->attributes[$name], Attribute::classes( $value ) ),
                'style', 'styles' => $prepend
                        ? \array_merge( Attribute::styles( $value ), $this->attributes[$name] )
                        : \array_merge( $this->attributes[$name], Attribute::styles( $value ) ),
                default => $value,
            };
        }
        return $this;
    }

    public function set(
        string|array           $attribute,
        string|array|bool|null $value = null,
    ) : self {
        if ( \is_string( $attribute ) ) {
            $attribute = [$attribute => $value];
        }

        foreach ( $attribute as $name => $value ) {
            $this->attributes[$name] = match ( $name ) {
                'id' => Attribute::id( $value ),
                'class', 'classes' => Attribute::classes( $value ),
                'style', 'styles' => Attribute::styles( $value ),
                default => $value,
            };
        }

        return $this;
    }

    final public function merge( array $attributes ) : self
    {
        foreach ( $attributes as $attribute => $value ) {
            $this->add( $attribute, $value );
        }
        return $this;
    }

    private function classAttribute() : array
    {
        if ( ! isset( $this->attributes['class'] ) ) {
            return [];
        }
        if ( ! \is_array( $this->attributes['class'] ) ) {
            dump( $this );
            return [];
        }
        return $this->attributes['class'];
    }

    /**
     * @param string  $attribute
     *
     * @return null|string|array
     */
    public function get(
        string $attribute,
    ) : string|array|null {
        return match ( $attribute ) {
            'class', 'classes' => $this->classAttribute(),
            'style', 'styles' => ( function() {
                $styles = [];

                foreach ( $this->attributes['style'] ?? [] as $style => $val ) {
                    $styles[$style] = "{$style}: {$val};";
                }
                return $styles;
            } )(),
        };
    }

    public function pull( string $attribute ) : string|array|null
    {
        $value = $this->get( $attribute ) ?? null;
        unset( $this->attributes[$attribute] );
        return $value;
    }

    public function remove( string $attribute ) : self
    {
        unset( $this->attributes[$attribute] );
        return $this;
    }

    public function has(
        string            $attribute,
        string|array|null $value = null,
    ) : bool {
        if ( null === $value ) {
            return isset( $this->attributes[$attribute] );
        }

        $attribute = $this->attributes[$attribute];

        if ( \is_string( $attribute ) ) {
            if ( \is_string( $value ) ) {
                return $attribute === $value;
            }

            Log::error(
                'Unable to property compare the attribute {attribute} of {attributeType} to value of {valueType}. The types do not match.',
                [
                    'attribute'     => $attribute,
                    'attributeType' => \gettype( $attribute ),
                    'valueType'     => \gettype( $value ),
                ],
            );
            return false;

        }

        if ( \is_array( $attribute ) ) {
            $has = [];

            foreach ( $attribute as $currentValue ) {
                if ( \in_array( $currentValue, $value, true ) ) {
                    $has[] = $currentValue;
                }
            }

            // dump( \array_intersect( $attribute, $value ) );

            if ( \count( $has ) === \count( $attribute ) ) {
                return true;
            }
        }

        return false;
    }

    public function clear( bool $areYouSure = false ) : bool
    {
        if ( $areYouSure ) {
            $this->attributes = [];
            return true;
        }
        return false;
    }

    final public function getAttributes( array $merge = [] ) : array
    {
        $attributes = [];

        foreach ( $this->merge( $merge )->attributes as $attribute => $value ) {
            // dd( $this->attributes );
            // Skip empty arrays
            if ( \is_array( $value ) && empty( $value ) ) {
                Log::error(
                    'The attribute {attribute} provided an empty array value.',
                    ['attribute' => $attribute, 'attributes' => $this->attributes],
                );

                continue;
            }

            // Attribute value formatting
            $value = match ( $attribute ) {
                'class' => $this->get( 'class' ),
                'style' => $this->get( 'style' ),
                default => $value,
            };

            // if ( \is_array( $value ) ) {
            //     dump( $attribute, $value );
            // }

            // Convert types to string
            $value = match ( \gettype( $value ) ) {
                'string'  => $value,
                'boolean' => $value ? 'true' : 'false',
                'array'   => \implode( ' ', \array_filter( $value ) ),
                'object'  => \method_exists( $value, '__toString' ) ? $value->__toString() : null,
                'NULL'    => null,
                default   => (string) $value,
            };

            $attributes[$attribute] = $value;
        }

        return $this::sort( $attributes );
    }

    public function getAttributeArray() : array
    {
        return $this->attributes;
    }

    public function toArray( bool $useSingleQuote = false ) : array
    {
        $quote = $useSingleQuote ? "'" : '"';

        $attributes = [];

        foreach ( $this->getAttributes() as $attribute => $value ) {
            // Check if the attribute is considered a boolean
            if ( null === $value || $this->isBooleanAttribute( $attribute ) ) {
                $attributes[$attribute] = $attribute;
            }

            // Discard empty values, assign the attribute="value" as string
            else {
                $attributes[$attribute] = $attribute.'='.$quote.$value.$quote;
            }
        }
        return $attributes;
    }

    private function isBooleanAttribute( string $attribute ) : bool
    {
        return \in_array( $attribute, ['disabled', 'readonly', 'required', 'checked', 'hidden', 'autofocus'], true );
    }

    /**
     * @internal
     *
     * @param string $attribute
     *
     * @return Attribute
     */
    final public function edit( string $attribute ) : Attribute
    {
        return new Attribute( $attribute, $this, $this->parent );
    }

    public static function from( array $attributes ) : Attributes
    {
        return new Attributes( $attributes );
    }

    public static function sort( array $attributes, ?array $order = null, ?array $sortByList = null ) : array
    {
        $sortByList ??= [
            'lang',
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

        $sort = [];

        foreach ( $order ?? $sortByList as $value ) {
            if ( \array_key_exists( $value, $attributes ) ) {
                $sort[$value] = $attributes[$value];
            }
        }

        return \array_merge( $sort, $attributes );
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function toString( bool $useSingleQuote = false ) : string
    {
        return \implode( ' ', $this->toArray( $useSingleQuote ) );
    }

    final public function count() : int
    {
        return \count( $this->attributes );
    }
}
