Optimus
=======

_Optimus is the prime server-side way to transform a DOM_

[![Build Status](https://secure.travis-ci.org/RobMasters/Optimus.png?branch=master)](http://travis-ci.org/RobMasters/Optimus)

## Installation

```
$ git clone https://github.com/RobMasters/Optimus.git
$ cd Optimus

$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install

```

## Usage

### Overview

Here is the minimum that is required. You'll need to construct the Transcoder with a \Optimus\EventDispatcher instance
and provide it with a \DOMDocument. You can configure as many listeners/transforms as you want and add them to the
event dispatcher. Then you can fetch the transcoded output from the Transcoder.

```php
/** @var \Optimus\EventDispatcher $dispatcher */
$dispatcher = new EventDispatcher();

/** @var \Optimus\Transcoder $transcoder */
$transcoder = new Transcoder($dispatcher);
$transcoder->setDocument($domDocument); // $domDocument is obtained elsewhere

// Add your listeners/transformers...

/** @var \DOMDocument $output */
$output = $transcoder->transcode();
```

### Adding listeners

Because the Event Dispatcher directly extends the Symfony2 EventDispatcher component you're able to add your own listeners,
which can be anything that is callable. e.g.

```php
// Remove all script nodes from the DOMDocument.
$dispatcher->addListener('div', function(TranscodeNodeEvent $event) {
  $event->removeNode(); // also stops propagation as there's no point continuing
});

// Listen for all nodes in the DOMDocument. NOTE: this does not include text nodes
$dispatcher->addListener('*', function(TranscodeElementEvent $event) {
  /** @var \DOMElement $node **/
  $node = $event->getNode();

  if ($node->nodeName === 'script') {
    throw new \Exception('This should never get thrown as the specific div listener is stopping propagation');
  }

  // your custom logic
  $node->setAttribute('data-transcoded', 1);
});
```

The event name to listen to is the name of the node (i.e. 'div' or 'ul') or a wilcard character, '*', which can be used to
listen to all element nodes. This 'wildcard' event is dispatched after the specific one for the node and uses  the same
event instance, so if the propagation was stopped in a listener to the 'node name' event then the wildcard event will not
be dispatched.

A simpler option is to use a Transformer object. This provides much greater control as you're able to apply 'Constraints'
to each Transformer, meaning they will only be called when all of the constraints are satisfied.

Here is the simplest example:

```php
// Transform all nodes in the DOMDocument.
// Use a pre-defined transformer
$dispatcher->addTransformer('*', new AddPositionClassTransformer());
```

This method also allows you to specify multiple selectors at once for convenience. Behind the scenes this will add a listener
for each selector given. e.g.

```php
// Add position classes to all <div> and <li> nodes in the document
$transformer = new AddPositionClassTransformer();
$dispatcher->addTransformer(['div', 'li'], $transformer);
```

### Limiting which Transformers are applied using Constraints

You are able to target much more specific nodes by adding as many constraints as you wish to a Transformer. The event will
only be triggered if all of the constraints are met. e.g.

```php
// Only transform nodes that are nested at least 5 levels deep in the DOM, but no more than 10,
$transformer->addConstraint(new DepthConstraint(5, 10));
$dispatcher->addTransformer('*', $transformer);
```

For added convenience, certain constraints can be added by specifying them as a CSS selector when adding the Transformer
to the Event Dispatcher. e.g.

```php
# Adding an id constraint for any tag
// This...
$transformer->addConstraint(new HasAttributeConstraint('id', 'container'));
$dispatcher->addTransformer('*', $transformer);

// ...is exactly equivalent to this:
$dispatcher->addTransformer('#container', $transformer);


# Adding class constraint(s) for <li> tags
// This...
$transformer->addConstraint(new HasClassConstraint(array('first', 'selected'));
$dispatcher->addTransformer('li', $transformer);

// ...is exactly equivalent to this:
$dispatcher->addTransformer('li.first.selected', $transformer);
```

## Testing

First, you must ensure you've installed all of the dev dependencies via composer:

```
$ php composer.phar install --dev

```

Then simply run the phpunit executable in the vendor directory:

```
$ vendor/bin/phpunit
```
