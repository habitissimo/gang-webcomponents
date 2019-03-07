<?php

namespace Gang\WebComponents\Exceptions;

class ComponentClassNotFound extends \Exception
{
  public function __construct(string $className)
  {
    parent::__construct('The class ' . $className . ' does not exist');
  }
}
