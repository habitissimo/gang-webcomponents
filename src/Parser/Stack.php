<?php

namespace Gang\WebComponents\Parser;


class Stack
{
  private $stack = [];
  private $lengthStack = 0;
  private $lastValueStack = null;

  public function push($element)
  {
    $this->lengthStack++;
    $this->lastValueStack = $element;
    array_push($this->stack,$element);
  }

  public function pop()
  {
    $array_pop = array_pop($this->stack);
    $this->lengthStack--;
    $this->lastValueStack = end($this->stack);
    return $array_pop;
  }

  public function peek()
  {
    return $this->lastValueStack;
  }

  public function length()
  {
    return $this->lengthStack;
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
    $this->lengthStack = 0;
    $this->lastValueStack = null;
  }

  public function moveHeadElementToStack(Stack $stack){
    $stack->unshift($this->pop());
  }
}
