<?php

namespace Northrook\HTML;

use Northrook\Core\Trait\PropertyAccessor;
use Northrook\Logger\Log;
use function Northrook\Core\Function\normalizeKey;

/**
 * @property-read string     $html
 * @property-read Tag        $tag
 * @property-read Attribute  $id
 * @property-read Attribute  $class
 * @property-read Attribute  $style
 * @property-read Attributes $attributes
 * @property-read Content    $content
 *
 */
class Element
{
    use PropertyAccessor;

    /** @var string Rendered HTML */
    private string $html;

    protected readonly Tag        $tag;
    protected readonly Attributes $attributes;
    protected readonly Content    $content;

    public function __construct(
        string $tag = 'div',
        array  $attributes = [],
        mixed  $content = null,
    ) {
        $this->tag        = new Tag( $tag );
        $this->attributes = new Attributes( $attributes );
        $this->content    = new Content( $content );
    }

    public function __get( string $property ) {
        return match ( $property ) {
            'tag'                  => $this->tag,
            'id', 'class', 'style' => $this->attributes->edit( $property ),
            'attributes'           => $this->attributes,
            default                => null,
        };
    }

    /**
     * Called as soon as the Element is built.
     *
     * - Will not be called if the Element is already built.
     *
     * @return void
     */
    protected function onBuild() : void {}

    /**
     * Called just before the Element is printed as HTML.
     *
     * @return void
     */
    protected function onPrint() : void {}

    final protected function build() : void {

        if ( isset( $this->html ) ) {
            return;
        }

        $this->onBuild();

        $this->html = $this->content::implode(
            $this->element(),
            $this->content->toString(),
            $this->tag->closingTag,
        );
    }


    final public function __toString() : string {
        $this->build();
        return $this->html;
    }

    final public function print() : void {
        $this->onPrint();
        echo $this->__toString();
    }


    /**
     * Generates the HTML string for this Element.
     *
     * @return string
     */
    private function element() : string {
        return '<' . implode( ' ', array_filter( [ $this->tag, ... $this->attributes->toArray() ] ) ) . '>';
    }

    final protected function attribute(
        string                    $name,
        string | array | callable $value,
        bool                      $force = false,
        mixed                     ...$args
    ) : self {

        if ( !$force && $this->attributes->has( $name ) ) {
            return $this;
        }

        $value = is_callable( $value ) ? $value( ...$args ) : $value;

        $this->attributes->set( $name, $value, $force );

        return $this;
    }

    public static function id( string $string, string $separator = '-' ) : string {
        // TODO : Check if $id is already in use, do this in a Core class
        // TODO : Get ASCII lang from Core\Settings
        return normalizeKey( $string, $separator );
    }

    public static function classes( null | string | array ...$classes ) : array {

        $return = [];

        foreach ( $classes as $class ) {

            if ( !$class ) {
                continue;
            }

            $class = array_filter( is_string( $class ) ? explode( ' ', $class ) : $class );

            $return = [ ...$return, ...$class ];
        }

        return $return;
    }

    public static function styles( null | string | array ...$styles ) : array {

        $return = [];

        foreach ( $styles as $style ) {
            if ( !$style ) {
                continue;
            }

            $style = array_filter( is_string( $style ) ? explode( ';', $style ) : $style );

            foreach ( $style as $property => $value ) {

                if ( is_int( $property ) ) {
                    if ( !str_contains( $value, ':' ) ) {
                        Log::Error(
                            'The style {key} was parsed, but {error}. The style was skipped.',
                            [
                                'key'   => $value,
                                'error' => 'has no declaration separator',
                                'value' => $style,
                            ],
                        );
                        continue;
                    }
                    [ $property, $value ] = explode( ':', $value );
                }

                $return[ trim( $property ) ] = trim( $value );
            }
        }

        // dd($return);
        return $return;
    }
}