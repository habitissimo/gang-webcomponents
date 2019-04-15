<?php

namespace Gang\WebComponents\Parser;


class Stack
{
    private $stack = [];

    public function push($element)
    {
          array_push($this->stack,$element);
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

    public function unshift($element)
    {
      array_unshift($this->stack, $element);
    }

    public function reset()
    {
      $this->stack = [];
    }

    public function moveHeadElementToStack(Stack $stack){
      $stack  ->unshift($this->pop());
    }
}
