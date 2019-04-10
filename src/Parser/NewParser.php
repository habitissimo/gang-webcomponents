<?php


namespace Gang\WebComponents\Parser;


use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Nodes\WebComponent;


class NewParser
{
  private $stack = [];
  private $children_stack = [];
  private $response = [];

  public function __construct()
  {
    $this->reset();
  }

  private function reset(): void
  {
    $this->stack = [];
    $this->children_stack = [];
    $this->response = [];
    $this->makeParser();
  }

  private function makeParser(): void
  {
    $this->parser = new SaxLikeParser();
    $this->parser->setDefaultHandler([$this, "_defaultHandler"]);
    $this->parser->setElementHandler(
      [$this, "_startElementHandler"],
      [$this, "_endElementHandler"]);
    $this->parser->setVoidElementHandler([$this, "_voidElementHandler"]);
  }

  /**
   * Returns an iterator that produces Plain HTML nodes or entire WebComponents nodes
   */
  public function parse(string $html): array
  {
    $this->reset();
    $success = $this->parser->parse($html);

    $this->saveResponse();

    return $this->response;
  }

  public function _defaultHandler($parser, $data): void
  {
    $data = trim($data);
    $data = str_replace("\n","", $data);

    if($data!=""){
      $this->addToBuffer("append",$data);
      array_push( $this->stack ,new Fragment($data));
    }
  }

  public function _voidElementHandler($parser, $name, $attrs): void
  {
    array_push($this->stack,[new WebComponent($name, $attrs), new Buffer()]);
    $this->addToBuffer("appendOpeningXmlTag",$name, $attrs, true);
    $webComponent = end($this->stack);
    $this->addComponent($webComponent, '');
  }

  public function _startElementHandler($parser, $name, $attrs): void
  {
    array_push($this->stack,[new WebComponent($name, $attrs), new Buffer()]);
    $this->addToBuffer("appendOpeningXmlTag",$name, $attrs);
  }

  public function _endElementHandler($parser, $name): void
  {

    $this->addToBuffer("appendClosingXmlTag",$name);

    $component = end($this->stack);
    $component = $this->processFragment($component);

    while ($name != $component[0]->getTagName()) {

      $childComponent = array_pop($this->stack);

      array_push($this->children_stack, [$childComponent[0], $childComponent[0]->getOuterHtml()]);
      $component = end($this->stack);
      $component = $this->processFragment($component);
    }

    $webComponent = end($this->stack);
    $this->children_stack = array_reverse($this->children_stack);
    $innerHtml = '';

    foreach ($this->children_stack as $children){
      $webComponent[0]->setChildren($children[0]);
      $innerHtml .= $children[1];
    }
    $this->addComponent($webComponent, $innerHtml);
  }

  private function addComponent ($webComponent, $innerHtml=''){
    $webComponent[0]->setInnerHtml($innerHtml);
    $webComponent[0]->setouterHtml($webComponent[1]->read());

    $this->children_stack = [];
    if (count($this->stack)==1){
      $this->saveResponse();
    }

  }

  private function processFragment($component){

    if ($component instanceof Fragment) {
      $fragment = array_pop($this->stack);
      array_push($this->children_stack, [$fragment, $fragment->__toString()]);
      return $component = end($this->stack);
    }

    return $component;
  }

  public static function isWebComponent(string $tagName): bool
  {

    return preg_match("/^[A-Z].*/", $tagName) && ucfirst($tagName) === $tagName;
  }

  private function addToBuffer($type, $data , array $attrs = null, $selfClosing=false)
  {
    foreach ($this->stack as $buffer) {
      if (count($buffer) > 1) {
        if ($attrs != null||$selfClosing) {
          $buffer[1]->$type($data, $attrs, $selfClosing);
        } else {
          $buffer[1]->$type($data);
        }
      }
    }
  }


  private function saveResponse ()
  {
    foreach ($this->stack as $content) {
      if (count($content)>1){
        array_push($this->response, $content[0]);
      }else{
        array_push($this->response, $content);
      }
    }
    $this->stack = [];
  }

}
