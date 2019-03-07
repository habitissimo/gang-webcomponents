<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Gang\WebComponents\Renderer\Renderer;
use Gang\WebComponents\Tests\WebComponents\Button\ShareSocial\TwitterShareSocialButton;
use Gang\WebComponents\Tests\WebComponents\Button\Button;
use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Contracts\TemplateRendererInterface;
use Gang\WebComponents\ComponentLibrary;
use Prophecy\Prophet;
use Prophecy\Argument;

class RendererTest extends TestCase
{
    private $prophet;
    private $componentLibrary;
    private $templateRenderer;
    private $renderer;

    public function setUp(): void
    {
        $this->prophet = new Prophet;
        $this->componentLibrary = $this->prophet->prophesize(ComponentLibrary::class);
        $this->templateRenderer = $this->prophet->prophesize(TemplateRendererInterface::class);
        $this->renderer = new Renderer($this->templateRenderer->reveal(), $this->componentLibrary->reveal());
    }

    public function testRenderComponent(): void
    {
        $button = new Button();
        $button->href = "www.habitissimo.com";
        $this->componentLibrary
            ->getTemplateContent("Button", ".twig")
            ->willReturn("<a href='www.habitissimo.com'></a>");
        $this->templateRenderer->getFileExtension()->willReturn('.twig');
        $this->componentLibrary->getComponentPath("Button", ".twig")
            ->willReturn('/Button/Button.twig');
        $this->componentLibrary
            ->addTemplateToLibrary(
                Argument::type('string'),
                Argument::type('string'),
                Argument::type('string')
            )->willReturn(null);
        $this->templateRenderer->render(
            "<a href='www.habitissimo.com'></a>",
            array("id" => null,
            "size" => "md",
            "type" => "primary",
            "disabled" => null,
            "href" => "www.habitissimo.com",
            "position" => null,
            "role" => "button",
            "block" => false,
            "with_icon" => false,
            "title" => null,
            "rel" => null,
            "target" => null,
            "onclick" => null,
                "children" => null,
                "webcomponent_children" => null)
        )->willReturn("<a href='www.habitissimo.com'></a>");
        $this->assertEquals("<a href='www.habitissimo.com'></a>", $this->renderer->render($button));
    }

    public function testRenderComponentWithoutTemplate(): void
    {
        $renderedButton = '<a href="asasasas" role="button" class="
          button-social-share share-size-md share-primary





  ">
            <i class="icon icon-twitter"> </i>


</a>';
        $button = new TwitterShareSocialButton();
        $button->href = "asasasas";
        $this->componentLibrary->getTemplateContent("TwitterShareSocialButton", ".twig")
            ->willReturn($renderedButton);
        $this->templateRenderer->getFileExtension()->willReturn('.twig');
        $this->componentLibrary
            ->getComponentPath("TwitterShareSocialButton", ".twig")
            ->willReturn('/Button/Button.twig');
        $this->componentLibrary
            ->addTemplateToLibrary(
                Argument::type('string'),
                Argument::type('string'),
                Argument::type('string')
            )
            ->willReturn(null);

        $this->templateRenderer->render(
            $renderedButton,
            array("id" => null,
                "size" => "md",
                "type" => "primary",
                "icon_type" => "twitter",
                "is_social_share" => true,
                "disabled" => null,
                "href" => "asasasas",
                "position" => null,
                "role" => "button",
                "block" => false,
                "with_icon" => true,
                "title" => null,
                "rel" => null,
                "target" => null,
                "onclick" => null,
                "children" => null,
                "webcomponent_children" => null)
        )->willReturn($renderedButton);
        $this->assertEquals($renderedButton, $this->renderer->render($button));
    }
}
