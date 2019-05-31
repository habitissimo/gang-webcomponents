<?php

namespace Gang\WebComponentsTests;

use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Configuration;
use Gang\WebComponents\Contracts\TemplateFolderInterface;
use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\Logger\NullLogger;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;
use Gang\WebComponents\TemplateFinder;
use Gang\WebComponentsTests\WebComponents\Button\Button;
use Gang\WebComponentsTests\WebComponents\Button\ShareSocial\GoogleShareSocialButton;
use Gang\WebComponentsTests\WebComponents\Button\ShareSocial\TwitterShareSocialButton;
use PHPUnit\Framework\TestCase;

class TemplateFinderTest extends TestCase
{
  private $templateFinder;
  private $logger;
  private $buttonTemplate = '<a {% if id %}id="{{ id }}"{% endif -%} {% if className %}class="btn {{ className }}" {% endif -%} {% if role %}role="{{ role }}" {% endif -%} {% if href -%} href="{{ href }}"{%- endif -%}>
    {{- children | raw }}
    {%- if with_icon == \'true\' -%}
        <Icon type="{{ icon_type }}"></Icon>
    {%- endif -%}
</a>
';

  public function setUp(): void
  {
    Configuration::$library_base_namespace = "Gang\WebComponentsTests\WebComponents";
    Configuration::$library_template_dir = __DIR__ . DIRECTORY_SEPARATOR . "WebComponents";
    $lib = new ComponentLibrary(null);
    $this->templateFinder = new TemplateFinder(new TwigTemplateRenderer(), $lib);
    $this->logger = new NullLogger();
  }

  // Test with template
  public function testGetTemplate(): void
  {
    $button = new Button();
    $dom = Dom::domFromString("<wc-button></wc-button>", $this->logger);
    $button->setDOMElement($dom->childNodes[1]);

    $this->assertEquals(
      $this->buttonTemplate,
      $this->templateFinder->find($button)
    );
  }

  // test without template but with getTemplate()
  public function testGetTemplateFromGetTemplate(): void
  {
    $button = new TwitterShareSocialButton();
    $dom = Dom::domFromString("<wc-twitter-share-social-button></wc-twitter-share-social-button>", $this->logger);
    $button->setDOMElement($dom->childNodes[1]);

    $this->assertEquals(
      $this->buttonTemplate,
      $this->templateFinder->find($button)
    );
  }

  public function testImplementsTemplateFolderInterface(): void
  {
    $button = new TwitterShareSocialButton();
    $this->assertInstanceOf(TemplateFolderInterface::class, $button);
  }

  // test no renderable
  public function testGetTemplateNotRenderable(): void
  {
    $button = new GoogleShareSocialButton();
    $dom = Dom::domFromString("<wc-google-share-social-button></wc-google-share-social-button>", $this->logger);
    $button->setDOMElement($dom->childNodes[1]);

    $this->assertEquals(
      ComponentLibrary::CONTENT_NOT_RENDERABLE,
      $this->templateFinder->find($button)
    );
  }
}
