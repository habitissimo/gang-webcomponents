<?php
declare(strict_types=1);
namespace Gang\WebComponents\Renderer;

use Gang\WebComponents\Configuration;
use Gang\WebComponents\Contracts\TemplateRendererInterface;
use Gang\WebComponents\Logger\WebComponentLogger as Log;

class TwigTemplateRenderer implements TemplateRendererInterface
{
    public function render($fileContent, $context) : string
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__);
        log::debug('Twig environment created');

        $env = Configuration::$twig_cache_path ? new \Twig_Environment($loader, ['cache' => Configuration::$twig_cache_path]) : new \Twig_Environment($loader);

        $template = $env->createTemplate($fileContent, $context);
        $rendered = $template->render($context);
        Log::debug('[TwigRenderer] rendered to: '.$rendered);
        return $rendered;
    }

    public function getFileExtension() : string
    {
        return '.twig';
    }
}
