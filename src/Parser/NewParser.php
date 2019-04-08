<?php


namespace Gang\WebComponents\Parser;


use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Nodes\WebComponent;

class NewParser
{


  private $deep = 0;
  private $buffer;
  private $current_webcomponent = '';
  private $current_webcomponent_attrs = [];
  private $response = [];

  public function __construct()
  {
    $this->reset();
  }

  private function reset(): void
  {
    $this->buffer = new Buffer();
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

    $this->buffer->append($data);
  }

  public function _voidElementHandler($parser, $name, $attrs): void
  {

  }

  public function _startElementHandler($parser, $name, $attrs=[]): void
  {

    if ($this->opensNewComponent($name)) {
      $this->bufferToFragment();
      $this->current_webcomponent = $name;
      $this->current_webcomponent_attrs = $attrs;
      $this->deep = 0;
    } elseif ($this->matchesCurrentComponent($name)) {
      $this->deep ++;
    }

    $this->buffer->appendOpeningXmlTag($name, $attrs);
  }

  public function _endElementHandler($parser, $name): void
  {

    $this->buffer->appendClosingXmlTag($name);

    if ($this->matchesCurrentComponent($name)) {
      if ($this->deep > 0) {
        $this->deep --;
      } else {
        $this->bufferToWebComponent();
      }
    }
  }

  private function bufferToFragment(): void
  {
    if (!$this->buffer->empty()) {
      $this->response[] = new Fragment($this->buffer->read());
    }
  }

  private function bufferToWebComponent(): void
  {
    if (!$this->buffer->empty()) {
      $this->response[] = new WebComponent(
        $this->buffer->read(),
        $this->current_webcomponent,
        $this->current_webcomponent_attrs
      );
    }
    $this->current_webcomponent = null;
    $this->current_webcomponent_attrs = [];
  }

  private function opensNewComponent($name): bool
  {
    return null === $this->current_webcomponent;
  }

  private function matchesCurrentComponent($name): bool
  {
    return $name === $this->current_webcomponent;
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

    // process remaining buffer
    $this->bufferToFragment();

    return $this->response;
  }

  public static function isWebComponent(string $tagName) : bool
  {

    return preg_match("/^[A-Z].*/", $tagName) && ucfirst($tagName) === $tagName;
  }

}
