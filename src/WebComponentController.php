<?php
declare(strict_types=1);

namespace Gang\WebComponents;

use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\Logger\WebComponentLogger;
use Gang\WebComponents\Logger\WebComponentLogger as Log;
use Gang\WebComponents\Parser\NewParser;
use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\Renderer\Renderer;
use Gang\WebComponents\Renderer\TreeRenderer;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;
use Psr\Log\LoggerInterface;

class WebComponentController
{

  static  $instance;
  private $parser;
  private $renderer;
  private $factory;
  private $render;

  private $dom;
  private $xpath;

  public function __construct(
    ?ComponentLibrary $library = null, ?TreeRenderer $renderer = null, ?NewParser $parser = null, ?LoggerInterface $logger = null
  ) {
    $library = $library ?? new ComponentLibrary();
    $this->parser = $parser ?? new NewParser();
    $this->renderer = $renderer ?? new TreeRenderer($library);
    $this->factory = new HTMLComponentFactory($library);
    $this->render = new Renderer(new TwigTemplateRenderer(), $library);
    if (null !== $logger) {
      WebComponentLogger::setLogger($logger);
    }

    self::$instance = $this;
  }

  /**
   * Replaces the WebComponents for actual HTML
   * @param string $content
   * @return string
   */
  public function process(string $content) : string
  {
    $this->dom = Dom::domFromString($content);
    $this->xpath = new \DOMXpath($this->dom);
    while($this->getNextWebComponent()){
      $webcomponent = $this->getNextWebComponent();
      $this->renderWC($webcomponent);
    };
    return $this->dom->saveHTML();
  }


  private function getNextWebComponent()
  {
    return $this->xpath->query("//*[starts-with(local-name(), 'wc-')]")[0];
  }


  private function renderWC($dom_wc){
    $wc = $this->createWebComponent($dom_wc);

    $htmlComponent = $this->factory->create($wc);

    $renderer_component = $htmlComponent->render($this->render, $dom_wc, $this->xpath);

    $DOM = Dom::domFromString($renderer_component);

    $dom_element_renderer = $DOM->childNodes[1];

    foreach($dom_wc->childNodes as $child){
      $dom_element_renderer->appendChild($DOM->importNode($child, true));
    }

    $parent_node = $dom_wc->parentNode;

    $parent_node->replaceChild($this->dom->importNode($dom_element_renderer, true),$dom_wc);
  }


  private function createWebComponent ($dom_wc){
    $attributes = [];
    if ($dom_wc->hasAttributes()) {
      foreach ($dom_wc->attributes as $attr) {
        $attributes[$attr->nodeName] = $attr->nodeValue;
      }
    }
    $wc = new WebComponent($dom_wc->nodeName, $attributes, false);
    return $wc;
  }
}
