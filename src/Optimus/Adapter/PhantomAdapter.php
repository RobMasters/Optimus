<?php

namespace Optimus\Adapter;

/**
 * Adapter to load content from a URL using a phantomjs server
 *
 * Class PhantomAdapter
 * @package Optimus\Adapter
 */
class PhantomAdapter extends HTMLAdapter
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $host
     * @param $port
     * @param string $url
     */
    function __construct($host, $port, $url = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->url = $url;

        // TODO validate phantom server is running
    }

    /**
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return \DOMDocument
     */
    public function getDocument()
    {
        $source = $this->getSource();
        $this->setHtml($source);

        return parent::getDocument();
    }

    /**
     * @return mixed
     */
    protected function getSource()
    {
        $ch = curl_init(sprintf('http://%s:%s/%s', $this->host, $this->port, $this->url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);

        return $data['source'];
    }
}