<?php

namespace Optimus\Adapter;

/**
 * Simple adapter that allows the input of raw HTML
 *
 * Class HTMLAdapter
 * @package Optimus\Adapter
 */
class HTMLAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected $html;

    /**
     * @var array
     */
    protected $tidyConfig = array();

    /**
     * @param string $html
     * @param array $tidyConfig
     */
    function __construct($html = '', $tidyConfig = array())
    {
        $this->html = $html;
        $this->tidyConfig = $tidyConfig;
    }

    /**
     * @return \DOMDocument
     */
    public function getDocument()
    {
        $document = new \DOMDocument();
        $document->loadHTML(tidy_repair_string($this->html, $this->tidyConfig));

        return $document;
    }

    /**
     * Setter for classes that extend this
     *
     * @param $html
     */
    protected function setHtml($html)
    {
        $this->html = $html;
    }
}