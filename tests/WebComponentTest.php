<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Parser\Nodes\WebComponent;

final class WebComponentTests extends TestCase
{
    public function testWebComponentFactory()
    {
        $str = '<Button>aaaa</Button>';
        $this->assertInstanceOf(WebComponent::class, WebComponent::create($str, 'Button', []));
    }

    public function testInnerHTML() : void
    {
        $innerHtml = '
                    asdasd
                    <a href="aaaaaa">Algo</a>
                ';
        $tag = '<Button>' . $innerHtml . '</Button>';
        $this->assertEquals($innerHtml, WebComponent::create($tag, 'Button', [])->getInnerHtml());
    }

    /** @test */
    public function should_preserve_innerhtml_original_casing() : void
    {
        $input = "<Fragment><Button>Hi</Button></Fragment>";
        $tag = new WebComponent($input, 'Fragment', []);

        $this->assertEquals("<Button>Hi</Button>", $tag->getInnerHtml());
    }

    public function testCompareTo()
    {
        // DOC: https://stackoverflow.com/questions/17008622/is-there-a-equals-method-in-php-like-there-is-in-java
        $tag = '<Button>
                    <A></A>
                </Button>';
        $other = '<Button>
                    <B></B>
                </Button>';
        $wc1 = new WebComponent($tag, 'Button', []);
        $wc2 = new WebComponent($tag, 'Button', []);
        $wc3 = new WebComponent($other, 'Button', []);
        // Important the Objects in PHP must be compare with == not ===
        $this->assertEquals(true, $wc1 == $wc2);
        $this->assertEquals(false, $wc1 == $wc3);
    }

    public function testGetSimpleChildren() : void
    {
        $tag = '<Button>
                    <A href="gugal"></A>
                </Button>';
        $this->assertEquals(
            new WebComponent('<A href="gugal"></A>', 'A', ['href'=>"gugal"]),
            WebComponent::create($tag, 'Button', [])->getChildren()[0]
        );
    }

    public function testEmptyComponentsIsRebuiltAsSelfClosing() : void
    {
        $empty_tag = '<Alert></Alert>';
        $this->assertEquals(
            '',
            WebComponent::create($empty_tag, 'Alert', [])->getInnerHtml()
        );
    }

    public function testCreateXmlComponent()
    {
        $xml = '<Button>
                    <a href="google.com" target="_blank">Pincha AQUI</a>
                </Button>';
        $tag = '<Button>
                    <a href="google.com" target="_blank">Pincha AQUI</a>
                </Button>';
        $this->assertEquals($xml, WebComponent::create($tag, 'Button', [])->__toString());
    }

    public function testGetCreateChilds() : void
    {
        $childWc = '<Icon src="www.google.com">
                        <Img>ole</Img>
                        AAAA
                    </Icon>';
        $tag = '<Button>
                    <a href="google.com" target="_blank">Pincha AQUI</a>
                    ' . $childWc . '
                </Button>';
        $this->assertEquals(
            WebComponent::create($childWc, 'Icon', ['src'=>"www.google.com"]),
            WebComponent::create($tag, 'Button', [])->getChildren()[0]
        );
    }

    public function testAttr() : void
    {
        $tag = '<Button src="dummy" require="true">
                    <a href="google.com" target="_blank">Pincha AQUI</a>
                    <Icon src="www.google.com">
                        <Img>ole</Img>
                        AAAA
                    </Icon>
                </Button>';
        $this->assertEquals(["src" =>"dummy", "require" => "true"], WebComponent::create($tag, 'Button', ['src'=>"dummy", 'require'=>"true"])->getAttr());
    }

    /**
     * @expectedException Gang\WebComponents\Exceptions\ParserException
     */
    public function testTextWebcomponent() : void
    {
        new WebComponent("hola guapi", '', []);
    }

    public function testShouldNotEscapeAccentsWhenCastingToString() : void
    {
        $input = "<TableHeaderCell>Teléfono</TableHeaderCell>\n";
        $tag = new WebComponent($input, 'TableHeaderCell', []);
        $result = (string) $tag;

        $this->assertEquals($input, $result);
    }

    public function testShouldReturnSpecialCharsOnChildren() : void
    {
        $input = "<Fragment>áéíóúÜüïÇç\"¡?¿!`+*-@#~½¬%ªº=/&</Fragment>";
        $tag = new WebComponent($input,'Fragment',[]);

        $this->assertEquals("áéíóúÜüïÇç\"¡?¿!`+*-@#~½¬%ªº=/&", $tag->getInnerHtml());
    }
}
