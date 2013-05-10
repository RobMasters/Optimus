<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Optimus\Adapter\AdapterInterface;

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
     * @var string
     */
    protected $adapterType;

    /**
     * @var string
     */
    protected $adapterUrl;

    /**
     * @var string
     */
    protected $adapterHtml;

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
    public function iHaveAnAdapter($adapterType)
    {
        $adapterType = strtolower($adapterType);

        switch ($adapterType) {
            case 'html':
            case 'phantom':
            case 'guzzle':
                $this->adapterType = $adapterType;
                break;

            default:
                throw new \Exception("Uknown adapter, `$adapterType`");
        }
    }

    /**
     * @Given /^I am requesting "([^"]*)"$/
     */
    public function iAmRequesting($arg1)
    {
        $this->adapterUrl = $arg1;
    }

    /**
     * @When /^I transcode the page$/
     */
    public function iTranscodeThePage()
    {
        $transcoder = new \Optimus\Transcoder($this->dispatcher);

        switch ($this->adapterType) {
            case 'html':
                $this->adapter = new \Optimus\Adapter\HTMLAdapter($this->adapterHtml);
                break;

            case 'phantom':
                $url = sprintf('localhost:%d/%s', self::WEB_SERVER_PORT, $this->adapterUrl);
                $this->adapter = new \Optimus\Adapter\PhantomAdapter('localhost', self::PHANTOM_PORT, $url);
                break;

            case 'guzzle':
                $client = new \Guzzle\Http\Client('', array(
                    'curl.options' => array(
                        CURLOPT_PORT => self::WEB_SERVER_PORT
                    )
                ));
                $client->setEventDispatcher($this->dispatcher);
                $this->adapter = new \Optimus\Adapter\GuzzleAdapter($client, sprintf('http://localhost/%s', $this->adapterUrl));
                break;
        }

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

    /**
     * @Then /^"([^"]*)" should not exist$/
     */
    public function shouldNotExist($selector)
    {
        $crawler = new \Symfony\Component\DomCrawler\Crawler($this->result);
        if ($crawler->filter($selector)->count()) {
            throw new Exception("`$selector` should not match any elements");
        }
    }
}
