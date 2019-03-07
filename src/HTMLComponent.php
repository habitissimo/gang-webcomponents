<?php
declare(strict_types=1);
namespace Gang\WebComponents;

use Gang\WebComponents\Exceptions\ComponentAttributeNotFound;
use Gang\WebComponents\Helpers\Str;
use Gang\WebComponents\Logger\WebComponentLogger as Log;

abstract class HTMLComponent
{
    protected $template;
    public $children; // contains innerHTML. It is maintained this way for retrocompatibility of the templates
    public $webcomponent_children = []; // parsed webcomponent children.
    public $className;
    protected $required_fields = [];
    protected $attributes = [];

    public function __set($name, $value) : void
    {
        $method_name = 'set' . ucfirst($name);

        if (method_exists($this, $method_name)) {
            $this->{$method_name}($value);
            return;
        }

        $attr_name = Str::snake($name);
        $this->{$attr_name} = $value;
        return;

        Log::error("Component ".$this->getClassName()." has no public attribute $name ".
            " nor public method set".ucfirst($name));
        throw new ComponentAttributeNotFound($name, $this->getClassName());
    }

    public function getClassName()
    {
        return self::__CLASS__;
    }

    public function setInnerHTML(string $html)
    {
        $this->children = $html;
    }

    public function getInnerHTML() : string
    {
        return $this->children ?? "";
    }

    public function addWebComponentChild($child)
    {
        $this->webcomponent_children[] = $child;
    }

    public function getWebComponentChildren() : array
    {
        return $this->webcomponent_children;
    }

    public function preRender() : void
    {
    }
}
