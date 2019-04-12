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
    $webcomponent = new WebComponent("Div", [], false);
    $webcomponent->closeTag("Div");
    $this->assertEquals($webcomponent, $parser[0]);
  }

  public function testOnlyOneWebComponentWithAttr(){
    $html = '<Div clickable="true"></Div>';
    $parser = $this->parser->parse($html);

    $this->assertEquals(["clickable"=>"true"], $parser[0]->getAttr());
  }

  public function testOnlyOneWebComponentWithContentInside(){
    $html = "<Div>a content</Div>";
    $parser = $this->parser->parse($html);

    $webcomponent = new WebComponent("Div", [], false);
    $webcomponent->appendChild(new Fragment("a content"));
    $webcomponent->setOuterHtml($html);

    $this->assertEquals($webcomponent, $parser[0]);
  }


  public function testTwoWebComponents(){
    $html = "<Component></Component><OtherCOmponent></OtherCOmponent>";
    $parser = $this->parser->parse($html);
    $webcomponent = new WebComponent("Component", [], false);

    $webcomponent1 = new WebComponent("OtherCOmponent", [], false);

    $this->assertEquals([$webcomponent,$webcomponent1], $parser);
  }

  public function testComponentSelfClosed(){
    $html = "<Img/>";
    $parser = $this->parser->parse($html);
    $webcomponent = new WebComponent("Img", []);
    $webcomponent->setOuterHtml("<Img/>");

    $this->assertEquals($webcomponent, $parser[0]);
  }

  public function testDataOutsideTagsIsNotLost() : void
  {
    $this->assertEquals(
      [new Fragment("<a>asdf</a> this will be lost")],
      $this->parser->parse("<a>asdf</a> this will be lost")
    );
  }
  public function testDataOutsideTagsIsLostUnlessWrappedByHTML() : void
  {
    $input = "<html><a>asdf</a>this will not be lost</html>";
    $this->assertEquals(
      [new Fragment($input)],
      $this->parser->parse($input)
    );
  }

  public function testNumOfFragments() : void
  {

    $webcomponet =   new WebComponent("Button", []);
    $webcomponet->setOuterHtml("<Button></Button>");

    $this->assertEquals([
      new Fragment("<html><a>asasasasas</a>asdasdasdasdasd>"),
      $webcomponet,
      new Fragment("<a>asasasasas</a>asdasdasdasdasd></html>"),
    ], $this->parser->parse("<html><a>asasasasas</a>asdasdasdasdasd><Button></Button><a>asasasasas</a>asdasdasdasdasd></html>"));
  }

  public function testComplexWebcomponent() : void
  {
    $iterator_array = $this->parser->parse('<Alert type="error">Error!!<a href="google.com"><Button>Close</Button></a></Alert>');
    $this->assertEquals(1, count($iterator_array));
  }

  public function testGroupNoWebComponentTokensIntoFragments(): void
  {
    $input = '<html><p>holaquetal</p><img src="ssdas"/><Alert type="error"></Alert></html>';

    $webcomponent = new WebComponent( "Alert", ["type" => "error"]);
    $webcomponent->setOuterHtml("<Alert type=\"error\"></Alert>");
    $this->assertEquals([
      new Fragment('<html><p>holaquetal</p><img src="ssdas"/>'),
      $webcomponent,
      new Fragment('</html>'),
    ], $this->parser->parse($input));
  }

  public function testInnerSelfClosingWebComponent(): void
  {
    $inner_wc = "<Alert><p>texto alerta</p><Icon/></Alert>";

    $webcomponent = new WebComponent("Alert", []);
    $selfClose = new WebComponent("Icon",[]);
    $webcomponent->setInnerHtml("<p>texto alerta</p><Icon/>");
    $webcomponent->appendChild(new Fragment("<p>texto alerta</p>"));
    $webcomponent->appendChild($selfClose);

    $webcomponent->setOuterHtml($inner_wc);
    $selfClose->setOuterHtml("<Icon/>");

    $this->assertEquals([
      $webcomponent
    ], $this->parser->parse($inner_wc));
  }

  public function testFragmentComponentFragmentComponent() : void
  {
    $text = '<html><Alert></Alert><br/><Input></Input>';

    $alert =  new WebComponent( "Alert", []);
    $alert->setOuterHtml("<Alert></Alert>");

    $input = (new Webcomponent( "Input", []));
    $input->setOuterHtml('<Input></Input>');
    $this->assertEquals([
      new Fragment('<html>'),
     $alert,
      new Fragment('<br/>'),
      $input
    ], $this->parser->parse($text));
  }


  public function testHtmlInComponentAttribute() : void
  {
    $input = '<Alert content="<b>Hello</b>"/><html>';

    $webcomponent =  new WebComponent( "Alert", ["content" => "<b>Hello</b>"]);
    $webcomponent->setOuterHtml('<Alert content="<b>Hello</b>"/>');
    $this->assertEquals([
      $webcomponent,
      new Fragment('<html>')
    ], $this->parser->parse($input));
  }

  public function testNestedChildren() : void
  {
    $inputText = '<Html><Div><Button></Button></Div><Div><Aside><Button></Button></Aside></Div></Html>';

    $Html =  new WebComponent( "Html", []);
    $Html->setOuterHtml($inputText);
    $Html->setInnerHtml('<Div><Button></Button></Div><Div><Aside><Button></Button></Aside></Div>');

    $Div_1 =  new WebComponent( "Div", []);
    $Div_1->setOuterHtml('<Div><Button></Button></Div>');
    $Div_1->setInnerHtml('<Button></Button>');

    $Button_1 =  new WebComponent( "Button", []);
    $Button_1->setOuterHtml('<Button></Button>');


    $Div_2 =  new WebComponent( "Div", []);
    $Div_2->setOuterHtml('<Div><Aside><Button></Button></Aside></Div>');
    $Div_2->setInnerHtml('<Aside><Button></Button></Aside>');


    $Aside =  new WebComponent( "Aside", []);
    $Aside->setOuterHtml('<Aside><Button></Button></Aside>');
    $Aside->setInnerHtml('<Button></Button>');


    $Button_2 =  new WebComponent( "Button", []);
    $Button_2->setOuterHtml('<Button></Button>');

    $Aside->appendChild($Button_2);
    $Div_2->appendChild($Aside);

    $Div_1->appendChild($Button_1);

    $Html->appendChild($Div_1);
    $Html->appendChild($Div_2);


    $this->assertEquals([
      $Html,
    ], $this->parser->parse($inputText));
  }


}
