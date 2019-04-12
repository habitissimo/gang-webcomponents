<?php


namespace Gang\WebComponents\Parser;


class BufferStack
{

  public function append(array $stack ,string $value): void
  {
    $this->addToBuffer($stack,'append', $value);
  }

  public function appendOpeningTag(array $stack, string $name, array $attrs=[], bool $selfClosing): void
  {
    $this->addToBuffer($stack, 'appendOpeningXmlTag', $name, $attrs, $selfClosing);
  }

  public function appendClosingTag(array $stack , string $name): void
  {
    $this->addToBuffer($stack,'appendClosingXmlTag', $name);
  }

  private function addToBuffer(array $stack, $methodName, $data, array $attrs = null, $selfClosing=false)
  {
    $data = $this->cleanString($data);
    foreach ($stack as [$_, $buffer]) {
      if (is_array($attrs) || $selfClosing) {
        $buffer->$methodName($data, $attrs, $selfClosing);
      } else {
        $buffer->$methodName($data);
      }
    }
  }

  private function cleanString(string $data) : string
  {
    $data = str_replace("\n","", $data);
    return $data;
 }

}
