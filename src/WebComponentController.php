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
    $web_components = $this->getWebComponents();
    while($web_components){
      foreach ($web_components as $webcomponent){
        $htmlComponent = $this->factory->create($webcomponent);
        $htmlComponent->render($this->render, $webcomponent, $this->dom, $this->factory);
      }
      $web_components = $this->getWebComponents();
    };
    return $this->dom->saveHTML();
  }


  private function getWebComponents()
  {
    return array_reverse(iterator_to_array($this->xpath->query("//*[starts-with(local-name(), 'wc-')]")));;
  }
}
