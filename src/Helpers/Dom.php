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
        $dom->loadHtml(utf8_decode($html));
        libxml_clear_errors();
        libxml_use_internal_errors(false); // restore normal behavior

        return $dom->childNodes[1]->firstChild->firstChild;
    }

    public static function elementToString(\DOMDocument $dom, $element)
    {
      return $dom->saveHtml($element);
    }
}
