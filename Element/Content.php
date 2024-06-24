<?php

namespace Northrook\HTML\Element;


use Northrook\Core\Interface\Printable;

/**
 */
final class Content implements Printable
{
    private array $content = [];

    public function __construct( null | string | \Stringable | array $content = null ) {

        if ( $content === null ) {
            return;
        }

        $this->content = is_array( $content ) ? $content : [ 'content' => $content ];
    }


    public function __toString() : string {
        return Content::implode( ...$this->content );
    }

    public function toString() : ?string {
        return $this->__toString() ?: null;
    }

    public function print() : void {
        echo $this->__toString();
    }

    public static function implode( null | string | \Stringable | array ...$content ) : string {

        $array = [];

        foreach ( $content as $item ) {
            $item    = is_array( $item ) ? implode( ' ', $item ) : $item;
            $array[] = $item;
        }

        return implode( '', array_filter( $array ) );
    }
}