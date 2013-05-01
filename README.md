Optimus
=======

[![Build Status](https://secure.travis-ci.org/RobMasters/Optimus.png?branch=master)](http://travis-ci.org/RobMasters/Optimus)

_The prime server-side way to manage DOM transformations_

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
use Optimus\EventDispatcher,
    Optimus\Transcoder;

$dispatcher = new EventDispatcher();
$transcoder = new Transcoder($dispatcher);
$transcoder->setDocument($domDocument); // $domDocument is obtained elsewhere

// Add your listeners/transformers to the event dispatcher...

/** @var \DOMDocument $output */
$output = $transcoder->transcode();
```

### Adding custom listeners

Because the Event Dispatcher directly extends the Symfony2 EventDispatcher component you're able to add your own listeners,
which can be anything that is callable. e.g.

```php
use Optimus\Event\TranscodeElementEvent;

# Remove all script nodes from the DOMDocument.
$dispatcher->addListener('div', function(TranscodeElementEvent $event) {
  $event->removeNode(); // also stops propagation as there's no point continuing
});

# Listen for all nodes in the DOMDocument. NOTE: this does not include text nodes
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

### Adding Transformers

A simpler option is to use a Transformer object. For example:

```php
use Optimus\Transformer\AddPositionClassTransformer;

# Transform all nodes in the DOMDocument
$dispatcher->addTransformer('*', new AddPositionClassTransformer());
```

This method also allows you to specify multiple selectors at once for convenience. Behind the scenes this will add a listener
for each selector given. e.g.

```php
use Optimus\Transformer\AddPositionClassTransformer;

# Add position classes to all <div> and <li> nodes in the document
$transformer = new AddPositionClassTransformer();
$dispatcher->addTransformer(['div', 'li'], $transformer);
```

### Using Constraints to control when a Transformer will be applied

The main benefit to using a Transformer rather than any other listener is that it allows you much greater control over
when the Transformer is dispatched events. This is achieved by configuring any number of Constraint objects and adding
them to the Transformer. e.g.

```php
use Optimus\Constraint\DepthConstraint;

# Only transform nodes that are nested at least 5 levels deep in the DOM, but no more than 10,
$transformer->addConstraint(new DepthConstraint(5, 10));
$dispatcher->addTransformer('*', $transformer);
```

For added convenience, certain constraints can be added by specifying them as a CSS selector when adding the Transformer
to the Event Dispatcher. e.g.

```php
use Optimus\Constraint\HasAttributeConstraint,
    Optimus\Constraint\HasClassConstraint;

# Adding an id constraint for any tag
$transformer->addConstraint(new HasAttributeConstraint('id', 'container'));
$dispatcher->addTransformer('*', $transformer);

// ...can be written shorter as:
$dispatcher->addTransformer('#container', $transformer);


# Adding class constraint(s) for <li> tags
$transformer->addConstraint(new HasClassConstraint(['first', 'selected']));
$dispatcher->addTransformer('li', $transformer);

// ...can be written shorter as:
$dispatcher->addTransformer('li.first.selected', $transformer);
```

### Combining Constraints in a Composite for even greater control

You'll only be able to achieve so much by adding individual Constraints to a Transformer that all need to be satisfied.
It's likely there will be times when you want to transform a node if one of any number of conditions are met, and this can
be achieved by adding any number of Constraints to the CompositeConstraint. As the name implies this also implements the
Optimus\Constraint\ConstraintInterface so it can be added to a Transformer the same as any other Constraint. e.g.

```php
use Optimus\Constraint\CompositeConstraint,
    Optimus\Constraint\HasAttributeConstraint,
    Optimus\Constraint\HasClassConstraint;

# Listen to <ul> tags that have 'nav' anywhere in their id, or have a 'nav' class
$idConstraint = new HasAttributeConstraint('id', 'container');
$idConstraint->setPattern('/nav/');
$transformer->addConstraint(new CompositeConstraint([
    $idConstraint,
    new HasClassConstraint('nav')
]));
```

By default the CompositeConstraint uses the `CompositeConstraint::MODE_ANY` mode; it will return true if any of the Constraints
given to it are satisfied. There are two other modes to choose from: `CompositeConstraint::MODE_ALL` or `CompositeConstraint::MODE_NONE`.
MODE_ALL is probably going to be the least used as this is the default behaviour when adding Constraints directly to a
Transformer - it is really only necessary when nesting CompositeConstraints inside each other. MODE_NONE, on the other hand,
is very useful as it allows you to specify when a Transformer should NOT be applied. e.g.

```php
use Optimus\Constraint\CompositeConstraint,
    Optimus\Constraint\HasAttributeConstraint;

# Listen to all <input> nodes except checkboxes and radio buttons
$compositeConstraint = new CompositeConstraint();
$compositeConstraint
    ->setMode(CompositeConstraint::MODE_NONE)
    ->addConstraint(new HasAttributeConstraint('type', 'checkbox'))
    ->addConstraint(new HasAttributeConstraint('type', 'radio'))
;
$transformer->addConstraint($compositeConstraint);
$dispatcher->addTransformer('input', $transformer);
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
