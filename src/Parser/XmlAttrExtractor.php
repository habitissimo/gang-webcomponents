<?php
declare(strict_types=1);
namespace Gang\WebComponents\Parser;

use Gang\WebComponents\Parser\Exception\InvalidXml;
use Gang\WebComponents\Parser\Exception\NotImplementedHandler;
use Gang\WebComponents\Parser\Exception\UnhandledXmlEntity;

class XmlAttrExtractor
{
  private $dom;
  private $attrs;
  private $name;

  public function __construct()
  {
    $this->dom = new \DOMDocument();
  }

  private function start($parser, $name, $attrs): void
  {
    $this->name = $name;
    $this->attrs = $attrs;
  }

  private function end($parser, $name): void
  {
    $this->name = $name;
  }

  public function with(string $node): self
  {
    $this->name = null;

    libxml_use_internal_errors(true); // supress malformed html warnings
    $this->dom->loadHtml(utf8_decode($node));
    libxml_use_internal_errors(false); // restore normal behavior

    $this->dom->substituteEntities = false;

    $element = $this->dom->childNodes[1]->childNodes[0]->childNodes[0];

    $this->name = $this->extractNamePreservingCase($node);
    $this->attrs = $this->getDomNodeAttrs($element);

    if ($this->nodeWasUnhandled()) {
        throw new UnhandledXmlEntity("$node could not be parsed by the xml parser");
    }

    return $this;
  }

  private function nodeWasUnhandled()
  {
    return null === $this->name;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getAttrs(): array
  {
    return $this->attrs;
  }

  private function getDomNodeAttrs(\DomElement $element = null) : array
  {
    $attrs = [];

    if (null === $element) {
        return $attrs;
    }

    foreach ($element->attributes as $attr) {
        $attrs[$attr->name] = $attr->value;
    }

    return $attrs;
  }

  private function extractNamePreservingCase(string $node)
  {
    if (preg_match('/<([A-Za-z0-9]+)/', $node, $matches)) {
        return $matches[1];
    }

    return $null;
  }
}
