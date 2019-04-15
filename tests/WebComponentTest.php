<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Gang\WebComponents\Parser\Nodes\Fragment;
use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Parser\Nodes\WebComponent;

final class WebComponentTests extends TestCase
{
  public function testWebComponentFactory()
  {
    $this->assertInstanceOf(WebComponent::class, new WebComponent( 'Button', [], false));
  }

  public function testInnerHTML(): void
  {
    $innerHtml = 'asdasd<a href="aaaaaa">Algo</a>';
    $tag = new Fragment($innerHtml);
    $wb = new WebComponent('Button', [],false);
    $wb->appendChild($tag);
    $wb->closeTag();
    $this->assertEquals($innerHtml, $wb->getInnerHtml());
  }

  /** @test */
    public function should_preserve_innerhtml_original_casing(): void
  {
    $tag = new WebComponent( 'Fragment', [], false);

    $wb = new WebComponent('Button', [], false);
    $wb->appendChild(new Fragment('Hi'));
    $wb->closeTag();
    $tag->appendChild($wb);

    $this->assertEquals("<Button>Hi</Button>", $tag->getInnerHtml());
  }

  public function testCompareTo()
  {
    // DOC: https://stackoverflow.com/questions/17008622/is-there-a-equals-method-in-php-like-there-is-in-java

    $a= new WebComponent('A', [], false);
    $a->closeTag();

    $wc1 = new WebComponent( 'Button', [], false);
    $wc1->appendChild($a);
    $wc1->closeTag();

    $wc2 = new WebComponent( 'Button', [], false);
    $wc2->appendChild($a);
    $wc2->closeTag();

    $b= new WebComponent('B', [], false);
    $b->closeTag();

    $wc3 = new WebComponent( 'Button', [], false);
    $wc3->appendChild($b);
    $wc3->closeTag();

    // Important the Objects in PHP must be compare with == not ===
    $this->assertEquals(true, $wc1 == $wc2);
    $this->assertEquals(false, $wc1 == $wc3);
  }

  public function testGetSimpleChildren(): void
  {
    $component = new WebComponent('Button', [], false);

    $expected_child = new WebComponent( 'A', ['href' => "gugal"], false);
    $expected_child->closeTag();

    $component->appendChild($expected_child);

    $this->assertEquals($expected_child, $component->getChildren()[0]);
  }

  public function testEmptyComponentsIsRebuiltAsSelfClosing(): void
  {
    $wc = new WebComponent('Alert', [], false);
    $wc->closeTag();
    $this->assertEquals('', $wc->getInnerHtml());
  }

  public function testCreateXmlComponent()
  {
    $xml = '<Button><a href="google.com" target="_blank">Pincha AQUI</a></Button>';

    $wc = new WebComponent( 'Button', [], false);
    $fragment =  new Fragment('<a href="google.com" target="_blank">Pincha AQUI</a>');
    $wc->appendChild($fragment);
    $wc->closeTag();

    $this->assertEquals($xml, $wc->__toString());
  }

  public function testGetCreateChildren(): void
  {
    $icon = new WebComponent( 'Icon', ['src' => "www.google.com"], false);
    $icon->appendChild(new WebComponent('Img', [], true));
    $icon->appendChild(new Fragment('AAA'));
    $icon->closeTag();

    $button = new WebComponent( 'Button', [], false);
    $button->appendChild(new Fragment('<a href="google.com" target="_blank">Pincha AQUI</a>'));
    $button->appendChild($icon);

    $this->assertEquals($icon, $button->getChildren()[1]);
  }

  public function testAttr(): void
  {
    $wc = new WebComponent('Button', ['src' => "dummy", 'require' => "true"], true);
    $this->assertEquals(["src" => "dummy", "require" => "true"], $wc->getAttr());
  }


  public function testShouldNotEscapeAccentsWhenCastingToString(): void
  {
    $input = "<TableHeaderCell>Teléfono</TableHeaderCell>";
    $tag = new WebComponent( 'TableHeaderCell', [], false);
    $tag->appendChild(new Fragment('Teléfono'));
    $tag->closeTag();
    $result = (string)$tag;

    $this->assertEquals($input, $result);
  }

  public function testShouldReturnSpecialCharsOnChildren(): void
  {
    $tag = new WebComponent( 'Fragment', [], false);
    $tag->appendChild(new Fragment('áéíóúÜüïÇç"¡?¿!`+*-@#~½¬%ªº=/&'));
    $tag->closeTag();

    $this->assertEquals("áéíóúÜüïÇç\"¡?¿!`+*-@#~½¬%ªº=/&", $tag->getInnerHtml());
  }


  public function testCreateWebComponentWithChildrens()
  {
    $button = '<Button><A href="sdferfer"></Button>';

    $wc = new WebComponent('Button', [], false);
    $wc->appendChild(new WebComponent('A', ['href'=>'sdferfer'], false));
    $wc->closeTag();

    $this->assertEquals($button, $wc->__toString());
  }
}
