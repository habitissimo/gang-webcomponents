<?php

namespace Gang\WebComponents\Helpers;

use Gang\WebComponents\Configuration;
use Psr\Log\LoggerInterface;

class Dom
{
  public static $tagsToReplace = ["script", "noscript"];
  private static $contentToReplace = [];
  private static $scripts = [];
  private static $noScripts = [];

  public static function create()
  {
    return new \DOMDocument();
  }


  // When $html value contains more than one element must be throw exception

  public static function elementFromString(\DOMDocument $dom, string $html)
  {
    libxml_use_internal_errors(true); // supress malformed html warnings
    $dom->substituteEntities = false;
    $dom->loadHtml($html, LIBXML_HTML_NOIMPLIED | LIBXML_NONET | LIBXML_NOBLANKS);
    libxml_clear_errors();
    libxml_use_internal_errors(false); // restore normal behavior
    return $dom->childNodes[1];
  }

  public static function elementToString(\DOMDocument $dom, $element)
  {
    return $dom->saveHtml($element);
  }

  public static function domFromString(string $html, ?LoggerInterface $logger = null)
  {
    libxml_use_internal_errors(true);
    $dom = new \DOMDocument();
    $html =  iconv('UTF-8', 'UTF-8//IGNORE', $html);
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    $dom->loadHtml($html, LIBXML_HTML_NOIMPLIED | LIBXML_NONET);
    if (libxml_get_errors() && $logger && Configuration::$log_enable) {
      Dom::showErrors(libxml_get_errors(), $logger);
    }
    libxml_clear_errors();
    return $dom;
  }

  public static function preProcess($content): string
  {
    foreach (Dom::$tagsToReplace as $replace) {
      $content = Dom::replaceTags($content, $replace);
    }
    return $content;
  }

  public static function postProcess($content)
  {
    if (Dom::$contentToReplace) {
      foreach (Dom::$contentToReplace as $foo) {
        foreach ($foo as list($script, $comment)) {
          $content = str_replace($comment, $script, $content);
        }
      }
    }
    return $content;
  }

  public static function isWebComponent(\DomNode $element): bool
  {
    return substr($element->nodeName, 0, 3) === "wc-";
  }

  private static function showErrors($errors, $logger) : void
  {
    foreach ($errors as $error) {
      if ($error->code === 801 || $error->code === 23 ||  $error->code ===  513 ) {
        if(Configuration::$log_level_info) {
          $logger->info($error->message);
        }
      }else {
        if (Configuration::$log_level_warning){
          $logger->warning($error->message);
        }
      }
    }
  }

  private static function replaceTags($content, $replace)
  {
    $replacements = [];
    $openTag = "<{$replace}";
    $closeTag = "</{$replace}>";

    $i = 0;
    while (strpos($content, $openTag)) {
      $start = strpos($content, $openTag);
      $length = strpos($content, $closeTag) - $start + strlen($closeTag);
      $element = substr($content, $start, $length);
      $comment = '<replace-'.$replace.' id="replace-'.$replace.'-'.$i.'"></replace-'.$replace.'>';
      $replacements[] = [$element, $comment];

      $content = substr_replace($content, $comment, $start, strlen($element));

      $i++;
    }
    Dom::$contentToReplace[] = $replacements;
    return $content;
  }

}
