<?php


namespace Gang\WebComponents\Parser;


use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use phpDocumentor\Reflection\Types\Null_;

class NewParser
{
  private $deep = 0;
  private $buffers = [];
  private $current_webcomponent;
  private $current_webcomponent_attrs = [];
  private $response = [];

  public function __construct()
  {
    $this->reset();
  }

  private function reset(): void
  {
    $this->deep = 0;
    $this->current_webcomponent = null;
    $this->current_webcomponent_attrs = [];
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

  public function _defaultHandler($parser, $data): void
  {
    $this->addToBuffer("2",$data);
  }

  public function _voidElementHandler($parser, $name, $attrs): void
  {
    dd($name);
  }

  public function _startElementHandler($parser, $name, $attrs = []): void
  {
    if ($this->opensNewComponent($name)) {
      $this->current_webcomponent = new WebComponent($name, $attrs);
      $this->deep = 0;
      array_push($this->buffers, new Buffer());
    } else {

      if ($this->matchesCurrentComponent($name)) {
        $this->deep++;
      }

      $child = $this->getLastChild($this->current_webcomponent);

      if($child->isCloseTag()){

        $fhader = $this->getFatherComponent($this->current_webcomponent, $child);
        $fhader->setChildren(new WebComponent($name, $attrs));

      }else{

        $child->setChildren(new WebComponent($name, $attrs));

      }
      array_push($this->buffers, new Buffer());
    }

    $this->addToBuffer("0", $name, $attrs);
  }

  public function _endElementHandler($parser, $name): void
  {
    $this->addToBuffer(1,$name);
    $this->closeComponentTag($this->current_webcomponent, $name);
    $child = $this->findComponent($this->current_webcomponent, $name);
    $this->findWebComponent = [];
    d(["WEB COMPONENT ENCONTRADO", $child]);
    d(["BUFFER", $this->buffers]);
    $child->setOuterHtml(array_pop($this->buffers)->read());
  }

  private function bufferToFragment(): void
  {
//    if (!$this->buffer->empty()) {
//      $this->response[] = new Fragment($this->buffer->read());
//    }
  }

  private function bufferToWebComponent(): void
  {
//    if (!$this->buffer->empty()) {
//      $this->response[] = new WebComponent(
//        $this->buffer->read(),
//        $this->current_webcomponent,
//        $this->current_webcomponent_attrs
//      );
//    }
//    $this->current_webcomponent = null;
//    $this->current_webcomponent_attrs = [];
  }

  private function opensNewComponent($name): bool
  {
    return null === $this->current_webcomponent;
  }

  private function matchesCurrentComponent($name): bool
  {
    return $name === $this->current_webcomponent->getTagName();
  }

  private function insideComponent(): bool
  {
    return null !== $this->current_webcomponent;
  }

  /**
   * Returns an iterator that produces Plain HTML nodes or entire WebComponents nodes
   */
  public function parse(string $html): array
  {
    $this->reset();
    $success = $this->parser->parse($html);
//    d($this->current_webcomponent);
//    dd($this->buffers);
    // process remaining buffer
    // $this->bufferToFragment();
    dd($this->current_webcomponent);
    return $this->response;
  }

  public static function isWebComponent(string $tagName): bool
  {

    return preg_match("/^[A-Z].*/", $tagName) && ucfirst($tagName) === $tagName;
  }

  private $lastChild;
  private function getLastChild(WebComponent $webComponent)
  {
    $childs = $webComponent->getChildren();
    $child = end($childs);


    if(!$child){

      $this->lastChild = $webComponent;

    }else {

      if (count($child->getChildren()) > 0) {

        $this->getLastChild(end($child->getChildren()));

      } else {

        $this->lastChild =  $child;

      }

    }

    return $this->lastChild;
  }

  private function getFatherComponent(WebComponent $webComponent, WebComponent $child){
    foreach ($webComponent->getChildren() as $fhader){
      if($child==$fhader){
        return $webComponent;
      }else{
        $this->getFatherComponent($fhader, $child);
      }
    }
  }

  private $findWebComponent = [];
  private function findComponent(WebComponent $webcomponent , string $name){

    if ($webcomponent->getTagName()==$name && $webcomponent->isCloseTag()){
      array_push($this->findWebComponent, $webcomponent);
    }
    if(!empty($webcomponent->getChildren())){
      foreach ($webcomponent->getChildren() as $child){
        $this->findComponent($child, $name);
      }
    }

    return reset($this->findWebComponent);

  }

  private $webComponenetsToClose = [];

  private function closeComponentTag(WebComponent $webComponent, string $name)
  {
    if(!$webComponent->isCloseTag() && $webComponent->getTagName()==$name){
      array_push( $this->webComponenetsToClose, $webComponent);
    }
    foreach ($webComponent->getChildren() as $child) {
      $this->closeComponentTag($child, $name);
    }

    $wc = array_pop($this->webComponenetsToClose);

    if($wc!=null){
      $wc->closeTag();
      $this->webComponenetsToClose = [];
    }

  }

  private function addToBuffer($num, $data , array $attrs = null)
  {
    switch ($num){
      case "0":
        $this->addOnpeingXmlTag($data, $attrs);
        break;
      case "1":
        $this->addCloseXmlTag($data);
        break;
      case "2":
        $this->addDefaultData($data);
        break;
    }
  }

  private function addOnpeingXmlTag ($name, array $attrs){
    foreach ($this->buffers as $buffer){
      $buffer->appendOpeningXmlTag($name, $attrs);
    }

  }

  private function addCloseXmlTag ($name){
    foreach ($this->buffers as $buffer){
      $buffer->appendClosingXmlTag($name);
    }
  }

  private function addDefaultData ($data){
    if(!$data==""){
      if(empty ( $this->buffers)){
        array_push($this->buffers, new Buffer());
      }
      foreach ($this->buffers as $buffer){
        $buffer->append($data);
      }
    }
  }



}
