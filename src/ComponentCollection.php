<?php

namespace Gang\WebComponents;

class ComponentCollection
{
    private $items = [];

    public function __construct($items = [])
    {
        $this->appendChildrens($items);
    }

    public function __toString()
    {
        return implode('', $this->items);
    }

    public function append($element)
    {
        $this->items[] = $element;
    }

    public function appendChildrens(iterable $items)
    {
        foreach ($items as $element) {
            $this->append($element);
        }
    }

    public function setChildren(iterable $items)
    {
        $this->items = [];
        $this->appendChildrens($items);
    }
}
