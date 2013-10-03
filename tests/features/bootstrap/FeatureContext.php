<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Behat\Event\SuiteEvent;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Optimus\Adapter\AdapterInterface;

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Pid for the web server
     *
     * @var int
     */
    private static $webServerProcess;

    /**
     * Pid for the phantom server
     *
     * @var int
     */
    private static $phantomServerProcess;

    /**
     * @var int
     */
    public static $webServerPort;

    /**
     * @var int
     */
    public static $phantomServerPort;

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
     * Start up the web server
     *
     * @BeforeSuite
     */
    public static function setUp(SuiteEvent $event)
    {
        $params = $event->getContextParameters();
        $url = parse_url($params['url']);
        self::$webServerPort = !empty($url['port']) ? $url['port'] : 80;
        self::$phantomServerPort = $params['phantom_port'];

        self::$webServerProcess = self::setUpWebServer($event);
        self::$phantomServerProcess = self::setUpPhantomServer($event);
    }

    /**
     * Kill the httpd process if it has been started when the tests have finished
     *
     * @AfterSuite
     */
    public static function tearDown(SuiteEvent $event) {
        if (self::$webServerProcess) {
            self::killProcess(self::$webServerProcess);
        }
        if (self::$phantomServerProcess) {
            self::killProcess(self::$phantomServerProcess);
        }
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
     * @Given /^The following markup:$/
     */
    public function theFollowingMarkup(PyStringNode $html)
    {
        // We can safely assume that we're using the HTML adapter
        $this->iHaveAnAdapter('html');
        $this->adapterHtml = $html;
    }

    /**
     * @Given /^I apply the "([^"]*)" transformer to "([^"]*)" nodes$/
     */
    public function iApplyTheTransformerToNodes($transformerName, $selector)
    {
        $transformer = $this->getTransformer($transformerName);

        $this->dispatcher->addTransformer($selector, $transformer);
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
                $url = sprintf('localhost:%d/%s', self::$webServerPort, $this->adapterUrl);
                $this->adapter = new \Optimus\Adapter\PhantomAdapter('localhost', self::$phantomServerPort, $url);
                break;

            case 'guzzle':
                $client = new \Guzzle\Http\Client('', array(
                    'curl.options' => array(
                        CURLOPT_PORT => self::$webServerPort
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

    /**
     * @Then /^"([^"]*)" should have the class "([^"]*)"$/
     */
    public function shouldHaveTheClass($selector, $arg2)
    {
        $crawler = new \Symfony\Component\DomCrawler\Crawler($this->result);
        $class = $crawler->filter($selector)->attr('class');

        if (strpos($class, $arg2) === false) {
            throw new Exception(sprintf('Expected class `%s` does not match `%s`', $arg2, $class));
        }
    }

    /**
     * @param Optimus\Transformer\BaseTransformer
     * @return mixed
     */
    private function getTransformer($transformerName)
    {
        $className = 'Optimus\\Transformer\\' . str_replace(' ', '', ucwords($transformerName)) . 'Transformer';

        return new $className;
    }

    /**
     * @param SuiteEvent $event
     * @return int
     * @throws RuntimeException
     */
    private static function setUpWebServer(SuiteEvent $event)
    {
        // Fetch config
        $params = $event->getContextParameters();
        $url = parse_url($params['url']);
        $port = !empty($url['port']) ? $url['port'] : 80;

        if (self::canConnectToHttpd($url['host'], $port)) {
            throw new RuntimeException('Something is already running on ' . $params['url'] . '. Aborting tests.');
        }

        // Try to start the web server
        $pid = self::startBuiltInHttpd(
            $url['host'],
            $port,
            $params['documentRoot']
        );

        if (!$pid) {
            throw new RuntimeException('Could not start the web server');
        }

        $start = microtime(true);
        $connected = false;

        // Try to connect until the time spent exceeds the timeout specified in the configuration
        while (microtime(true) - $start <= (int) $params['timeout']) {
            if (self::canConnectToHttpd($url['host'], $port)) {
                $connected = true;
                break;
            }
        }

        if (!$connected) {
            self::killProcess($pid);
            throw new RuntimeException(
                sprintf(
                    'Could not connect to the web server within the given timeframe (%d second(s))',
                    $params['timeout']
                )
            );
        }

        return $pid;
    }

    /**
     * @param SuiteEvent $event
     * @return int
     * @throws RuntimeException
     */
    private static function setUpPhantomServer(SuiteEvent $event)
    {
        // Fetch config
        $params = $event->getContextParameters();
        $port = $params['phantom_port'];

        if (self::canConnectToHttpd('localhost', $port)) {
            throw new RuntimeException('Something is already running on port ' . $port . '. Aborting tests.');
        }

        // Try to start the phantom server
        $pid = self::startPhantom($port);

        if (!$pid) {
            throw new RuntimeException('Could not start the phantom server');
        }

        $start = microtime(true);
        $connected = false;

        // Try to connect until the time spent exceeds the timeout specified in the configuration
        while (microtime(true) - $start <= (int) $params['timeout']) {
            if (self::canConnectToHttpd('localhost', $port)) {
                $connected = true;
                break;
            }
        }

        if (!$connected) {
            self::killProcess($pid);
            throw new RuntimeException(
                sprintf(
                    'Could not connect to the phantom server within the given timeframe (%d second(s))',
                    $params['timeout']
                )
            );
        }

        return $pid;
    }

    /**
     * Kill a process
     *
     * @param int $pid
     */
    private static function killProcess($pid) {
        exec('kill ' . (int) $pid);
    }

    /**
     * See if we can connect to the httpd
     *
     * @param string $host The hostname to connect to
     * @param int $port The port to use
     * @return boolean
     */
    private static function canConnectToHttpd($host, $port) {
        // Disable error handler for now
        set_error_handler(function() { return true; });

        // Try to open a connection
        $sp = fsockopen($host, $port);

        // Restore the handler
        restore_error_handler();

        if ($sp === false) {
            return false;
        }

        fclose($sp);

        return true;
    }

    /**
     * Start the built in httpd
     *
     * @param string $host The hostname to use
     * @param int $port The port to use
     * @param string $documentRoot The document root
     * @return int Returns the PID of the httpd
     */
    private static function startBuiltInHttpd($host, $port, $documentRoot) {
        // Build the command
        $command = sprintf('php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
            $host,
            $port,
            $documentRoot);

        $output = array();
        exec($command, $output);

        return (int) $output[0];
    }

    /**
     * Start the phantomjs server
     *
     * @param int $port The port to use
     * @return int Returns the PID
     */
    private static function startPhantom($port) {
        // Build the command
        $command = sprintf('phantomjs phantomserver.js %d  >/dev/null & echo $!',
            $port
        );

        $output = array();
        exec($command, $output);

        return (int) $output[0];
    }
}
