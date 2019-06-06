<?php

namespace Gang\WebComponents\Helpers;

class Dom
{
  public static $tagsToReplace = ["script", "noscript"];
  public static $errorCodes = [801, 23, 513, 68];
  private static $contentToReplace = [];

  public static function create()
  {
    return new \DOMDocument();
  }

  public static function domFromString(string $html, $logger)
  {
    libxml_use_internal_errors(true);
    $dom = new \DOMDocument();
    $html = iconv('UTF-8', 'UTF-8//IGNORE', $html);
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    $dom->loadHtml($html, LIBXML_HTML_NOIMPLIED | LIBXML_NONET);
    Log::showLibXMLErrors(libxml_get_errors(), $logger, Dom::$errorCodes, $html);
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
          $content = str_replace($comment, $script, $content, $count);
          $content = str_replace(htmlentities($comment, ENT_NOQUOTES),$script  , $content);
        }
      }
    }
    return $content;
  }

  public static function isWebComponent(\DomNode $element): bool
  {
    return substr($element->nodeName, 0, 3) === "wc-";
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
      $comment = '<replace-' . $replace . ' id="replace-' . $replace . '-' . $i . '"></replace-' . $replace . '>';
      $replacements[] = [$element, $comment];

      $content = substr_replace($content, $comment, $start, strlen($element));

      $i++;
    }
    Dom::$contentToReplace[] = $replacements;
    return $content;
  }

}
