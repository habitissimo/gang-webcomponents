<?php

namespace Gang\WebComponents\Helpers;

use Psr\Log\LoggerInterface;

class Dom
{
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
    if (libxml_get_errors() && $logger) {
      Dom::showErrors(libxml_get_errors(), $logger);
    }
    libxml_clear_errors();
    return $dom;
  }

  public static function preProcess($content): string
  {
    return Dom::preProcesNoScipt(Dom::preProcesScipt($content));
  }

  public static function postProcess($content)
  {
    return Dom::postProcessNoScript(Dom::postProcessScript($content));
  }

  public static function isWebComponent(\DomNode $element): bool
  {
    return substr($element->nodeName, 0, 3) === "wc-";
  }

  private static function showErrors($errors, $logger) : void
  {
    foreach ($errors as $error) {
      if ($error->code === 801) {
        $logger->info($error->message);
      }else {
        $logger->warning($error->message);
      }
    }
  }

  private static function preProcesNoScipt($content)
  {
    $matches = [];
    $openScript = "<noscript";
    $closeScript = "</noscript>";
    preg_match_all("/<noscript.*?>.*<\/noscript>/", $content, $matches, PREG_OFFSET_CAPTURE);
    $i = 0;
    foreach ($matches[0] as list($noscript, $_)) {
      $comment = '<replace-noscript id="replace-noscript-'.$i.'"></replace-noscript>';
      Dom::$noScripts[] = [$noscript, $comment];
      $content = str_replace($noscript, $comment, $content);
      $i++;
    }

    while (strpos($content, $openScript)) {
      $start = strpos($content, $openScript);
      $length = strpos($content, $closeScript) - $start + strlen($closeScript);
      $noscript = substr($content, $start, $length);
      $comment = '<replace-noscript id="replace-noscript-'.$i.'"></replace-noscript>';
      Dom::$noScripts[] = [$noscript, $comment];
      $content = str_replace($noscript, $comment, $content);
      $i++;
    }
    return $content;
  }

  private static function preProcesScipt($content)
  {
    $matches = [];
    $openScript = "<script";
    $closeScript = "</script>";
    preg_match_all("/<script.*?>.*<\/script>/", $content, $matches, PREG_OFFSET_CAPTURE);
    $i = 0;
    foreach ($matches[0] as list($script, $_)) {
      $comment = '<replace-script id="replace-script-'.$i.'"></replace-script>';
      Dom::$scripts[] = [$script, $comment];
      $content = str_replace($script, $comment, $content);
      $i++;
    }

    while (strpos($content, $openScript)) {
      $start = strpos($content, $openScript);
      $length = strpos($content, $closeScript) - $start + strlen($closeScript);
      $script = substr($content, $start, $length);
      $comment = '<replace-script id="replace-script-'.$i.'"></replace-script>';
      Dom::$scripts[] = [$script, $comment];
      $content = str_replace($script, $comment, $content);
      $i++;
    }
    return $content;
  }

  private static function postProcessNoScript($content)
  {
    if (Dom::$noScripts) {
      foreach (Dom::$noScripts as list($noScript, $comment)) {
        $content = str_replace($comment, $noScript, $content);
      }
    }
    return $content;
  }

  private static function postProcessScript($content)
  {
    if (Dom::$scripts) {
      foreach (Dom::$scripts as list($script, $comment)) {
        $content = str_replace($comment, $script, $content);
      }
    }
    return $content;
  }

}
