<?php
declare(strict_types=1);

namespace Gang\WebComponents\Renderer;

use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Contracts\TemplateRendererInterface;
use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\HTMLComponent;
use Gang\WebComponents\Logger\NullLogger;
use Gang\WebComponents\TemplateFinder;

class Renderer
{
  private $templateRender;
  private $templateFinder;

  /*
  Renderer receives the TemplateEngine to render and the FileLoader to get the htmlComponent
  */
  public function __construct(TemplateRendererInterface $templateRender, ComponentLibrary $componentLibrary)
  {
    $this->templateRender = $templateRender;
    $this->templateFinder = new TemplateFinder($templateRender, $componentLibrary);
  }

  public function render(HTMLComponent $htmlComponent): string
  {
    $fileContent = $this->templateFinder->find($htmlComponent);
    $context = get_object_vars($htmlComponent);
    $context['children'] = $context['innerHtml'];
    // In case that the content it couldn't be render return an empty string
    // So the HTML dosen't add anything

    if ($fileContent === componentLibrary::CONTENT_NOT_RENDERABLE) {
      return "";
    }

    $rendered = $this->templateRender->render($fileContent, $context);
    $this->postRender($rendered, $htmlComponent);
    return $this->postRender($rendered, $htmlComponent);
  }

  private function postRender(string $rendered, HTMLComponent $htmlComponent)
  {
    if ($htmlComponent->dataAttributes !== null || $htmlComponent->class_name) {
      $dom = Dom::domFromString($rendered, new NullLogger());
      $element = $dom->childNodes[1];

      if($htmlComponent->dataAttributes !== null ) {
        $this->addDataAtributes($element, $htmlComponent);
      }

      if($htmlComponent->class_name) {
        $this->addClassAtributesNotYetAdded($htmlComponent->class_name, $element);
      }

      return  $dom->saveHTML($element);
    } else {
      return $rendered;
    }
  }

  public function replaceChildNodeToWebComponetRendered($webcomponent_rendered, $HTMLComponent, $dom, $logger)
  {
    $newDOM = Dom::domFromString($webcomponent_rendered, $logger);
    $dom_element_renderer = $newDOM->childNodes[1];
    $parent_node = $HTMLComponent->DOMElement->parentNode;
    $parent_node->replaceChild($dom->importNode($dom_element_renderer, true), $HTMLComponent->DOMElement);
  }


  private function addClassAtributesNotYetAdded($className, $element)
  {
    $componentClassAttributes = explode(" ", $element->getAttribute("class"));
    $classNameAtributes = explode(" ", $className);
    $classAtributesNoAddedYet = array_diff($classNameAtributes, $componentClassAttributes);
    $element->setAttribute('class', $element->getAttribute("class") . " " . implode(" ", $classAtributesNoAddedYet));
  }

  private function addDataAtributes($element, $htmlComponent)
  {
    foreach ($htmlComponent->dataAttributes->toArray() as $name => $value) {
      if (empty($element->getAttribute($name))) {
        $element->setAttribute($name, $value);
      }
    }
  }
}
