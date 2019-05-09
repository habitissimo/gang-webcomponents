<?php
declare(strict_types=1);
namespace Gang\WebComponents;


use Doctrine\Common\Cache\CacheProvider;
use Gang\WebComponents\Exceptions\ComponentClassNotFound;
use Gang\WebComponents\Exceptions\TemplateFileNotFound;
use Gang\WebComponents\Helpers\File;
use Gang\WebComponents\Logger\WebComponentLogger as Log;
use Symfony\Component\Finder\Finder;


class ComponentLibrary
{
    public const CONTENT_NOT_RENDERABLE = "CONTENT_NOT_RENDER";
    public const KEY_FILE = 'file';
    public const KEY_NAMESPACE = 'namespace';
    public const COMPONENT_FOLDER = 'component_folder';
    public const COMPONENT_TEMPLATE = 'component_template';
    public const COMPONENT_TEMPLATE_PATH = 'component_template_path';
    public const COMPONENT_NAME = 'class_name_component';

    private $library = [];
    private $cacheDriver;
    private $lifeTime;
    private const ID_CACHE = 'component_cache';

    public function __construct()
    {
      $this->cacheDriver = Configuration::$library_cache_driver;
      $this->lifeTime = Configuration::$library_cache_life_time;
      $this->loadLibrary(Configuration::$library_base_namespace, Configuration::$library_template_dir);
    }

    private function loadLibrary(string $base_namespace, string $template_dir) : void
    {
      $safe_base_namespace =  $this->getSafePath($base_namespace);
      $safe_template_dir =  $this->getSafePath($template_dir);

      if($this->cacheDriver){
        $this->addComponentsToLibrary("saveInCache", $safe_base_namespace, $template_dir);
      }else{
        $this->addComponentsToLibrary("findComponentsFiles", $safe_base_namespace, $safe_template_dir);
      }
    }

    public function getLibrary() : array
    {
        return $this->library;
    }

    public function getComponentClass(string $component_name) : string
    {
        $this->checkComponentInLibrary($component_name, '[ComponentLibrary@getComponentClass] Component class ' . $component_name . ' not found');
        return ($this->library[$component_name][self::KEY_NAMESPACE].'\\'.$this->library[$component_name][self::COMPONENT_NAME]);
    }

    public function getTemplateContent(string $component_name, string $extension, ?string $template_path = null) : string
    {
        $this->checkComponentInLibrary($component_name, '[ComponentLibrary@getTemplateContent-checkComponentInLibrary] Component class ' . $component_name . ' not found');
        $template_path = $this->getComponentPath($component_name, $extension, $template_path);
        $this->checkTemplateFolderInLibrary($template_path, '[ComponentLibrary@getTemplateContent-checkTemplateFolderInLibrary] Component class ' . $component_name . ' not found');

        return file_get_contents($template_path);
    }

    public function getTemplateContentFromPath(string $component_name, string $extension, string $path) : ?string
    {
        return $this->getTemplateContent($component_name, $extension, $path) ;
    }

    public function getComponentPath(string $component_name, string $extension, ?string $template_path = null)
    {
        if (null === $template_path) {
            $template_path = str_replace('.php', $extension, $this->library[$component_name][self::KEY_FILE]);
        }
        return $template_path;
    }

    public function addTemplateToLibrary(string $component, string $fileContent, string $filePath)
    {
        $this->addTemplateToWebComponent($component, $fileContent);
        $this->addTemplateFileToWebComponent($component, $filePath);
    }

    private function getSafePath($path) {
      if(substr($path, -1)==="\\" ||  substr($path, -1)==="/") {
       return substr_replace($path, "",  -1);
      }
      return $path;
    }

    private function addComponentsToLibrary($call_method, $base_namespace, $template_dir)
    {
      // Need it because the finder has to be refresh each use
      foreach ($this->{$call_method}($base_namespace, $template_dir) as $route => $component) {
        $componentName = "wc-".strtolower(preg_replace("%([a-z])([A-Z])%",'\1-\2',$component));
        $relative_path = File::getRelativePath($route, $template_dir);
        $relative_dir = File::getRelativeDir($relative_path);
        $namespace_extension = File::getNameSpaceFromFolder($relative_dir);
        $class_name = File::getClassNameFromFile($route) ;
        $this->library[$componentName][self::KEY_FILE] = $route;
        $this->library[$componentName][self::KEY_NAMESPACE] = $base_namespace.$namespace_extension;
        $this->library[$componentName][self::COMPONENT_FOLDER] = $template_dir;
        $this->library[$componentName][self::COMPONENT_NAME] = $class_name;
      }
    }

    private function saveInCache(string $base_namespace, string $template_dir) : array
    {
      if (!$this->cacheDriver->contains(self::ID_CACHE)) {
        $componentList = $this->findComponentsFiles($base_namespace, $template_dir);
        $this->cacheDriver->save(self::ID_CACHE, $componentList, $this->lifeTime);
      }
      return $this->cacheDriver->fetch(self::ID_CACHE);
    }

    private function findComponentsFiles(string $base_namespace, string $template_dir)
    {
      $template_dir = $this->standarizeTemplateDir($base_namespace, $template_dir);

      $finder = new Finder();
      $finder->files()->name('*' . '.php')->in($template_dir);
      Log::debug('[Library@load] Looking in ' . $template_dir . ' for web components');

      $map = [];
      foreach ($finder as $path => $file) {
        $map[$path] = basename($path, ".php");
      }

      return $map;
    }

    private function addTemplateFileToWebComponent(string $component, string $filePath)
    {
        $this->library[$component][self::COMPONENT_TEMPLATE_PATH] = $filePath;
    }

    private function addTemplateToWebComponent(string $component, string $fileContent)
    {
        $this->library[$component][self::COMPONENT_TEMPLATE] = $fileContent;
    }


    private function standarizeTemplateDir(string $base_namespace, ?string $template_dir = null) : string
    {
      if (null === $template_dir) {
        $template_dir = str_replace('\\', DIRECTORY_SEPARATOR, $base_namespace);
        Log::debug('[Library@load] No template dir specified. Using ' . $template_dir . ' as dir');
      }
      if (substr($template_dir, -1, 1) === '/') {
        $template_dir = substr($template_dir, 0, -1);
      }
      return $template_dir;
    }

    private function checkComponentInLibrary(string $component_name, ?string $msg = null)
    {
      if (!array_key_exists($component_name, $this->library)) {
        if (null !== $msg) {
          Log::error($msg);
        }
        throw new ComponentClassNotFound($component_name);
      }
    }

    private function checkTemplateFolderInLibrary(string $template_path, ?string $msg = null)
    {
      if (!file_exists($template_path)) {
        if (null !== $msg) {
          Log::error($msg);
        }
        throw new TemplateFileNotFound($template_path);
      }
    }
}
