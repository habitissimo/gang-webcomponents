<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Contracts\TemplateRendererInterface;
use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\HTMLComponent;
use Gang\WebComponents\Renderer\Renderer;
use Gang\WebComponents\WebComponentController;
use Gang\WebComponentsTests\WebComponents\Button\ShareSocial\TwitterShareSocialButton;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophet;


class RendererTest extends TestCase
{
    private $prophet;
    private $componentLibrary;
    private $templateRenderer;
    private $renderer;
    private $controller;

  public function setUp(): void
    {

      $this->prophet = new Prophet;
      $this->componentLibrary = $this->prophet->prophesize(ComponentLibrary::class);
      $this->templateRenderer = $this->prophet->prophesize(TemplateRendererInterface::class);
      $this->renderer = new Renderer($this->templateRenderer->reveal(), $this->componentLibrary->reveal());
      $this->controller = new WebComponentController($this->componentLibrary->reveal());
    }

    public function testRenderComponent(): void
    {
        $buttonComponentClass = new class() extends HtmlComponent {};
        $button = new $buttonComponentClass();

        $class_name = "wc-button";
        $dom = Dom::domFromString("<wc-button>");
        $button->setDOMElement($dom->childNodes[1]);

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
            "childNodes" => [],
            "DOMElement" => $button->DOMElement,
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
      $class_name = "wc-twitter-share-social-button";
      $dom = Dom::domFromString("<wc-twitter-share-social-button>");
      $button->setDOMElement($dom->childNodes[1]);

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
              "childNodes" => [],
              "DOMElement" => $button->DOMElement,
              "dataAttributes" => null,
              "innerHtml" => null,
              "class_name" => null,
              "children" => null
            )
        )->willReturn($renderedButton);
        $this->assertEquals($renderedButton, $this->renderer->render($button));
    }
}
