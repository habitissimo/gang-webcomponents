<?php

namespace Gang\WebComponentsTests;

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
        $this->webcomponent = $this->prophet->prophesize(WebComponent::class);

    }

    public function testPublicAttrComponent() : void
    {
        $button = new PublicAttrsComponent();
        $button->id="habitissimo";
        $button->setInnerHTML("Habitissimo");

        $this->webcomponent->getChildren()->willReturn([]);
        $this->webcomponent->getTagName()->willReturn('Button');
        $this->webcomponent->getAttr()->willReturn(['id'=>'habitissimo']);
        $this->webcomponent->getInnerHtml()->willReturn('Habitissimo');

        $button->setWebComponent($this->webcomponent->reveal());
        
        $this->library->getComponentClass("Button")
            ->willReturn(PublicAttrsComponent::class);
        $factory = new HTMLComponentFactory($this->library->reveal());
        $HtmlComponent = $factory->create($this->webcomponent->reveal());
        $this->assertEquals($button, $HtmlComponent);

    }

    public function testProtectedAttrComponent() : void
    {
        $input = new ProtectedAttrsComponent();
        $input->setId("testing-input");

        $this->webcomponent->getChildren()->willReturn([]);
        $this->webcomponent->getTagName()->willReturn('InputText');
        $this->webcomponent->getAttr()->willReturn(['id'=>"testing-input"]);
        $this->webcomponent->getInnerHtml()->willReturn("");

        $input->setWebComponent($this->webcomponent->reveal());

        $this->library->getComponentClass('InputText')
            ->willReturn(ProtectedAttrsComponent::class);
        $factory = new HTMLComponentFactory($this->library->reveal());

        $this->assertEquals($input, $factory->create($this->webcomponent->reveal()));
    }

    public function testPreRender() : void
    {
        $this->webcomponent->getChildren()->willReturn([]);
        $this->webcomponent->getTagName()->willReturn('TagName');
        $this->webcomponent->getAttr()->willReturn([]);
        $this->webcomponent->getInnerHtml()->willReturn("");

        $this->library->getComponentClass('TagName')
            ->willReturn(PreRenderComponent::class);
        $factory  = new HTMLComponentFactory($this->library->reveal());

        $this->assertEquals("something", $factory->create($this->webcomponent->reveal())->prerendered_content);
    }
}
