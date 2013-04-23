<?php

namespace Optimus\Tests;

use Optimus\Rule\AddPositionClassRule;
use Optimus\Transcoder;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventDispatcher;

class TranscoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var Transcoder
     */
    protected $transcoder;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->transcoder = new Transcoder($this->dispatcher);
    }

    /**
     * TODO move into correct test case
     */
    public function testPositionClassRule()
    {
        // Connect rules
        $this->dispatcher->addListener('transcode.li', array(new AddPositionClassRule(), 'handle'));

        $dom = new \DOMDocument();
        $dom->loadHTML('<html><head></head><body><div><h1>Heading</h1><ul><li>First</li><li>Second</li></ul><div>Some text <span>in a span</span></div></div></body>');
        $transcoded = $this->transcoder->setDocument($dom)->transcode();

        $crawler = new Crawler();
        $crawler->add($transcoded->saveHTML());

        $this->assertEquals('first', $crawler->filter('li')->first()->attr('class'));
        $this->assertEquals('last', $crawler->filter('li')->last()->attr('class'));
    }
}