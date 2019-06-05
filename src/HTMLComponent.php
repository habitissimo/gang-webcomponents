<?php
declare(strict_types=1);

namespace Gang\WebComponents;

use Gang\WebComponents\Exceptions\ComponentAttributeNotFound;
use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\Parser\Nodes\WebComponent;

abstract class HTMLComponent
{
  protected $template;
  protected $required_fields = [];
  protected $attributes = [];

  public $childNodes = [];
  public $DOMElement;
  public $dataAttributes;
  public $innerHtml;
  public $class_name;

  public function __construct()
  {
    $this->dataAttributes = new AttributeHolder();
  }

  public function __set($name, $value): void
  {
    if ($this->isDataAttribute($name)) {
      $this->dataAttributes->add($name, $value);
      return;
    }

    if ($this->setterExists($name)) {
      $this->setWithSetter($name, $value);
      return;
    }

    if ($name === "classname") {
      $this->class_name = $value;
    }
    $this->{$name} = $value;

    return; // TODO: should we keep the validation here?

    throw new ComponentAttributeNotFound($name, $this->getClassName());
  }

  public function setDOMElement(\DOMNode $element)
  {
    $this->DOMElement = $element;
  }

  public function getClassName()
  {
    return self::__CLASS__;
  }

  public function getTagName()
  {

    return $this->DOMElement->nodeName;

  }

  public function getRequiredFields(): array
  {
    return $this->required_fields;
  }

  public function setInnerHtml(string $html)
  {
    $this->innerHtml = $html;
  }

  public function getInnerHTML(): string
  {
    return $this->innerHtml ?? "";
  }

  public function addChild($child)
  {
    $this->childNodes[] = $child;
  }

  public function preRender(): void
  {
    return;
  }

  private function setterExists($attrName)
  {
    return method_exists($this, $this->getSetterName($attrName));
  }

  private function getSetterName($attrName)
  {
    return 'set' . ucfirst($attrName);
  }

  private function setWithSetter($attrName, $value)
  {
    $setter = $this->getSetterName($attrName);
    $this->$setter($value);
  }

  private function isDataAttribute($attrName)
  {
    return strpos($attrName, "data-") === 0;
  }

  public function render($renderer, $dom , $factory, $logger)
  {
    foreach ($this->DOMElement->childNodes as $child) {
      if (Dom::isWebComponent($child)) {
        $HTMLComponentChild = $factory->create($child);
        if ($HTMLComponentChild->class_name) {
          $HTMLComponent_rendered = $HTMLComponentChild->render($renderer, $dom, $factory, $logger);
          $auxDom = Dom::domFromString($HTMLComponent_rendered, $logger);
          $renderer->addClassAtributesNotYetAdded($HTMLComponentChild->class_name, $auxDom->childNodes[1]);
          $this->innerHtml .= $auxDom->saveHTML($auxDom->childNodes[1]);
        } else {
          $this->innerHtml .= $HTMLComponentChild->render($renderer, $dom, $factory, $logger);
        }
      } else {
        $this->innerHtml .= html_entity_decode ($dom->saveHTML($child));
      }
    }

    return $renderer->render($this);
  }

  protected function remove_text_blanks($elements)
  {
    return array_values(array_filter(iterator_to_array($elements), array($this, 'remove_text_blanks_call_back')));
  }

  private function remove_text_blanks_call_back($element)
  {
    return !($element instanceof \DOMText && trim($element->data) === "");
  }

}
