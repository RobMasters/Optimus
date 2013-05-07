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
     * @param $html
     */
    function __construct($html)
    {
        $this->html = $html;
    }

    /**
     * @return \DOMDocument
     */
    public function getDocument()
    {
        $document = new \DOMDocument();
        $document->loadHTML($this->html);

        return $document;
    }
}