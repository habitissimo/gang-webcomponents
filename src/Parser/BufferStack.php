<?php


namespace Gang\WebComponents\Parser;


class BufferStack
{

  public function append(array $stack ,string $value): void
  {
    $this->addToBuffer($stack,'append', $value);
  }

  public function appendOpeningTag(array $stack, string $name, array $attrs=[]): void
  {
    $this->addToBuffer($stack, 'appendOpeningXmlTag', $name, $attrs);
  }


  public function appendSelfClosingTag(array $stack, string $name, array $attrs=[]): void
  {
    $this->addToBuffer($stack,'appendOpeningXmlTag', $name, $attrs, true);
  }

  public function appendClosingTag(array $stack , string $name): void
  {
    $this->addToBuffer($stack,'appendClosingXmlTag', $name);
  }

  private function addToBuffer(arra $stack, $methodName, $data, array $attrs = null, $selfClosing=false)
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
