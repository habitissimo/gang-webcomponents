<?php

namespace Gang\WebComponents\Helpers;

class Dom
{
    public static function create()
    {
      return new \DOMDocument();
    }

    public static function elementFromString(\DOMDocument $dom, string $html)
    {
        libxml_use_internal_errors(true); // supress malformed html warnings
        $dom->substituteEntities = false;
        $dom->loadHtml(utf8_decode($html), LIBXML_HTML_NOIMPLIED | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors(false); // restore normal behavior
        return $dom->childNodes[1];
    }

    public static function xmlFromString(\DOMDocument $dom, string $html)
    {
      libxml_use_internal_errors(true); // supress malformed html warnings
      $dom->substituteEntities = false;
      $dom->loadXml(utf8_decode($html), LIBXML_NONET);
      libxml_clear_errors();
      libxml_use_internal_errors(false); // restore normal behavior
      return $dom->firstChild;
    }

    public static function elementToString(\DOMDocument $dom, $element)
    {
      return str_replace("\n","",$dom->saveHtml($element));
    }
}
