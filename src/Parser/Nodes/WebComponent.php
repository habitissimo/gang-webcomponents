<?php
declare(strict_types=1);

namespace Gang\WebComponents\Parser\Nodes;

use Gang\WebComponents\Contracts\NodeInterface;
use Gang\WebComponents\Exceptions\ParserException;
use Gang\WebComponents\Parser\InnerHTMLExtractor;
use Gang\WebComponents\Parser\TagMaker;
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
  private $webInnerComponentHtml = '';
  private $children = [];
  private $isCloseWebComponent = false;

  public function __construct(string $name, array $attrs, bool $isSelfClose)
  {
    $this->name = $name;
    $this->attributes = $attrs;
    $this->appendOuterHtml(TagMaker::getOpeningTag($name, $attrs, $isSelfClose));
  }

  public function __toString() : string
  {
    return $this->getOuterHtml();
  }

  public function getOuterHtml() : string
  {
    return $this->outerHtml;
  }

  public function getAttr()
  {
    return $this->attributes;
  }

  public function getTagName() : string
  {
    return $this->name;
  }

  public function getInnerHtml() : string
  {
    return $this->innerHtml;
  }

  public function closeTag()
  {
    $this->appendOuterHtml(TagMaker::getClosingTag($this->name));
  }

  public function getChildren() : array
  {
    return $this->children;
  }

  public function appendChild($child) : void
  {
    $this->children[] = $child;
    $this->appendOuterHtml($child->__toString());
    $this->appendInnerHtml($child->__toString());
    $this->appendWebComponentInnerHtml($child->__toString());
  }

  public function setInnerHtml(string $innerHtml): void
  {
    $this->innerHtml = $innerHtml;
  }

  public function isCloseWebComponent() : bool
  {
    return $this->isCloseWebComponent;
  }

  public function closeWebcomponent() : void
  {
    $this->isCloseWebComponent = true;
  }

  public function getWebComponentInnerHtml()
  {
    return $this->webInnerComponentHtml;
  }

  private function appendInnerHtml($value) : void
  {
    $this->innerHtml .= $value;
  }

  private function appendWebComponentInnerHtml($value) : void
  {
    $this->webInnerComponentHtml .= $value;
  }

  private function appendOuterHtml($value) : void
  {
    $this->outerHtml .= $value;
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
