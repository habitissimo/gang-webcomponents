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
  private $isChildWebComponent =  false;

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
    while ($HTMLComponents){
      foreach ($HTMLComponents as $htmlComponent){
        $renderer_component = $htmlComponent->render($this->render, $this->dom, $this->factory);
        $this->render->replaceChildNodeToWebComponetRender($renderer_component, $this->dom);
      }
        $HTMLComponents = $this->findParentsHTMLComponent();
    }


    return $this->dom->saveHTML();
  }

  private function findParentsHTMLComponent()
  {
    $this->HTMLComponents = [];
    $webcomponents = iterator_to_array($this->xpath->query("//*[starts-with(local-name(), 'wc-')]"));

    foreach ($webcomponents as $key => $component) {
      if ($key == 0){
        $this->HTMLComponents[] =  $this->factory->create($component);
      }else{
        $this->findWebComponentParent($component);
        if(!$this->isChildWebComponent){
          $this->HTMLComponents[] =  $this->factory->create($component);
        } else {
          $this->isChildWebComponent = false;
        }
      }
    }
    return $this->HTMLComponents;
  }

  private function findWebComponentParent($element)
  {
    if($element->parentNode){
      if(Dom::isWebComponent($element->parentNode)){
        $this->isChildWebComponent = true;
      }else {
        $this->findWebComponentParent($element->parentNode);
      }
    }
  }
}
