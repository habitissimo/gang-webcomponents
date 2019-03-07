<?php
declare(strict_types=1);
namespace Gang\WebComponents\Parser\Nodes;

use Gang\WebComponents\Contracts\NodeInterface;

abstract class Text implements NodeInterface
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
