<?php

namespace Gang\WebComponentsTests;

use NewParserTests;
use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\NewParser;
use Gang\WebComponents\Parser\Nodes\WebComponent;

class NewParserTest extends TestCase
{
  /**
   * @var NewParser
   */
  private $parser;

  protected function setUp()
  {
    $this->parser = new NewParser();
  }

  public function testonlyWebComponent(){
    $button = "<Div><Button><Div href='es un div'>Hola</Div></Button></Div>";

    dd($this->parser->parse($button));

  }


}
