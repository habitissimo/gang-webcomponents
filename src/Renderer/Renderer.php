<?php
declare(strict_types=1);
namespace Gang\WebComponents\Renderer;

use Gang\WebComponents\Contracts\TemplateRendererInterface;
use Gang\WebComponents\HTMLComponent;
use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\TemplateFinder;
use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\WebComponentController;

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

    public function render(HTMLComponent $htmlComponent) : string
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
        return $rendered;
    }

    public function replaceChildNodeToWebComponetRendered($webcomponent_rendered, $HTMLComponent, $dom)
    {
      $newDOM = Dom::domFromString($webcomponent_rendered);
      $dom_element_renderer = $newDOM->childNodes[1];

      $this->addClassAtributesNotYetAdded($HTMLComponent->class_name,$dom_element_renderer);

      $parent_node = $HTMLComponent->DOMElement->parentNode;
      $parent_node->replaceChild($dom->importNode($dom_element_renderer, true),$HTMLComponent->DOMElement);
    }


    private function addClassAtributesNotYetAdded($className,$element)
    {
      if($className){
        $componentClassAttributes =  explode(" ",$element->getAttribute("class"));
        $classNameAtributes = explode(" ",$className);
        $classAtributesNoAddedYet = array_diff($classNameAtributes, $componentClassAttributes);
        $element->setAttribute('class', $element->getAttribute("class") ." ". implode(" ", $classAtributesNoAddedYet));
      }
  }
}
