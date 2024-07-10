<?php

declare( strict_types = 1 );

namespace Northrook\HTML;

use LogicException;
use Northrook\Core\Interface\Printable;
use Northrook\Core\Trait\PropertyAccessor;
use Northrook\HTML\Element\{Attribute, Attributes, Content, Tag};
use Northrook\Logger\Log;
use function array_filter;
use function explode;
use function implode;
use function is_callable;
use function is_int;
use function is_string;
use function Northrook\Core\Function\normalizeKey;
use function str_contains;
use function trim;

/**
 * @property-read string    $html
 * @property-read Tag       $tag
 * @property-read Attribute $id
 * @property-read Attribute $class
 * @property-read Attribute $style
 *
 */
class Element implements Printable
{
    use PropertyAccessor;

    /** @var string Rendered HTML */
    protected string $html;

    protected readonly Tag     $tag;
    public readonly Attributes $attributes;
    public readonly Content    $content;


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
    ) {
        $this->tag        = new Tag( $tag );
        $this->attributes = new Attributes( $this->elementAttributes( $attributes ), $this );
        $this->content    = new Content( $content );
    }

    private function elementAttributes( array $attributes ) : array {

        $attributes = match ( $this->tag->name ) {
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6' => $this->headingAttributes( $this->tag->name, $attributes ),
            'button'                           => [ 'type' => 'button', ...$attributes, ],
            'input'                            => [ 'type' => 'text', ...$attributes, ],
            'img'                              => [ 'alt' => '', ...$attributes, ],
            'link'                             => $this->linkAttributes( $attributes ),
            default                            => $attributes,
        };

        return array_filter( $attributes );
    }

    /**
     * Ensures heading elements have a class of the same name as the heading tag.
     *
     * This is used to apply the correct styling to the heading.
     *
     * The heading style may be used on other, non-heading elements,
     * and in some cases a different heading style may be desired for a heading.
     *
     * @param string  $tag
     * @param array   $attributes
     *
     * @return array
     */
    private function headingAttributes( string $tag, array $attributes ) : array {
        $attributes[ 'class' ] = $tag . ' ' . ( $attributes[ 'class' ] ?? '' );
        return $attributes;
    }

    private function linkAttributes( array $attributes ) : array {

        if ( !isset( $attributes[ 'rel' ] ) && isset( $attributes[ 'href' ] ) ) {
            $filename = strstr( basename( $attributes[ 'href' ] ), '?', true );
            if ( str_ends_with( $filename, '.css' ) ) {
                $attributes[ 'rel' ] = 'stylesheet';
            }
        }

        return $attributes;
    }

    /**
     * @param string  $property
     *
     * @return null|Attribute|Attributes|Tag
     */
    public function __get( string $property ) : Attribute | Attributes | Tag | null {

        // __get is mainly used to facilitate editing attributes

        return match ( $property ) {
            'tag'                  => $this->tag,
            'id', 'class', 'style' => $this->attributes->edit( $property ),
            default                => $this->{$property} ??
                                      throw new LogicException( 'Invalid property: ' . $property ),
        };
    }

    final public function set( string $property, mixed $value ) : Element {
        $this->attributes->set( $property, $value );
        return $this;
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
     * - Called in both {@see toString()} and {@see print()}.
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

    final public function toString() : ?string {
        $this->onPrint();
        return $this->__toString() ?: null;
    }

    final public function print() : void {
        $this->onPrint();
        echo $this->__toString();
    }

    public function prepend( string | Element $content ) : Element {
        $this->content->prepend( $content );
        return $this;
    }

    public function append( string | Element $content ) : Element {
        $this->content->append( $content );
        return $this;
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

        $this->attributes->set( $name, $value );

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
        return $return;
    }
}