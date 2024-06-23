# HTML Elements

A collection of classes for generating HTML elements and their attributes.

The motivation behind this package is to provide a simple way to generate HTML elements
with sensible defaults, while being much more efficient than using the DomDocument class.

Unlike the DomDocument class, this package does not provide a way fully traverse the DOM tree.
This is a deliberate design decision to keep the package lightweight and performant.

```php
namespace Northrook\HTML\Element;

$button = new Northrook\HTML\Element\Button();
$div = new Northrook\HTML\Element();

use Northrook\HTML\Element\Document;
use Northrook\HTML\Element\Button;

$document = new Document();



```