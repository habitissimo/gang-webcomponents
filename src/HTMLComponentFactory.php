<?php

namespace Gang\WebComponents;

use Gang\WebComponents\Logger\WebComponentLogger as Log;
use Gang\WebComponents\Parser\Nodes\WebComponent;

class HTMLComponentFactory
{
    private $library;

    public function __construct(ComponentLibrary $library)
    {
        $this->library = $library;
    }

    public function create(\DOMElement $element) : HTMLComponent
    {
        $class = $this->library->getComponentClass($element->nodeName);

        $component = new $class();

        $component->setDOMElement($element);


        foreach ($element->attributes as $attr) {
          $component->__set($attr->nodeName, $attr->nodeValue);
        }

        // PreRender is a class-specific method
        $component->preRender();

        return $component;
    }
}
