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
        $class_name = $wc->getTagName();
        $attributes = $wc->getAttr();
        $wc_children = $wc->getWebComponentChildren();
        Log::info("[Factory@create] Converting WebComponent <$class_name>".$wc->getInnerHtml()."</$class_name>To $class_name object");
        $class = $this->library->getComponentClass($class_name); //returns instantiable class

        $component = new $class();

        foreach ($attributes as $attr_name => $value) {
            $component->__set($attr_name, $value);
        }

        foreach ($wc_children as $wc_child) {
            $component->addWebComponentChild($wc_child);
        }

        if ($wc->getInnerHtml()) {
            $component-> setInnerHTML($wc->getInnerHtml());
        }

        // PreRender is a class-specific method
        $component->preRender();

        Log::debug("[Factory@create] $class_name created with children: ".$component->getInnerHTML());

        return $component;
    }
}
