<?php

namespace Gang\WebComponentsTests;

use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Contracts\TemplateFolderInterface;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;
use Gang\WebComponents\TemplateFinder;
use Gang\WebComponentsTests\WebComponents\Button\Button;
use Gang\WebComponentsTests\WebComponents\Button\ShareSocial\GoogleShareSocialButton;
use Gang\WebComponentsTests\WebComponents\Button\ShareSocial\TwitterShareSocialButton;
use PHPUnit\Framework\TestCase;

class TemplateFinderTest extends TestCase
{
    private $templateFinder;
    private $buttonTemplate = '<a {% if id %}id="{{ id }}"{% endif -%} {% if className %}class="btn {{ className }}" {% endif -%} {% if role %}role="{{ role }}" {% endif -%} {% if href -%} href="{{ href }}"{%- endif -%}>
    {{- children | raw }}
    {%- if with_icon == \'true\' -%}
        <Icon type="{{ icon_type }}"></Icon>
    {%- endif -%}
</a>
';

    public function setUp(): void
    {
        $lib = new ComponentLibrary(null);
        $lib->loadLibrary("Gang\WebComponentsTests\WebComponents", __DIR__ . DIRECTORY_SEPARATOR .  "WebComponents");

        $this->templateFinder = new TemplateFinder(new TwigTemplateRenderer(), $lib);
    }

    // Test with template
    public function testGetTemplate() : void
    {
        $button = new Button();
        $this->assertEquals(
            $this->buttonTemplate,
            $this->templateFinder->find($button)
        );
    }

    // test without template but with getTemplate()
    public function testGetTemplateFromGetTemplate() : void
    {
        $this->assertEquals(
            $this->buttonTemplate,
            $this->templateFinder->find(new TwitterShareSocialButton())
        );
    }

    public function testImplementsTemplateFolderInterface() : void
    {
        $button = new TwitterShareSocialButton();
        $this->assertInstanceOf(TemplateFolderInterface::class, $button);
    }

    // test no renderable
    public function testGetTemplateNotRenderable() : void
    {
        $this->assertEquals(
            ComponentLibrary::CONTENT_NOT_RENDERABLE,
            $this->templateFinder->find(new GoogleShareSocialButton())
        );
    }
}
