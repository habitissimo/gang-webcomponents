<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Gang\WebComponents\Parser\Nodes\Fragment;
use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Parser\Parser;
use Gang\WebComponents\Parser\Nodes\WebComponent;

final class ParserTests extends TestCase
{
    private $parser;

    public function setUp()
    {
        $this->parser = new Parser();
    }

    public function testParseFakeHTML() : void
    {
        $input = "soy html";
        $this->assertEquals(
            [new Fragment($input)],
            $this->parser->parse($input)
        );
    }

    /** @test */
    public function should_xxx() : void
    {
        $res = $this->parser->parse("<As></As>");

        $this->assertEquals(
            new WebComponent("<As></As>", "As", []),
            $res[0]);
    }

    public function testParseComponent() : void
    {
        $this->assertInstanceOf(
            WebComponent::class,
            $this->parser->parse("<Button/>")[0]
        );
    }

    public function testTwoTokensOneComponent() : void
    {
        $this->assertEquals(1, count($this->parser->parse("<Fragment></Fragment>")));
    }

    public function testParseVoidElement() : void
    {
        $result = $this->parser->parse("<img src=\"a_source\">");
        $this->assertEquals([
            new Fragment("<img src=\"a_source\">"),
        ], $result);
    }

    public function testParseSelfClosingComponent() : void
    {
        $result = $this->parser->parse("<Button/>");
        $this->assertEquals([
            new WebComponent("<Button/>", "Button", []),
        ], $result);
    }

    public function testParseOpeningOnlyComponentException() : void
    {
        $this->assertEquals([
            new Fragment("<Button><Alert><Tag>")
        ], $this->parser->parse("<Button><Alert><Tag>"));
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
        $input = "<html><a>asdf</a> this will not be lost</html>";
        $this->assertEquals(
            [new Fragment($input)],
            $this->parser->parse($input)
        );
    }

    public function testNumOfFragments() : void
    {
        $this->assertEquals([
            new Fragment("<html><a>asasasasas</a>asdasdasdasdasd>"),
            new WebComponent("<Button></Button>", "Button", []),
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
        $input = '<html><p>holaquetal</p>  <img src="ssdas"/><Alert type="error">errro!</Alert></html>';
        $this->assertEquals([
            new Fragment('<html><p>holaquetal</p>  <img src="ssdas"/>'),
            new WebComponent('<Alert type="error">errro!</Alert>', "Alert", ["type" => "error"]),
            new Fragment('</html>'),
        ], $this->parser->parse($input));
    }

    public function testInnerSelfClosingWebComponent(): void
    {
        $inner_wc = "<Alert><p>texto alerta</p><Icon/></Alert>";
        $this->assertEquals([
            new WebComponent($inner_wc, "Alert", [])
        ], $this->parser->parse($inner_wc));
    }

    public function testIndentedHTML(): void
    {
        $input = '
    <Tabs>
        <Tab><div>Contenido Tab1</div></Tab>
        <Tab>Algo que estará oculto</Tab>
    </Tabs>';

        $this->assertEquals([
            new Fragment("\n    "),
            new WebComponent(trim($input), "Tabs", []),
        ], $this->parser->parse($input));
    }


    public function testWebcomponentDepth() : void
    {
        $input = '<html><Button><Alert><Button></Button></Alert></Button><some/><html/><tags/><WebComponent></WebComponent></html>';
        $this->assertEquals([
            new Fragment('<html>'),
            new WebComponent('<Button><Alert><Button></Button></Alert></Button>', "Button", []),
            new Fragment('<some/><html/><tags/>'),
            new WebComponent('<WebComponent></WebComponent>', "WebComponent", []),
            new Fragment('</html>')
            ], $this->parser->parse($input));
    }

    public function testFragmentComponentFragmentComponent() : void
    {
        $input = '<html><Alert>hola</Alert><br/><Input></Input>';
        $this->assertEquals([
            new Fragment('<html>'),
            new WebComponent('<Alert>hola</Alert>', "Alert", []),
            new Fragment('<br/>'),
            new Webcomponent('<Input></Input>', "Input", [])
        ], $this->parser->parse($input));
    }

    public function testHtmlInComponentAttribute() : void
    {
      $input = '<Alert content="<b>Hello</b>"/><html>';
      $this->assertEquals([
        new WebComponent('<Alert content="<b>Hello</b>"/>', "Alert", ["content" => "<b>hello</b>"]),
      ], $this->parser->parse($input));
    }
}
