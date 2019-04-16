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
        $controller  = WebComponentController::$instance;
        $fileContent = $this->templateFinder->find($htmlComponent);
        $context = get_object_vars($htmlComponent);

        $context['children'] = $context['innerHtml'];

        // In case that the content it couldn't be render return an empty string
        // So the HTML dosen't add anything

        if ($fileContent === componentLibrary::CONTENT_NOT_RENDERABLE) {
            return "";
        }

        $rendered = $this->templateRender->render($fileContent, $context);

        $rendered = $controller->process($rendered);
        return $this->postRender($rendered, $htmlComponent);
    }

    private function postRender(string $rendered, HTMLComponent $component)
    {
        $dom = Dom::create();
        $element = Dom::elementFromString($dom, $rendered);
        $className = $component->class_name;

        if (!$className && isset($component->classname)) {
          $className = $component->class_name;
        }

        if ($className && empty($element->getAttribute("class"))) {
            $element->setAttribute('class', $className);
        }

        /*
        if ($component->id && empty($element->getAttribute("id"))) {
            $element->setAttribute('id', $component->id);
        }
        */

        if (null !== $component->dataAttributes) {
          foreach ($component->dataAttributes->toArray() as $name => $value) {
            if (empty($element->getAttribute($name))) {
                $element->setAttribute($name, $value);
            }
          }
        }

        return Dom::elementToString($dom, $element);
    }
}
