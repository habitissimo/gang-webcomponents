<?php

namespace Gang\WebComponentsTests;

use Gang\WebComponents\TemplateFinder;
use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Tests\WebComponents\Button\ShareSocial\TwitterShareSocialButton;
use Gang\WebComponents\Tests\WebComponents\Button\ShareSocial\GoogleShareSocialButton;
use Gang\WebComponents\Tests\WebComponents\Button\Button;
use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;
use Gang\WebComponents\Contracts\TemplateFolderInterface;

class TemplateFinderTest extends TestCase
{
    private $templateFinder;
    private $buttonTemplate = '<a
  {% if id %} id = "{{ id }}" {% endif %}
  {% if href %} href = "{{ href }}" {% endif %}
  {% if role %} role = "{{ href? \'button\' : role }}" {% endif %}
  {% if title %} title = "{{ title }}" {% endif %}
  {% if rel %} rel = "{{ rel }}" {% endif %}
  {% if target %} target = "{{ target }}" {% endif %}
  {% if onclick %} onclick = "{{ onclick }}" {% endif %}

  class ="
    {% if is_social_share %}
      button-social-share share-size-{{ size }} share-{{ type }}
    {% else %}
      btn btn-{{ type }} btn-{{ size }}
    {% endif %}
    {# Align element to position #}
    {{ position? \'pull-\' ~ position : \'\' }}
    {# Add icons class #}
    {% if with_icon == \'true\' %}
      {{ is_social_share ? \'\' : \'btn-icon\' }}
    {% endif %}
    {# Add disabled class #}
    {{ disabled ? \'disabled\' : \'\' }}

    {{ block ? \'btn-full\' : \'\' }}
  "
>
    {{ children | raw }}
    {% if with_icon == \'true\' %}
        <Icon type="{{ icon_type}}"></Icon>
    {% endif %}

</a>
';

    public function setUp(): void
    {
        $lib = new ComponentLibrary();
        $lib->loadLibrary("Gang\WebComponents\Tests\WebComponents", __DIR__ . DIRECTORY_SEPARATOR .  "WebComponents");
        $this->templateFinder = new TemplateFinder(new TwigTemplateRenderer(), $lib);
    }

    // Test witb template
    public function testGetTemplate() : void
    {
        $this->assertEquals(
            $this->buttonTemplate,
            $this->templateFinder->find(new Button())
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
