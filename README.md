Optimus
=======

_DOM Transcoding using Symfony2 components_

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
    throw new \Exception('This should never get thrown as the specific 'div' listener is stopping propagation');
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

Here's an example of listening to all &lt;div&gt; and &lt;li&gt; nodes in the document that match a 'depth' constraint
(this refers to how deeply nested it is in the DOM) of being no less 5 deep and no greater than 10.

```php
// Transform div and li nodes in the DOMDocument with a 'depth' from 5 to 10
// There are several pre-defined transformers you may wish to use, but it's simple to make your own too as long as they implement the interface
$transformer = new AddPositionClassTransformer();
$transformer->addConstraint(new DepthConstraint(5, 10));
$dispatcher->addTransformer(['div', 'li'], $transformer);
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
