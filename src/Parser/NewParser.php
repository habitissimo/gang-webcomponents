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

  public static function isWebComponent(string $tagName): bool
  {
    return preg_match("/^[A-Z].*/", $tagName) && ucfirst($tagName) === $tagName;
  }

  /**
   * Returns an iterator that produces Plain HTML nodes or entire WebComponents nodes
   * @param string $html
   * @return array
   */
  public function parse(string $html): array
  {
    $this->reset();
    $this->parser->parse($html);
    $this->saveResponse();

    return $this->response;
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

  private function cleanString(string $data) : string
  {
    $data = str_replace("\n","", $data);

    return $data;
  }

  private function stackOrKeepFragment(): void
  {
    [$element, $_] = end($this->stack);

    if (count($this->stack) === 0 || $element instanceof WebComponent) {
      array_push($this->stack, [new Fragment(''), new Buffer()]);
    }
  }

  private function stackWebComponent($name, $attrs)
  {
    array_push($this->stack,[new WebComponent($name, $attrs), new Buffer()]);
  }

  public function updateFragmentValueFromBuffer()
  {
      [$fragment, $buffer] = end($this->stack);
      $current_content = $fragment->__toString();
      $fragment->setValue($current_content . $buffer->read());
  }

  public function _defaultHandler($parser, $data): void
  {
    $this->stackOrKeepFragment();
    $this->addToBuffer("append", $data);
    $this->updateFragmentValueFromBuffer();
  }

  public function _startElementHandler($parser, $name, $attrs, $isSelfClose): void
  {
    if ($this->isWebComponent($name)) {
      $this->startWebComponentHandler($name, $attrs, $isSelfClose);
    } else {
      $this->stackOrKeepFragment();
      $this->addToBuffer("appendOpeningXmlTag", $name, $attrs, $isSelfClose);
      $this->updateFragmentValueFromBuffer();
    }
  }

  private function startWebComponentHandler($name, $attrs, $isSelfClose)
  {
    $this->stackWebComponent($name, $attrs);
    $this->addToBuffer("appendOpeningXmlTag", $name, $attrs, $isSelfClose);
    if ($isSelfClose) {
      $webComponent = end($this->stack);
      $this->setElementInnerHtml($webComponent, '');
    }
  }

  public function _endElementHandler($parser, $name, $isSelfClose): void
  {
    if ($isSelfClose) {
      return;
    }

    if ($this->isWebComponent($name)) {
      $this->addToBuffer("appendClosingXmlTag", $name);

      if ($this->headIsFragment()) {
        $this->moveHeadFragmentToChildrenStack();
      }

      [$element, $_] = end($this->stack);
      while ($name !== $element->getTagName()) {
        $this->moveHeadWebComponentToChildrenStack();
        if ($this->headIsFragment()) {
          $this->moveHeadFragmentToChildrenStack();
        }
        [$element, $_] = end($this->stack);
      }

      $this->foo();
    } else {
      $this->stackOrKeepFragment();
      $this->addToBuffer("appendClosingXmlTag", $name);
      $this->updateFragmentValueFromBuffer();
    }
  }

  private function headIsFragment()
  {
    [$element, $_] = end($this->stack);
    return $element instanceof Fragment;
  }

  private function moveHeadFragmentToChildrenStack()
  {
    [$fragment, $_] = array_pop($this->stack);
    array_push($this->children_stack, [$fragment, $fragment->__toString()]);
  }

  private function moveHeadWebComponentToChildrenStack()
  {
    [$childElement, $_] = array_pop($this->stack);
    array_push($this->children_stack, [$childElement, $childElement->getOuterHtml()]);
  }

  private function foo()
  {
    [$webComponent, $webComponentBuffer] = end($this->stack);

    $this->children_stack = array_reverse($this->children_stack);

    $innerHtml = '';
    foreach ($this->children_stack as [$childElement, $childContent]) {
      $webComponent->appendChild($childElement);
      $innerHtml .= $childContent;
    }

    $this->setElementInnerHtml([$webComponent, $webComponentBuffer], $innerHtml);
  }

  private function setElementInnerHtml($webComponent, $innerHtml='')
  {
    [$element, $buffer] = $webComponent;
    $element->setInnerHtml($innerHtml);
    $element->setOuterHtml($buffer->read());

    if (count($this->stack) === 1){
      $this->saveResponse();
    }
  }

  private function saveResponse ()
  {
    foreach ($this->stack as [$element, $_]) {
        array_push($this->response, $element);
    }
    $this->stack = [];
  }

  private function addToBuffer($methodName, $data, array $attrs = null, $selfClosing=false)
  {
    $data = $this->cleanString($data);
    foreach ($this->stack as [$_, $buffer]) {
      if (is_array($attrs) || $selfClosing) {
        $buffer->$methodName($data, $attrs, $selfClosing);
      } else {
        $buffer->$methodName($data);
      }
    }
  }
}
