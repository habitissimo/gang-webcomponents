<?php
declare(strict_types=1);

namespace Gang\WebComponents\Parser\Nodes;

use Gang\WebComponents\Contracts\NodeInterface;
use Gang\WebComponents\Exceptions\ParserException;
use Gang\WebComponents\Parser\InnerHTMLExtractor;
use Gang\WebComponents\Parser\Parser;

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

    public function __construct(string $outerHtml, string $name, array $attrs)
    {
        $this->outerHtml = $outerHtml;
        $this->name = $name;
        $this->attributes = $attrs;
        $this->innerHtml = InnerHTMLExtractor::extract($outerHtml,$name);
        $this->originalInnerHtml = $this->innerHtml;
        $this->createChildren();
    }

    public static function create(string $outerHtml, string $name, array $attrs)
    {
        return new static ($outerHtml,$name,$attrs);
    }

    public function __toString() : string
    {
        return $this->outerHtml;
    }

    private function setRoot(\DOMDocument $xml)
    {
        $this->root = $xml->documentElement;
    }

    public function getAttr()
    {
        return $this->attributes;
    }

    private function extractInnerHtml(string $outerHtml): string
    {
        $innerHTML = '';
        set_error_handler([WebComponent::class, 'handleXmlError']);
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHtml(mb_convert_encoding($outerHtml,'HTML-ENTITIES','UTF-8'));
        libxml_use_internal_errors(false);
        restore_error_handler();
        $self = $dom->childNodes[1]->childNodes[0]->childNodes[0];
        foreach ($self->childNodes as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }

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
        return $this->children;
    }

    private function createChildren() : void
    {
        $parser = new Parser();
        $elements = $parser->parse($this->innerHtml);
        foreach ($elements as $element) {
            $this->children[] = $element;
        }
    }

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
