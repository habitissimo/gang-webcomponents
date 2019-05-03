<?php


namespace Gang\WebComponents\Parser;


class TagMaker
{
  static function getOpeningTag(string $name, array $attrs=[], $selfClosing=false): string
  {
    $tag = '<';
    $tag .= $name;

    foreach ($attrs as $attr => $value) {
      if ($value){
        $quote = (new self)->getMoreAppropriateQuotes($value);
        $tag.=  " {$attr}={$quote}{$value}{$quote}";
      }else{
        $tag.=  " {$attr}";
      }
    }

    $tag .= $selfClosing ? '/>' : '>';

    return $tag;
  }

  static function getClosingTag($name): string
  {
    return "</{$name}>";
  }

  private function getMoreAppropriateQuotes($value)
  {
    return strpos($value, "\"") ? '\'' : "\"";
  }
}
