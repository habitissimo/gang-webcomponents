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
        $className = File::getClassFromNameSpace(get_class($component));
        $fileContent = ComponentLibrary::CONTENT_NOT_RENDERABLE;
        $filePath = $this->lib
            ->getComponentPath(
                $className,
                $this->templateRender->getFileExtension()
            );
        // Core
        try {
            // Tries to find the Template of the component by convention
            $fileContent = $this->lib
                ->getTemplateContent(
                    $className,
                    $this->templateRender->getFileExtension()
                );
        } catch (TemplateFileNotFound $templateEx) {
            Log::error('[Renderer@render] TemplateFileNotFound - CONTENT_NOT_RENDERABLE' . $templateEx);
            if ($component instanceof TemplateFolderInterface) {
                $fileContent = $this->lib
                    ->getTemplateContent(
                        $className,
                        $this->templateRender->getFileExtension(),
                        $component->getTemplate()
                    );
                $filePath = $this->lib
                    ->getComponentPath(
                        $className,
                        $this->templateRender->getFileExtension(),
                        $component->getTemplate()
                    );
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
