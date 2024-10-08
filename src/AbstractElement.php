<?php

/*-------------------------------------------------------------------/

    AbstractElement

    - A base version, intended to be extended by custom elements
    - Will be used by the UI library

/-------------------------------------------------------------------*/

declare( strict_types = 1 );

namespace Northrook\HTML;

use Northrook\HTML\Element\Attributes;
use Northrook\HTML\Element\Tag;
use Northrook\Interface\Printable;
use const Support\{WHITESPACE, EMPTY_STRING};
use function Support\toString;


class AbstractElement implements Printable
{
    protected readonly Tag     $tag;
    public readonly Attributes $attributes;
    protected array            $content = [];

    /** @var string Rendered HTML */
    protected string $html;

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

    final public function tag( string $name ) : self
    {
        if ( isset( $this->tag ) ) {
            $this->tag->set( $name );
        }
        else {
            $this->tag = new Tag( $name );
        }

        return $this;
    }
    public function attributes(
        string | array | null $add = null,
        string | array | null $value = null,
    ) : static
    {
        $this->attributes ??= new Attributes();

        $this->attributes->add( $add, $value );

        return $this;
    }

    public function content( string | array | Element | null $content, bool $prepend = false ) : static
    {
        if ( $content === null ) {
            return $this;
        }

        if ( !\is_array( $content ) ) {
            $content = [ $content ];
        }

        if ( $prepend ) {
            $this->content = [ ... $content, ... $this->content ];
        }
        else {
            $this->content = [ ... $this->content, ... $content ];
        }

        return $this;
    }

    protected function buildContent( string $contentSeparator = EMPTY_STRING ) : string
    {
        $content = [];
        foreach ( $this->content as $html ) {
            $content[] = \trim( $html );
        }

        return \implode( $contentSeparator, $content );
    }

    final protected function buildElement( string $contentSeparator = EMPTY_STRING ) : static
    {
        if ( isset( $this->html ) ) {
            return $this;
        }

        $this->onBuild();

        if ( $attributes = toString( $this->getAttributes(), WHITESPACE ) ) {
            $attributes = " $attributes";
        }

        $this->html = toString(
            [
                "<{$this->tag}{$attributes}>",
                toString( $this->content, $contentSeparator ),
                $this->tag->closingTag,
            ],
        );
        return $this;
    }

    /**
     * Builds the `Element` and returns the generated `html`.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->buildElement()->html;
    }

    public function toString( string $contentSeparator = EMPTY_STRING ) : ?string
    {
        $this->buildElement( $contentSeparator );

        $this->onPrint();

        return $this->html;
    }

    public function print( string $contentSeparator = EMPTY_STRING ) : void
    {
        echo $this->toString( $contentSeparator );
    }

    final protected function assignAttributes( array $attributes = [] ) : static
    {
        $this->attributes ??= new Attributes();

        $this->attributes->set( $attributes );

        return $this;
    }

    public function getAttributes() : array
    {
        return $this->attributes->toArray();
    }

}