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
$eventPrefix = 'transcode'; // This is the default - you don't need to specify this, but can choose a different prefix
$transcoder = new Transcoder($dispatcher, $eventPrefix);
$transcoder->setDocument($domDocument); // $domDocument is obtained elsewhere

// Add your listeners/transformers...

/** @var \DOMDocument $output */
$output = $transcoder->transcode();
```

### Adding listeners

Because the Event Dispatcher directly extends the Symfony2 EventDispatcher component you're able to add your own listeners,
which can be anything that is callable. e.g.

```php
// Listen for all nodes in the DOMDocument.
$dispatcher->addListener('transcode.*', function(TranscodeNodeEvent $event) {
  // your custom logic
  $event->getNode()->setAttribute('data-transcoded', 1);
});
```

When using the addListener() method you'll need to specify the full event name, which consists of the Transcoder's specified prefix
followed by a dot and then the name of the node. As shown above, a wilcard character, '*', can be used to listen to all nodes.

There is a better way of adding listeners though, which is to use a Transformer object. This offers a simpler syntax and
provides much greater control as you're able to apply 'Constraints' to each Transformer, meaning they will only be called
when all of the constraints are satisfied.

Here is the simplest example:

```php
// Transform all nodes in the DOMDocument.
// Use a pre-defined transformer
$dispatcher->addTransformer('*', new AddPositionClassTransformer());
```

Here's an example of listening to all DIV and LI nodes in the document that match a 'depth' constraint
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
