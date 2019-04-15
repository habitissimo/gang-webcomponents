<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\Renderer\Renderer;
use Gang\WebComponentsTests\WebComponents\Button\ShareSocial\TwitterShareSocialButton;
use Gang\WebComponentsTests\WebComponents\Button\Button;
use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Contracts\TemplateRendererInterface;
use Gang\WebComponents\ComponentLibrary;
use Prophecy\Prophet;
use Prophecy\Argument;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;
use Gang\WebComponents\HTMLComponent;

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
        $buttonComponentClass = new class() extends HtmlComponent {};
        $button = new $buttonComponentClass();
        $class_name = get_class($button);
        $this->componentLibrary
            ->getTemplateContent($class_name, ".twig")
            ->willReturn("<p>a content</p>");

        $this->componentLibrary
            ->getComponentPath($class_name, ".twig")
            ->willReturn('/Button/Button.twig');

        $this->componentLibrary
            ->addTemplateToLibrary(
                $class_name,
                '<p>a content</p>',
                '/Button/Button.twig'
            )->willReturn(null);

        $this->templateRenderer
            ->getFileExtension()
            ->willReturn('.twig');

        $this->templateRenderer->render(
          "<p>a content</p>",
          array(
            "dataAttributes" => $button->dataAttributes,
            "innerHtml" => null,
            "class_name" => null,
            "children" => null
          )
        )->willReturn("<p>a rendered content</p>");

        $this->assertEquals('<p>a rendered content</p>', $this->renderer->render($button));
    }

    public function testRenderComponentWithoutTemplate(): void
    {
        $renderedButton = '<a>A content</a>';
        $button = new TwitterShareSocialButton();
        $button->href = "asasasas";
        $button->type = "primary";


      $buttonComponentClass = new class() extends TwitterShareSocialButton {};
      $button = new $buttonComponentClass();
      $class_name = get_class($button);

        $this->componentLibrary->getTemplateContent($class_name, ".twig")
            ->willReturn($renderedButton);
        $this->templateRenderer->getFileExtension()->willReturn('.twig');
        $this->componentLibrary
            ->getComponentPath($class_name, ".twig")
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
            array(
              "icon_type" => "twitter",
              "is_social_share" => true,
              "id" => null,
              "size" => "md",
              "type" => "primary",
              "disabled" => null,
              "href" => null,
              "position" => null,
              "role" => "button",
              "block" => false,
              "with_icon" => true,
              "title" => null,
              "rel" => null,
              "target" => null,
              "onclick" => null,
              "dataAttributes" => null,
              "innerHtml" => null,
              "class_name" => null,
              "children" => null,
            )
        )->willReturn($renderedButton);
        $this->assertEquals($renderedButton, $this->renderer->render($button));
    }
}
