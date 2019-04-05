<?php
declare(strict_types=1);
namespace Gang\WebComponents;

use Gang\WebComponents\Exceptions\ComponentAttributeNotFound;
use Gang\WebComponents\Helpers\Str;
use Gang\WebComponents\Logger\WebComponentLogger as Log;
use Gang\WebComponents\Parser\Nodes\WebComponent;

abstract class HTMLComponent
{
    protected $template;
    protected $required_fields = [];
    protected $attributes = [];
    protected $childNodes = [];
    protected $webComponent;

    public $dataAttributes;
    public $innerHtml;
    public $className;

    public function __construct()
    {
      $this->dataAttributes = new AttributeHolder();
    }

    public function __set($name, $value) : void
    {
        if ($this->isDataAttribute($name)) {
          $this->dataAttributes->add($name, $value);
          return;
        }

        if ($this->setterExists($name)) {
          $this->setWithSetter($name, $value);
          return;
        }

        $attr_name = Str::snake($name);
        $this->{$attr_name} = $value;


      return; // TODO: should we keep the validation here?

        throw new ComponentAttributeNotFound($name, $this->getClassName());
    }

    public function setWebComponent(WebComponent $webComponent)
    {
      $this->webComponent = $webComponent;
    }

    public function getClassName()
    {
        return self::__CLASS__;
    }

    public function setInnerHtml(string $html)
    {
        $this->innerHtml = $html;
    }

    public function getInnerHTML() : string
    {
        return $this->innerHtml ?? "";
    }

    public function addChild($child)
    {
        $this->childNodes[] = $child;
    }

    public function preRender() : void
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
}
