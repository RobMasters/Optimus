<?php

namespace Optimus\Adapter;

interface AdapterInterface
{
    /**
     * @return \DOMDocument
     */
    public function getDocument();
}