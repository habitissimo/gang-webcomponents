<?php
declare(strict_types=1);

namespace Gang\WebComponents;


use Gang\WebComponents\Logger\NullLogger;
use Psr\Log\LoggerInterface;
use Gang\WebComponents\Helpers\Log;
use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\Renderer\Renderer;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;

class WebComponentController
{
  private $factory;
  private $renderer;

  private $dom;
  private $xpath;

  private $logger;

  public function __construct(?LoggerInterface $logger = null, ?ComponentLibrary $library = null)
  {
    $this->logger = $logger ?? new NullLogger();
    $library = $library ?? new ComponentLibrary();
    $this->factory = new HTMLComponentFactory($library, $this->logger );
    $this->renderer = new Renderer(new TwigTemplateRenderer(), $library);
  }

  /**
   * Replaces the WebComponents for actual HTML
   * @param string $content
   * @return string
   */
  public function process(string $content): string
  {
    Log::startLogPerformace();
    $preProcessContent = Dom::preProcess($content);
    $this->dom = Dom::domFromString($preProcessContent, $this->logger);
    $this->xpath = new \DOMXpath($this->dom);
    $HTMLComponents = $this->getParentWebComponents();

    while ($HTMLComponents) {
      foreach ($HTMLComponents as $htmlComponent) {
        $renderer_component = $htmlComponent->render($this->renderer, $this->dom, $this->factory, $this->logger);
        $this->renderer->replaceChildNodeToWebComponetRendered($renderer_component, $htmlComponent, $this->dom, $this->logger);
      }
      $HTMLComponents = $this->getParentWebComponents();
    }
    $response = Dom::postProcess($this->dom->saveHTML());
    Log::endLogPerformance($this->logger);
    return $response;
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
      if ($key == 0) {
        $HTMLComponents[] = $this->factory->create($component);
      } else {
        if (!$this->hasParentWebComponent($component)) {
          $HTMLComponents[] = $this->factory->create($component);
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
    if ($element->parentNode) {
      if (Dom::isWebComponent($element->parentNode)) {
        return true;
      } else {
        $this->hasParentWebComponent($element->parentNode);
      }
    }
    return false;
  }
}
