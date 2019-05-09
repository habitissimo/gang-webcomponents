<?php
declare(strict_types=1);
namespace Gang\WebComponents;

use Gang\WebComponents\Contracts\TemplateFolderInterface;
use Gang\WebComponents\Contracts\TemplateRendererInterface;
use Gang\WebComponents\Exceptions\TemplateFileNotFound;
use Gang\WebComponents\Helpers\File;
use Gang\WebComponents\Logger\WebComponentLogger as Log;

class TemplateFinder
{
   
    private $lib;
    private $templateRender;

    public function __construct(TemplateRendererInterface $templateRender, ComponentLibrary $componentLibrary)
    {
        $this->lib = $componentLibrary;
        $this->templateRender = $templateRender;
    }

    /**
     * Function that tries to get a template for an HTMLComponent
     * by using the convention or the template
     */
    public function find(HTMLComponent $component) : string
    {
        // Defaults
        $className = $component->getTagNameComponent();
        $fileContent = ComponentLibrary::CONTENT_NOT_RENDERABLE;

        $fileExtension = $this->templateRender->getFileExtension();

        $filePath = $this->lib->getComponentPath($className, $fileExtension);

        // Core
        try {
            // Tries to find the Template of the component by convention
            $fileContent = $this->lib->getTemplateContent($className, $fileExtension);
        } catch (TemplateFileNotFound $templateEx) {
            Log::error('[Renderer@render] TemplateFileNotFound - CONTENT_NOT_RENDERABLE' . $templateEx);
            if ($component instanceof TemplateFolderInterface) {
                $fileContent = $this->lib->getTemplateContent(
                    $className, $fileExtension, $component->getTemplate());

                $filePath = $this->lib->getComponentPath(
                    $className, $fileExtension, $component->getTemplate());
            }
        }
        // update the library with the template folder and content
        $this->lib->addTemplateToLibrary($className, $fileContent, $filePath);
        Log::info('[Renderer@render] Calling loader and storing ' . $className . ' file content');
        return $fileContent;
    }

    public function getComponentLibrary()
    {
        return $this->lib;
    }
}
