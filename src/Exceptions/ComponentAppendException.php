<?php

namespace Gang\WebComponents\Exceptions;

class ComponentAppendException extends \Exception
{
  public function __construct(string $fatherClassName, string $childClassName)
  {
    parent::__construct('The component ' . $fatherClassName . ' cannot have ' . $childClassName . ' as a children');
  }
}
