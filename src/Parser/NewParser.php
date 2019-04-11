<?php


namespace Gang\WebComponents\Parser;


use Diggin\HTMLSax\HTMLSax;
use DigginTest\HTMLSax\ListenerInterface;
use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Nodes\WebComponent;


class NewParser
{
  private $stack = [];
  private $children_stack = [];
  private $response = [];
  private $parser;

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

    $this->parser = new HTMLSax;
    $this->parser->set_object($this);
    $this->parser->set_element_handler('_startElementHandler','_endElementHandler');
    $this->parser->set_data_handler('_defaultHandler');

  }

  /**
   * Returns an iterator that produces Plain HTML nodes or entire WebComponents nodes
   * @param string $html
   * @return array
   */
  public function parse(string $html): array
  {
    $this->reset();
    $success = $this->parser->parse($html);

    $this->saveResponse();

    return $this->response;
  }

  public function _defaultHandler($parser, $data,  $attrs =  null , bool $isHtmlTag = false, bool $isCloseTag = false, $isSelfClose= false): void
  {
    $data = trim($data);
    $data = str_replace("\n","", $data);

    if ($data === "") {
      return;
    }

    $component = end($this->stack);

    if (!$component[0] instanceof Fragment || !$component) {
      array_push($this->stack, [new Fragment(''), new Buffer()]);
    }

    if ($isHtmlTag) {
      if ($isSelfClose) {
        $this->addToBuffer("appendOpeningXmlTag", $data, $attrs, true);
      } elseif ($isCloseTag) {
        $this->addToBuffer("appendClosingXmlTag", $data);
      } else {
        $this->addToBuffer("appendOpeningXmlTag", $data, $attrs);
      }
    } else {
      $this->addToBuffer("append", $data);
    }

    $component = end($this->stack);

    $component[0]->setValue($component[0]->__toString() . $component[1]->read());
  }

  public function _voidElementHandler($parser, $name, $attrs): void
  {

    array_push($this->stack,[new WebComponent($name, $attrs), new Buffer()]);
    $this->addToBuffer("appendOpeningXmlTag",$name, $attrs, true);
    $webComponent = end($this->stack);
    $this->addComponent($webComponent, '');

  }

  public function _startElementHandler($parser, $name, $attrs, $isSelfClose): void
  {

    if(!$this->isWebComponent($name)){
      $isSelfClose ? $this->_defaultHandler($parser, $name, $attrs, true, false, true) :
        $this->_defaultHandler($parser, $name, $attrs, true);
    }else{
      if($isSelfClose){
        $this->_voidElementHandler($parser, $name, $attrs);
      } else {
        array_push($this->stack, [new WebComponent($name, $attrs), new Buffer()]);
        $this->addToBuffer("appendOpeningXmlTag", $name, $attrs);
      }
    }

  }

  public function _endElementHandler($parser, $name, $isSelfClose): void
  {

    if (!$isSelfClose) {
      if (!$this->isWebComponent($name)) {
        $this->_defaultHandler($parser, $name, null, true, true);
      } else {
        $this->addToBuffer("appendClosingXmlTag", $name);

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

        foreach ($this->children_stack as $children) {
          $webComponent[0]->setChildren($children[0]);
          $innerHtml .= $children[1];
        }
        $this->addComponent($webComponent, $innerHtml);
      }
    }

  }

  private function addComponent ($webComponent, $innerHtml='')
  {

    $webComponent[0]->setInnerHtml($innerHtml);
    $webComponent[0]->setouterHtml($webComponent[1]->read());

    $this->children_stack = [];
    if (count($this->stack)==1){
      $this->saveResponse();
    }

  }

  private function processFragment($component)
  {

    if ($component[0] instanceof Fragment) {
      $fragment = array_pop($this->stack)[0];
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
        if (is_array($attrs) || $selfClosing) {
          $buffer[1]->$type($data, $attrs, $selfClosing);
        } else {
          $buffer[1]->$type($data);
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
