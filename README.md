# HTML Elements

Generate HTML elements and their attributes.

The motivation behind this package is to provide a simple way to generate HTML elements
with sensible defaults, while being more light-weight than the [DOMDocument class](https://www.php.net/manual/en/class.domdocument.php).

Elements can be nested to create complex HTML structures, but unlike DOMDocument it does not provide a way fully traverse the DOM tree.

This is a deliberate design decision to keep the package lightweight and performant.

> [!IMPORTANT]
> This package is still in development.
>
> While it is considered MVP and stable, it may still undergo breaking changes.

> [!NOTE]
> Documentation is still being written. 

## Installation

```bash
composer require northrook/html-element
``` 

## Usage

New elements can be created using the `Element` class:

```php
namespace Northrook\HTML\Element;

$basic = new Element( content: 'Hello World!' );

echo $basic;
```
```html
<div>Hello World!</div>
```

Elements can be nested using the `$content` parameter.
It accepts strings, Elements, and arrays of either.

>[!IMPORTANT]
>The `Element` class does not escape provided `$content`, so ensure you do so either before passing it, or later down the line.

```php
echo new Element( 'h1', [ 'class' => 'example classes' ], $basic );
```
```html
<h1 class="example classes">
    <div>Hello World!</div>
</h1>
```
```php
$button = new Element(
    tag        : 'button',
    attributes : [ 'id' => 'Save Action', 'class' => 'btn icon' ],
    content    : [
        new Element( 'i', content: '<svg ... </svg>' ),
        'Save',
    ]
);

echo $button;
```
```html
<button id="save-action" type="button" class="btn icon">
    <i>...</i>
    Save
</button>
```

## License
[MIT](https://github.com/northrook/html-element/blob/main/LICENSE)