<?php
declare(strict_types=1);

namespace Gang\WebComponents;

use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\Logger\WebComponentLogger;
use Gang\WebComponents\Parser\NewParser;
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
  private $HTMLComponents = [];
  private $parentComponent;

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


    $HTMLComponents = $this->findParentsHTMLComponent();

    foreach ($HTMLComponents as $htmlComponent){
      $renderer_component = $htmlComponent->render($this->render, $this->dom, $this->factory);

      $newDOM = Dom::domFromString($renderer_component["render_content"]);
      $dom_element_renderer = $newDOM->childNodes[1];

      $renderer_component["HTMLComponent"]->addClassAtributesNotYetAdded($dom_element_renderer);

      $parent_node = $renderer_component["HTMLComponent"]->DOMElement->parentNode;
      $parent_node->replaceChild($this->dom->importNode($dom_element_renderer, true),$htmlComponent->DOMElement);
    }


    return $this->dom->saveHTML();
  }


  private function findParentsHTMLComponent()
  {
    $webcomponents = iterator_to_array($this->xpath->query("//*[starts-with(local-name(), 'wc-')]"));

    foreach ($webcomponents as $key => $component) {
      if ($key == 0){
        $this->HTMLComponents[] =  $this->factory->create($component);
      }else{
        $this->parentComponent = null;
        $this->setHTMLComponentChild(end($this->HTMLComponents)->DOMElement, $component);

        if(!$this->parentComponent){
          $this->HTMLComponents[] =  $this->factory->create($component);
        }
      }
    }
    return $this->HTMLComponents;
  }

  private function setHTMLComponentChild($WebComponentElement , $component)
  {
    if ($component->parentNode === $WebComponentElement){
      $this->parentComponent = $WebComponentElement;
    }
    if ($WebComponentElement->childNodes){
      foreach ($WebComponentElement->childNodes as $child) {
        $this->setHTMLComponentChild($child, $component);
      }
    }
  }
}
