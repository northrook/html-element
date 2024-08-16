<?php

declare( strict_types = 1 );

namespace Northrook\HTML\Element;

use Northrook\Interface\Printable;
use Northrook\Trait\PrintableClass;
use Northrook\HTML\Element;
use Stringable;
use function array_filter, implode, is_array;

/**
 */
final class Content implements Printable
{
    use PrintableClass;

    private array $content = [];

    public function __construct( null | string | Stringable | array $content = null ) {

        if ( $content === null ) {
            return;
        }

        $this->content = array_filter( is_array( $content ) ? $content : [ 'content' => $content ] );
    }

    public function add( int | string $key, string | Element $content ) : void {

        if ( is_int( $key ) ) {

            if ( array_key_exists( $key, $this->content ) ) {

                $insertPosition = $key;
                $newElement     = $content;
                $this->content  = array_merge(
                    array_slice( $this->content, 0, $insertPosition ),
                    [ $newElement ],
                    array_slice( $this->content, $insertPosition ),
                );
                return;
            }

            $this->content[] = $content;
            return;
        }

        $this->content[ $key ] = $content;
    }

    public function append( string | Element $content ) : void {
        $this->content = array_merge( $this->content, [ $content ] );
    }

    public function prepend( string | Element $content ) : void {
        $this->content = array_merge( [ $content ], $this->content );
    }

    public function __toString() : string {
        return $this::implode( $this->content );
    }

    public static function implode( null | string | Stringable | array ...$content ) : string {

        $array = [];

        foreach ( $content as $item ) {
            $item    = is_array( $item ) ? implode( ' ', $item ) : $item;
            $array[] = $item;
        }

        return implode( '', array_filter( $array ) );
    }
}