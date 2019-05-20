<?php
declare(strict_types=1);

namespace Gang\WebComponents;

use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\Logger\WebComponentLogger;
use Gang\WebComponents\Renderer\Renderer;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;
use Psr\Log\LoggerInterface;

class WebComponentController
{

  static  $instance;
  private $factory;
  private $renderer;

  private $dom;
  private $xpath;


  public function __construct(
    ?ComponentLibrary $library = null, ?LoggerInterface $logger = null
  ) {
    $library = $library ?? new ComponentLibrary();
    $this->factory = new HTMLComponentFactory($library);
    $this->renderer = new Renderer(new TwigTemplateRenderer(), $library);
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
    $HTMLComponents = $this->getParentWebComponents();

    while ($HTMLComponents){
      foreach ($HTMLComponents as $htmlComponent){
        $renderer_component = $htmlComponent->render($this->renderer, $this->dom, $this->factory);
        $this->renderer->replaceChildNodeToWebComponetRendered($renderer_component,$htmlComponent, $this->dom);
      }
        $HTMLComponents = $this->getParentWebComponents();
    }

    return $this->dom->saveHTML();
  }

  /**
   * Return array of WebComponents that not have WebComponent parents
   * @param void
   * @return array
   */
  private function getParentWebComponents()
  {
    $HTMLComponents = [];
    $webcomponents = iterator_to_array($this->xpath->query("//*[starts-with(local-name(), 'wc-')]"));

    foreach ($webcomponents as $key => $component) {
      if ($key == 0){
        $HTMLComponents[] =  $this->factory->create($component);
      }else{
        if(!$this->hasParentWebComponent($component)){
          $HTMLComponents[] =  $this->factory->create($component);
        }
      }
    }
    return $HTMLComponents;
  }

  /**
   * If element has an WebComponent parent will return true
   * @param mixed
   * @return bool
   */
  private function hasParentWebComponent(\DomNode $element)
  {
    if($element->parentNode){
      if(Dom::isWebComponent($element->parentNode)){
        return true;
      }else {
        $this->hasParentWebComponent($element->parentNode);
      }
    }
    return false;
  }
}
