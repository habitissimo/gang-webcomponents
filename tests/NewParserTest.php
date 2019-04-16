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

  public function testScapeElements(){
    $html = '<!DOCTYPE html><!--This is a comment. Comments are not displayed in the browser-->';
    $parser = $this->parser->parse($html);

    $this->assertEquals(new Fragment("<!DOCTYPE html><!--This is a comment. Comments are not displayed in the browser-->"), $parser[0]);
  }

  public function testAttributeWithNullValue(){
    $html = '<script async></script>';

    $parser = $this->parser->parse($html);

    $this->assertEquals(new Fragment("<script async></script>"), $parser[0]);
  }

  public function testOnlyOneWebComponent(){
    $html = "<Div></Div>";
    $parser = $this->parser->parse($html);
    $webcomponent = new WebComponent("Div", [], false);
    $webcomponent->closeTag();
    $webcomponent->closeWebcomponent();

    $this->assertEquals($webcomponent, $parser[0]);
  }

  public function testOnlyOneWebComponentWithAttr(){
    $html = '<Div clickable="true"></Div>';
    $parser = $this->parser->parse($html);

    $this->assertEquals(["clickable"=>"true"], $parser[0]->getAttr());
  }

  public function testOnlyOneWebComponentWithContentInside(){
    $html = "<Div>a content</Div>";
    $webcomponent = new WebComponent("Div", [], false);
    $webcomponent->appendChild(new Fragment("a content"));
    $webcomponent->closeTag();
    $webcomponent->closeWebcomponent();

    $parser = $this->parser->parse($html);

    $this->assertEquals($webcomponent, $parser[0]);
  }


  public function testTwoWebComponents(){
    $html = "<Component></Component><OtherCOmponent></OtherCOmponent>";
    $parser = $this->parser->parse($html);
    $webcomponent = new WebComponent("Component", [], false);
    $webcomponent->closeTag();
    $webcomponent->closeWebcomponent();
    $webcomponent1 = new WebComponent("OtherCOmponent", [], false);
    $webcomponent1->closeTag();
    $webcomponent1->closeWebcomponent();
    $this->assertEquals([$webcomponent,$webcomponent1], $parser);
  }

  public function testComponentSelfClosed(){
    $html = "<Img/>";
    $parser = $this->parser->parse($html);
    $webcomponent = new WebComponent("Img", [], true);
    $webcomponent->closeWebcomponent();
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
    $webcomponet =   new WebComponent("Button", [], false);
    $webcomponet->closeTag();
    $webcomponet->closeWebcomponent();

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

    $webcomponent = new WebComponent( "Alert", ["type" => "error"], false);
    $webcomponent->closeTag();
    $webcomponent->closeWebcomponent();

    $this->assertEquals([
      new Fragment('<html><p>holaquetal</p><img src="ssdas"/>'),
      $webcomponent,
      new Fragment('</html>'),
    ], $this->parser->parse($input));
  }

  public function testInnerSelfClosingWebComponent(): void
  {
    $inner_wc = "<Alert><p>texto alerta</p><Icon/></Alert>";

    $webcomponent = new WebComponent("Alert", [], false);
    $selfClose = new WebComponent("Icon",[], true);
    $selfClose->closeWebcomponent();
    $webcomponent->appendChild(new Fragment("<p>texto alerta</p>"));
    $webcomponent->appendChild($selfClose);
    $webcomponent->closeTag();
    $webcomponent->closeWebcomponent();

    $this->assertEquals([
      $webcomponent
    ], $this->parser->parse($inner_wc));
  }

  public function testFragmentComponentFragmentComponent() : void
  {
    $text = '<html><Alert></Alert><br/><Input></Input>';

    $alert =  new WebComponent( "Alert", [], false);

    $input = (new Webcomponent( "Input", [], false));

    $alert->closeTag();
    $alert->closeWebcomponent();
    $input->closeTag();
    $input->closeWebcomponent();


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

    $webcomponent =  new WebComponent( "Alert", ["content" => "<b>Hello</b>"], true);
    $webcomponent->closeWebcomponent();
    $this->assertEquals([
      $webcomponent,
      new Fragment('<html>')
    ], $this->parser->parse($input));
  }

  public function testNestedChildren() : void
  {
    $inputText = '<Html><Div><Button></Button></Div><Div><Aside><Button></Button></Aside></Div></Html>';

    $Html =  new WebComponent( "Html", [], false);

    $Div_1 =  new WebComponent( "Div", [], false);

    $Button_1 =  new WebComponent( "Button", [], false);

    $Div_2 =  new WebComponent( "Div", [], false);

    $Aside =  new WebComponent( "Aside", [], false);

    $Button_2 =  new WebComponent( "Button", [], false);

    $Button_1->closeTag();
    $Button_1->closeWebcomponent();
    $Div_1->appendChild($Button_1);


    $Button_2->closeTag();
    $Button_2->closeWebcomponent();
    $Aside->appendChild($Button_2);
    $Aside->closeTag();
    $Aside->closeWebcomponent();
    $Div_2->appendChild($Aside);

    $Div_1->closeTag();
    $Div_1->closeWebcomponent();
    $Div_2->closeTag();
    $Div_2->closeWebcomponent();

    $Html->appendChild($Div_1);
    $Html->appendChild($Div_2);


    $Html->closeTag();
    $Html->closeWebcomponent();
    $this->parser->parse($inputText);

    $this->assertEquals([
      $Html,
    ], $this->parser->parse($inputText));
  }


}
