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
        $safe_value = addslashes($value);
        $tag.=  " {$attr}=\"{$safe_value}\"";
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
}
