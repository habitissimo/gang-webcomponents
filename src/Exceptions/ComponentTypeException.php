<?php

namespace Gang\WebComponents\Exceptions;

class ComponentTypeException extends \Exception
{
  public function __construct($type, $component)
  {
    parent::__construct('The type ' . $type . ' is not compatible with ' . $component);
  }
}
