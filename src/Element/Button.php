<?php

namespace Northrook\HTML\Element;

use Northrook\HTML\Element;

class Button extends Element
{
    /**
     * @param array{
     *     id: string,
     *     class: string|array,
     *     style: string|array,
     *     type: 'button'|'submit'|'reset',
     *     disabled: bool,
     *     form: string,
     *     name: string,
     *     value: string,
     *     autofocus: bool,
     *     required: bool,
     *     data-tooltip: string,
     *     data-tooltip-position: string,
     *     data-tooltip-placement: string,
     * }                  $attributes
     * @param mixed|null  $content
     */
    public function __construct(
        array $attributes = [],
        mixed $content = null,
    ) {
        $attributes[ 'type' ] ??= 'button';
        parent::__construct( 'button', $attributes, $content );
        $this->attributes->add( 'class', 'button', true );
    }
}