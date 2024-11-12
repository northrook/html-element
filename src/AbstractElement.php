<?php

/*-------------------------------------------------------------------/

    AbstractElement

    - A base version, intended to be extended by custom elements
    - Will be used by the UI library

/-------------------------------------------------------------------*/

declare(strict_types=1);

namespace Northrook\HTML;

use Northrook\HTML\Element\{Attributes, Tag};
use Interface\Printable;
use const Support\{WHITESPACE, EMPTY_STRING};
use function Support\toString;
use Stringable;

class AbstractElement implements Printable
{
    protected readonly Tag $tag;

    public readonly Attributes $attributes;

    /** @var array<array-key, Element|Printable|string> */
    protected array $content = [];

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

    /**
     * @param null|array<string, mixed>|string $add
     * @param null|array<string, mixed>|string $value
     *
     * @return $this
     */
    public function attributes(
        string|array|null $add = null,
        string|array|null $value = null,
    ) : static {
        $this->attributes ??= new Attributes();

        $this->attributes->add( $add, $value );

        return $this;
    }

    /**
     * @param null|array<array-key, Element|Printable|string>|Element|string $content
     * @param bool                                                           $prepend
     *
     * @return $this
     */
    public function content( string|array|Element|null $content, bool $prepend = false ) : static
    {
        if ( null === $content ) {
            return $this;
        }

        if ( ! \is_array( $content ) ) {
            $content = [$content];
        }

        if ( $prepend ) {
            $this->content = [...$content, ...$this->content];
        }
        else {
            $this->content = [...$this->content, ...$content];
        }

        return $this;
    }

    protected function buildContent( string $contentSeparator = EMPTY_STRING ) : string
    {
        $content = [];

        foreach ( $this->content as $html ) {
            $content[] = \trim( $html instanceof Stringable ? $html->__toString() : $html );
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
            $attributes = " {$attributes}";
        }

        $this->html = toString(
            [
                "<{$this->tag}{$attributes}>",
                toString( $this->content, $contentSeparator ),
                $this->tag->closingTag,
            ],
            $contentSeparator,
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
        return $this->toString() ?: EMPTY_STRING;
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

    /**
     * @param array<string, mixed> $attributes
     *
     * @return $this
     */
    final protected function assignAttributes( array $attributes = [] ) : static
    {
        $this->attributes ??= new Attributes();

        $this->attributes->set( $attributes );

        return $this;
    }

    /**
     * @return array<string, string> `array[$attribute] = $attribute="$value"`
     */
    public function getAttributes() : array
    {
        return $this->attributes->toArray();
    }
}
