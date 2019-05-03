<?php
declare(strict_types=1);
namespace Gang\WebComponents\Parser;

/**
 * Acts as a buffer to accumulate HTML as it's read from the parser
 */
class Buffer
{
    private $content = [];

    public function append(string $value): void
    {
        $this->content[] = $value;
    }

    public function read(): string
    {
        $value = implode('', $this->content);
        $this->content = [];

        return $value;
    }

    public function empty(): bool
    {
        return count($this->content) === 0;
    }
}
