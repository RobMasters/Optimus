<?php

namespace Optimus\Adapter;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;

/**
 * Adapter to load content from a URL using the Guzzle library
 *
 * Class GuzzleAdapter
 * @package Optimus\Adapter
 */
class GuzzleAdapter extends HTMLAdapter
{
    /**
     * @var \Guzzle\Http\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param \Guzzle\Http\Client $client
     * @param $url
     */
    function __construct(Client $client, $url = '')
    {
        $this->client = $client;
        $this->url = $url;

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
        $request = $this->client->get($this->url);
        $this->response = $request->send();
        $this->setHtml($this->response->getBody(true));

        return parent::getDocument();
    }

    /**
     * @param bool $asObjects
     * @return \Guzzle\Common\Collection
     */
    public function getHeaders($asObjects = false)
    {
        return $this->response->getHeaders($asObjects);
    }

    /**
     * @param $header
     * @param bool $string
     * @return \Guzzle\Http\Message\Header|null|string
     */
    public function getHeader($header, $string = false)
    {
        return $this->response->getHeader($header, $string);
    }

    /**
     * @return null|string
     */
    public function getExpires()
    {
        return $this->response->getExpires();
    }
}