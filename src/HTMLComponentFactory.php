<?php

namespace Gang\WebComponents;

use Gang\WebComponents\Parser\Nodes\WebComponent;
use Psr\Log\LoggerInterface;

class HTMLComponentFactory
{
  private $library;
  private $logger;

  public function __construct(ComponentLibrary $library, LoggerInterface $logger)
  {
    $this->library = $library;
    $this->logger =  $logger;
  }

  public function create(\DOMNode $element): HTMLComponent
  {
    $class = $this->library->getComponentClass($element->nodeName);

    $component = new $class();

    $component->setDOMElement($element);

    if ($component->getRequiredFields()) {
      $DOMElementAttr = array_keys(iterator_to_array($component->DOMElement->attributes));
      $required_fields = array_intersect($DOMElementAttr , $component->getRequiredFields());
      if(!empty(array_diff($component->getRequiredFields() , $required_fields) )){
        $this->logger->error("Webcomponent: " . $component->DOMElement->nodeName . " required this attributes: ( ".
          implode("|",$component->getRequiredFields()) . " ) found: ( " .implode("|", $DOMElementAttr). " )");
      };
    }


    foreach ($element->attributes as $attr) {
      $component->__set($attr->nodeName, $attr->nodeValue);
    }

    // PreRender is a class-specific method
    $component->preRender();

    return $component;
  }
}
