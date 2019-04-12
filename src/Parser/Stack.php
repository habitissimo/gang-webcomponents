<?php

namespace Gang\WebComponents\Parser;


class Stack
{
    private $stack = [];

    public function push($element, $buffer)
    {
          array_push($this->stack, [$element, $buffer]);
    }

    public function pop()
    {
      return array_pop($this->stack);
    }

    public function peek()
    {
      return end($this->stack);
    }

    public function length()
    {
      return count($this->stack);
    }

    public function getStack()
    {
      return $this->stack;
    }

    public function reset()
    {
      $this->stack = [];
    }
}
