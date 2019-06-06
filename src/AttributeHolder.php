<?php
declare(strict_types=1);

namespace Gang\WebComponents;

class AttributeHolder
{
  private $data = [];

  public function add($name, $value)
  {
    $this->data[$name] = $value;
  }

  public function get($name, $default = null)
  {
    return $this->data[$name] ?? $default;
  }

  public function has($name)
  {
    return isset($this->data[$name]);
  }

  public function toArray()
  {
    return $this->data;
  }

  public function getData() {
    return $this->data;
  }
}
