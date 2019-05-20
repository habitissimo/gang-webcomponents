<?php

namespace Gang\WebComponentsTests;

use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\HTMLComponent;
use Gang\WebComponents\HTMLComponentFactory;
use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Parser\Nodes\WebComponent;

use Gang\WebComponents\Parser\Parser;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;

class PublicAttrsComponent extends HTMLComponent
{
    public $id;
}

class ProtectedAttrsComponent extends HTMLComponent
{
    protected $id;
    public function setId($value)
    {
        $this->id = $value;
    }
}

class PreRenderComponent extends HTMLComponent
{
    public $prerendered_content = null;
    public function preRender(): void
    {
        $this->prerendered_content = "something";
    }
}

class HTMLComponentFactoryTest extends TestCase
{
    private $prophet;
    private $library;
    private $webcomponent;


    public function setUp()
    {
        $this->prophet = new Prophet();
        $this->library = $this->prophet->prophesize(ComponentLibrary::class);
    }

    public function testPublicAttrComponent() : void
    {
        $button = new PublicAttrsComponent();
        $button->id="habitissimo";

        $dom = Dom::domFromString("<wc-button id='habitissimo'>Habitissimo</wc-button>");
        $button->setDOMElement($dom->childNodes[1]);

        $this->library->getComponentClass("wc-button")
            ->willReturn(PublicAttrsComponent::class);
        $factory = new HTMLComponentFactory($this->library->reveal());
        $HtmlComponent = $factory->create($dom->childNodes[1]);
        $this->assertEquals($button, $HtmlComponent);

    }

    public function testProtectedAttrComponent() : void
    {
        $input = new ProtectedAttrsComponent();
        $input->setId("testing-input");

        $dom = Dom::domFromString("<wc-input-text id='testing-input'>");
        $input->setDOMElement($dom->childNodes[1]);

        $this->library->getComponentClass('wc-input-text')
            ->willReturn(ProtectedAttrsComponent::class);
        $factory = new HTMLComponentFactory($this->library->reveal());

        $this->assertEquals($input, $factory->create($dom->childNodes[1]));
    }

    public function testPreRender() : void
    {
//        $this->webcomponent->getChildren()->willReturn([]);
//        $this->webcomponent->getTagName()->willReturn('TagName');
//        $this->webcomponent->getAttr()->willReturn([]);
//        $this->webcomponent->getInnerHtml()->willReturn("");

        $dom = Dom::domFromString("<wc-tag-name></wc-tag-name>");

        $this->library->getComponentClass('wc-tag-name')
            ->willReturn(PreRenderComponent::class);
        $factory  = new HTMLComponentFactory($this->library->reveal());

        $this->assertEquals("something", $factory->create($dom->childNodes[1])->prerendered_content);
    }
}
