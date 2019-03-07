<?php

namespace Gang\WebComponents\Exceptions;

class ComponentAttributeNotFound extends \Exception
{
  public function __construct(string $attributeName, string $componentName)
  {
    parent::__construct('The attribute ' . $attributeName . ' does not exist in ' . $componentName);
  }
}
