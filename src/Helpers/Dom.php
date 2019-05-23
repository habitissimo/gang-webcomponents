<?php

namespace Gang\WebComponents\Helpers;

use BasicLogger;

class Dom
{
  public static function create()
  {
    return new \DOMDocument();
  }


  // When $html value contains more than one element must be throw exception

  public static function elementFromString(\DOMDocument $dom, string $html)
  {
    libxml_use_internal_errors(true); // supress malformed html warnings
    $dom->substituteEntities = false;
    $dom->loadHtml(utf8_decode($html), LIBXML_HTML_NOIMPLIED | LIBXML_NONET | LIBXML_NOBLANKS);
    libxml_clear_errors();
    libxml_use_internal_errors(false); // restore normal behavior
    return $dom->childNodes[1];
  }

  public static function elementToString(\DOMDocument $dom, $element)
  {
    return $dom->saveHtml($element);
  }

  public static function domFromString(string $html, ?BasicLogger $logger = null)
  {
    libxml_use_internal_errors(true);
    $dom = new \DOMDocument();
    $dom->loadHtml(utf8_decode($html), LIBXML_HTML_NOIMPLIED | LIBXML_NONET);
    if(libxml_get_errors() && $logger) {
      $message = "";
      foreach (libxml_get_errors() as $error) {
        if (strpos($error->message , "wc-") === false & strpos($error->message , "replace-script") === false){
          $message .=  "Warning: {$error->message} | ";
        }
      }
      if(strlen($message)>0){
        $logger->warning($message);
      }
    }
    libxml_clear_errors();
    return $dom;
  }

  public static function isWebComponent(\DomNode $element): bool
  {
    return substr($element->nodeName, 0, 3) === "wc-";
  }

}
