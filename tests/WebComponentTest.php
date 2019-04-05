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
    $this->assertInstanceOf(WebComponent::class, new WebComponent($str, 'Button', []));
  }

  public function testInnerHTML(): void
  {
    $innerHtml = 'asdasd<a href="aaaaaa">Algo</a>';
    $tag = '<Button>' . $innerHtml . '</Button>';
    $wb = new WebComponent($tag, 'Button', []);
    $this->assertEquals($innerHtml, $wb->getInnerHtml());
  }

  /** @test */
  public function should_preserve_innerhtml_original_casing(): void
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

  public function testGetSimpleChildren(): void
  {
    $tag = '<Button>
                    <A href="gugal"></A>
                </Button>';

    $component = new WebComponent($tag, 'Button', []);

    $expected_child = new WebComponent('<A href="gugal"></A>', 'A', ['href' => "gugal"]);

    $this->assertEquals($expected_child, $component->getChildren()[0]);
  }

  public function testEmptyComponentsIsRebuiltAsSelfClosing(): void
  {
    $empty_tag = '<Alert></Alert>';
    $wc = new WebComponent($empty_tag, 'Alert', []);
    $this->assertEquals('', $wc->getInnerHtml());
  }

  public function testCreateXmlComponent()
  {
    $xml = '<Button><a href="google.com" target="_blank">Pincha AQUI</a></Button>';
    $tag = '<Button>
                    <a href="google.com" target="_blank">Pincha AQUI</a>
                </Button>';
    $wc = new WebComponent($tag, 'Button', []);
    $this->assertEquals($xml, $wc->__toString());
  }

  public function testGetCreateChilds(): void
  {
    $childWc = '<Icon src="www.google.com">
                        <Img>ole</Img>
                        AAAA
                    </Icon>';
    $tag = '<Button>
                    <a href="google.com" target="_blank">Pincha AQUI</a>
                    ' . $childWc . '
                </Button>';

    $component = new WebComponent($childWc, 'Icon', ['src' => "www.google.com"]);

    $expected_child = new WebComponent($tag, 'Button', []);

    $this->assertEquals($component, $expected_child->getChildren()[1]);
  }

  public function testAttr(): void
  {
    $tag = '<Button src="dummy" require="true">
                    <a href="google.com" target="_blank">Pincha AQUI</a>
                    <Icon src="www.google.com">
                        <Img>ole</Img>
                        AAAA
                    </Icon>
                </Button>';
    $wc = new WebComponent($tag, 'Button', ['src' => "dummy", 'require' => "true"]);
    $this->assertEquals(["src" => "dummy", "require" => "true"], $wc->getAttr());
  }

  /**
   * @expectedException Gang\WebComponents\Exceptions\ParserException
   */
  public function testTextWebcomponent(): void
  {
    new WebComponent("hola guapi", '', []);
  }

  public function testShouldNotEscapeAccentsWhenCastingToString(): void
  {
    $input = "<TableHeaderCell>Teléfono</TableHeaderCell>";
    $tag = new WebComponent($input, 'TableHeaderCell', []);
    $result = (string)$tag;

    $this->assertEquals($input, $result);
  }

  public function testShouldReturnSpecialCharsOnChildren(): void
  {
    $input = "<Fragment>áéíóúÜüïÇç\"¡?¿!`+*-@#~½¬%ªº=/&</Fragment>";
    $tag = new WebComponent($input, 'Fragment', []);

    $this->assertEquals("áéíóúÜüïÇç\"¡?¿!`+*-@#~½¬%ªº=/&", $tag->getInnerHtml());
  }


  public function testCreateWebComponentWithChildrens()
  {
    $button = "<Button><A href='sdferfer'></A></Button>";

    $wc = new WebComponent($button, 'Button', []);

    $this->assertEquals($button, $wc->__toString());
  }

  public function testGetChildrensWithJumpLines()
  {
    $element1 = '<A href="sdferfer">1</A>';
    $element2 = '<A href="sdferfer">2</A>';
    $element3 = '<A href="sdferfer">3</A>';
    $element4 = '<A href="sdferfer">4</A>';
    $element5 = '<A href="sdferfer">5</A>';
    $button = "<Button> 
                        $element1 
                        $element2 
                        $element3 
                        $element4 
                        $element5
                    </Button>";

    $wc = new WebComponent($button, 'Button', []);
    $this->assertEquals($element1, $wc->getChildren()[0]->__toString());
    $this->assertEquals($element2, $wc->getChildren()[1]->__toString());
    $this->assertEquals($element3, $wc->getChildren()[2]->__toString());
    $this->assertEquals($element4, $wc->getChildren()[3]->__toString());
    $this->assertEquals($element5, $wc->getChildren()[4]->__toString());
  }

  public function testGetChildrenNested()
  {
    $element = '  <A> 
                            retgrt
                            <B/> 
                            <C>klnerfvcknl    <Cd> <X/> rtbrtb</Cd> </C>
                        </A>  ';
    $button = "   <Button> 


                        $element 
                    </Button>   ";

    $expected_child = new WebComponent($element, 'A', []);
    $wc = new WebComponent($button, 'Button', []);

    $this->assertEquals(true, $expected_child == $wc->getChildren()[0]);
  }
}
