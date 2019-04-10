<?php


namespace Gang\WebComponents\Parser;


use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use phpDocumentor\Reflection\Types\Null_;

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
    if($data!=""){
      $this->addToBuffer("2",$data);
      array_push( $this->stack ,new Fragment($data));
    }
  }

  public function _voidElementHandler($parser, $name, $attrs): void
  {
    array_push($this->stack,[new WebComponent($name, $attrs), new Buffer()]);
    $this->addToBuffer("0",$name, $attrs);
  }

  public function _startElementHandler($parser, $name, $attrs): void
  {
    array_push($this->stack,[new WebComponent($name, $attrs), new Buffer()]);
    $this->addToBuffer("0",$name, $attrs);
  }

  public function _endElementHandler($parser, $name): void
  {
    $this->addToBuffer("1",$name);

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
    $innerHTml = '';

    foreach ($this->children_stack as $children){
      $webComponent[0]->setChildren($children[0]);
      $innerHTml .= $children[1];
    }

    $webComponent[0]->setInnerHtml($innerHTml);
    $webComponent[0]->setouterHtml($webComponent[1]->read());


    $this->children_stack = [];

//    if(count($this->stack)==1){
//      $this->saveResponse();
//    }

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

  private function addToBuffer($num, $data , array $attrs = null)
  {
    switch ($num){
      case "0":
        $this->addTag($data, $attrs, 'appendOpeningXmlTag');
        break;
      case "1":
        $this->addTag($data, null, 'appendClosingXmlTag');
        break;
      case "2":
        $this->addTag($data, null, 'append');
        break;
    }
  }

  private function addTag ($name, $attrs,  $hello){
    foreach ($this->stack as $buffer) {
      if (count($buffer) > 1) {
        if ($attrs == null) {
          $buffer[1]->$hello($name, $attrs);
        } else {
          $buffer[1]->$hello($name);
        }
      }
    }
  }

  private function saveResponse (){
    $component  = array_pop($this->stack);
    if($component instanceof Fragment){
      array_push($this->response, $component);
    }
    else{
      array_push($this->response, $component[0]);
    }
  }

}
