<?php

namespace Gang\WebComponentsTests\WebComponents\Div;

use Gang\WebComponents\HTMLComponent;

class Div extends HTMLComponent
{
  public function getTemplate(): string
  {
    return __DIR__ . DIRECTORY_SEPARATOR . 'Div.twig';
  }
}
