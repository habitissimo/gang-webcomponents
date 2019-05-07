<?php
declare(strict_types=1);

namespace Gang\WebComponents\Parser;

use Diggin\HTMLSax\HTMLSax;
use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Nodes\WebComponent;

class NewParser

{
  private $stack;
  private $children_stack;
  private $response = [];
  private $parser;

  public function __construct()
  {
    $this->stack = new Stack();
    $this->children_stack = new Stack();
    $this->reset();
  }

  public static function isWebComponent(string $tagName): bool
  {
    return ucfirst($tagName) === $tagName || preg_match("/^h-.*/", $tagName);
  }

  /**
   * Returns an iterator that produces Plain HTML nodes or entire WebComponents nodes
   * @param string $html
   * @return array
   */
  public function parse(string $html): array
  {
    $this->reset();
    $html = preg_replace("/<[ ]*\/[ ]*script[ ]*>/","</script>", $html );
    $this->parser->parse($html);
    $this->saveResponse();
    return $this->response;
  }

  private function reset(): void
  {
    $this->stack->reset();
    $this->children_stack->reset();
    $this->response = [];
    $this->makeParser();
  }

  private function makeParser(): void
  {
    $this->parser = new HTMLSax;
    $this->parser->set_object($this);
    $this->parser->set_element_handler('_startElementHandler','_endElementHandler');
    $this->parser->set_data_handler('_defaultHandler');
    $this->parser->set_escape_handler('_scapeElementHandler');
  }

  public function _scapeElementHandler($parser,$data){
     $this->_defaultHandler($parser,TagMaker::getOpeningTag("!{$data}"));
  }


  public function updateFragmentValue($data)
  {
    $fragment = $this->stack->peek();
    $fragment->appendValue($data);
  }

  public function _defaultHandler($parser, $data): void
  {
    $this->stackOrKeepFragment();
    $this->updateFragmentValue($data);
  }

  public function _startElementHandler($parser, $name, $attrs, $isSelfClose): void
  {
    if ($name === "script" && !isset($attrs['src'])) {
      $script = $parser->state_parser->scanUntilString("</script>");
      $this->stackOrKeepFragment();
      $this->updateFragmentValue(TagMaker::getOpeningTag($name, $attrs));
      $this->updateFragmentValue($script);
      $parser->state_parser->state = 1;
      return;
    }

    if ($this->isWebComponent($name)) {
      $this->stackWebComponent($name, $attrs, $isSelfClose);
    } else {
      $this->stackOrKeepFragment();
      $this->updateFragmentValue(TagMaker::getOpeningTag($name, $attrs, $isSelfClose));
    }
  }

  private function stackWebComponent($name, $attrs, $isSelfClose)
  {
    $webcomponent = new WebComponent($name, $attrs, $isSelfClose);
    if($isSelfClose) {
      $webcomponent->closeWebcomponent();
    }
    $this->stack->push($webcomponent);
  }

  private function stackOrKeepFragment(): void
  {
    $element = $this->stack->peek();

    if ($this->stack->length() === 0 || $element instanceof WebComponent) {
      $this->stack->push(new Fragment(''));
    }
  }

  public function _endElementHandler($parser, $name, $isSelfClose): void
  {
    if ($isSelfClose) {
      return;
    }

    if ($this->isWebComponent($name)) {
      if ($this->headIsFragment()) {
        $this->stack->moveHeadElementToStack($this->children_stack);
      }

      $element = $this->stack->peek();
      while ($element->isCloseWebComponent()) {
        $this->stack->moveHeadElementToStack($this->children_stack);

        if ($this->headIsFragment()) {
          $this->stack->moveHeadElementToStack($this->children_stack);
        }
        $element= $this->stack->peek();
      }

      $this->appendChildrenToWebComponent();

      $element->closeTag();
      $element->closeWebcomponent();
    } else {
      $this->stackOrKeepFragment();
      $this->updateFragmentValue(TagMaker::getClosingTag($name));
    }
  }


  private function headIsFragment()
  {
    $element = $this->stack->peek();
    return $element instanceof Fragment;
  }

  private function appendChildrenToWebComponent()
  {
    $webComponent = $this->stack->peek();

    foreach ($this->children_stack->getStack() as $childElement) {
      $webComponent->appendChild($childElement);
    }

    if ($this->stack->length() === 1){
      $this->saveResponse();
    }

    $this->children_stack->reset();
  }

  private function saveResponse ()
  {
    foreach ($this->stack->getStack() as $element) {
     $this->response[] = $element;
    }
    $this->stack->reset();
  }

}
