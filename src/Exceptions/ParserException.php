<?php

namespace Gang\WebComponents\Exceptions;

class ParserException extends \Exception
{
  public function __construct(string $message, string $content)
  {
    parent::__construct($message . ' - Trying to parser: ' . $content);
  }
}
