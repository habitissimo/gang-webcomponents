<?php
declare(strict_types=1);

namespace Gang\WebComponents\Parser\Nodes;

use Gang\WebComponents\Contracts\NodeInterface;
use Gang\WebComponents\Exceptions\ParserException;
use Gang\WebComponents\Parser\InnerHTMLExtractor;
use Gang\WebComponents\Parser\Parser;
use Gang\WebComponents\Helpers\Dom;

/**
 * Class WebComponent
 * @package Gang\WebComponents\Nodes
 */
class WebComponent implements NodeInterface
{
    private $outerHtml = '';
    private $name;
    private $attributes = [];
    private $innerHtml = '';
    private $originalInnerHtml = '';
    private $children = [];

    public function __construct(string $name, array $attrs)
    {
      $this->name = $name;
      $this->attributes = $attrs;
    }

    public function __toString() : string
    {
        return $this->outerHtml;
    }

    public function getAttr()
    {
        return $this->attributes;
    }

//    private function extractInnerHtml(string $outerHtml): string
//    {
//        $innerHTML = '';
//        $dom = Dom::create();
//        $self = Dom::elementFromString($dom, $outerHtml);
//        foreach ($self->childNodes as $child) {
//            $innerHTML .= Dom::elementToString($dom, $child);
//        }
//        return $innerHTML;
//    }

    public function getTagName() : string
    {
        return $this->name;
    }

    public function getInnerHtml() : string
    {
        return $this->innerHtml;
    }

    public function setInnerHtml($value) : void
    {
        $this->innerHtml = $value;
    }

    public function getOriginalInnerHtml() : string
    {
        return $this->originalInnerHtml;
    }

    public function setOriginalInnerHtml($value) : void
    {
        $this->originalInnerHtml = $value;
    }

    public function getChildren() : array
    {
        return $this->childrens;
    }

    public function appendChild($child) : void
    {
      $this->children[] = $child;
    }

    public function getOuterHtml(): string
    {
      return $this->outerHtml;
    }

    public function setOuterHtml(string $outerHtml): void
    {
      $this->outerHtml = $outerHtml;
    }

//    private function createChildren() : void
//    {
//        $parser = new Parser();
//        $elements = $parser->parse($this->innerHtml);
//
//        foreach ($elements as $element) {
//            $this->children[] = $element;
//        }
//    }

    /**
     * Function to handle errors from the DomDocument
     */
    public static function handleXmlError($errno, $errstr, $errfile, $errline, $errContext)
    {
        if (E_WARNING === $errno && (substr_count($errstr, 'DOMDocument::load') > 0)) {
            throw new ParserException($errstr, $errContext['outerHtml']);
        }
        return false;
    }
}
