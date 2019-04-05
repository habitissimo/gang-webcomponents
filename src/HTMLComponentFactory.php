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

    public function create(WebComponent $wc) : HTMLComponent
    {
        $class = $this->library->getComponentClass($wc->getTagName());

        $component = new $class();
        $component->setWebComponent($wc);
        foreach ($wc->getAttr() as $attr_name => $value) {
            $component->__set($attr_name, $value);
        }

        foreach ($wc->getChildren() as $child) {
            if ($child instanceof WebComponent) {
                $component->addChild($child);
            }
        }

        if ($wc->getInnerHtml()) {
            $component->setInnerHTML($wc->getInnerHtml());
        }

        // PreRender is a class-specific method
        $component->preRender();

        return $component;
    }
}
