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

  public function testOnlyHtml(){
    $html = "<html><body>Hola</body></html>";
    $parser = $this->parser->parse($html);

    $this->assertEquals(new Fragment("<html><body>Hola</body></html>"), $parser[0]);
  }

  public function testOnlyOneWebComponent(){
    $html = "<Div></Div>";
    $parser = $this->parser->parse($html);
    $webcomponent = new WebComponent("Div", []);
    $webcomponent->setOuterHtml($html);
    $this->assertEquals($webcomponent, $parser[0]);
  }

  public function testOnlyOneWebComponentWithContentInside(){
    $html = "<Div>a content</Div>";
    $parser = $this->parser->parse($html);
    $webcomponent = new WebComponent("Div", []);
    $webcomponent->setOuterHtml($html);
    $webcomponent->setChildren(new Fragment("a content"));
    $webcomponent->setInnerHtml("a content");

    $this->assertEquals($webcomponent, $parser[0]);
  }


  public function testTwoWebComponents(){
    $html = "<Component></Component><OtherCOmponent></OtherCOmponent>";
    $parser = $this->parser->parse($html);
    $webcomponent = new WebComponent("Component", []);
    $webcomponent->setOuterHtml("<Component></Component>");

    $webcomponent1 = new WebComponent("OtherCOmponent", []);
    $webcomponent1->setOuterHtml("<OtherCOmponent><OtherCOmponent/>");

    dd($parser);

    $this->assertEquals([$webcomponent,$webcomponent1], $parser);
  }

}
