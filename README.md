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

```php
/** @var \Optimus\EventDispatcher $dispatcher */
$dispatcher = new EventDispatcher();

// Listen for all nodes in the DOMDocument.
// Your own listener can be anything you want as long as it is callable
$dispatcher->addListener('transcode.*', function(TranscodeNodeEvent $event) {
  // your custom logic
  $event->getNode()->setAttribute('data-transcoded', 1);
});

// Listen for all nodes in the DOMDocument.
// Use a pre-defined rule
$dispatcher->addListener('transcode.*', [new AddPositionClassRule(), 'handle']);

// Listen for all nodes in the DOMDocument with a 'depth' from 5 to 10
// Constraints may only be used with listeners that implement Optimus\Rule\RuleInterface.
// There are several pre-defined rules you may wish to use, but it's simple to make your own too as long as they implement the interface
$rule = new AddPositionClassRule();
$depthConstraint = new DepthConstraint(5, 10);
$rule->addConstraint($depthConstraint);
$dispatcher->addListener('transcode.*', [$rule, 'handle']);

/** @var \Optimus\Transcoder $transcoder */
$transcoder = new Transcoder($dispatcher);
$transcoder->setDocument($domDocument); // $domDocument is obtained elsewhere

/** @var \DOMDocument $output */
$output = $transcoder->transcode();
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
