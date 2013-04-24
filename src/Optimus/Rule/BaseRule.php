<?php

namespace Optimus\Rule;

abstract class BaseRule implements RuleInterface
{
    /**
     * @param \DOMElement $node
     * @return bool
     */
    public function isLastSiblingElement(\DOMElement $node)
    {
        $sibling = $node->nextSibling;
        while ($sibling) {
            switch ($sibling->nodeType) {
                case XML_ELEMENT_NODE:
                    return false;

                default:
                    $sibling = $sibling->nextSibling;
            }
        }

        return true;
    }
}