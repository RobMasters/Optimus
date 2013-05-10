<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Optimus\Adapter\AdapterInterface;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    const WEB_SERVER_PORT = 8000;
    const PHANTOM_PORT = 8080;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var DOMDocument
     */
    protected $result;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->dispatcher = new \Optimus\EventDispatcher();
    }

    /**
     * @Given /^I have a "([^"]*)" adapter$/
     */
    public function iHaveAnAdapter($adapterName)
    {
        switch ($adapterName) {
            case 'Phantom':
                $this->adapter = new \Optimus\Adapter\PhantomAdapter('localhost', self::PHANTOM_PORT);
                break;

            case 'Guzzle':
                $this->adapter = new \Optimus\Adapter\GuzzleAdapter(new \Guzzle\Http\Client());
                break;

            default:
                throw new \Exception("Uknown adapter, `$adapterName`");
        }
    }

    /**
     * @Given /^I am using the "([^"]*)" example$/
     */
    public function iAmUsingTheExample($filename)
    {
        $this->adapter->setUrl(sprintf('localhost:%d/%s.html', self::WEB_SERVER_PORT, $filename));
    }

    /**
     * @When /^I transcode the page$/
     */
    public function iTranscodeThePage()
    {
        $transcoder = new \Optimus\Transcoder($this->dispatcher);

        $this->result = $transcoder
            ->setAdapter($this->adapter)
            ->transcode()
        ;
    }

    /**
     * @Then /^"([^"]*)" should contain "([^"]*)"$/
     */
    public function shouldContain($selector, $expectedText)
    {
        $crawler = new \Symfony\Component\DomCrawler\Crawler($this->result);
        $value = trim($crawler->filter($selector)->text());

        if ($value != $expectedText) {
            throw new Exception("`$value`` does not equal $expectedText");
        }
    }
}
