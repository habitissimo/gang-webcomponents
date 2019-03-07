<?php

declare(strict_types=1);
namespace Gang\WebComponents\Parser;

class InnerHtmlExtractor
{
    private $parser;
    private $buffer;
    private $outer;
    private $depth;

    public function __construct()
    {
        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_default_handler($this->parser, "defaultHandler");
        xml_set_element_handler($this->parser, "startHandler", "endHandler");
    }

    public function __destruct()
    {
        xml_parser_free($this->parser);
        unset($this->parser);
    }

    public function extract(string $html)
    {
        $this->outer = false;
        $this->depth = 0;
        $this->buffer = new Buffer();
        xml_parse($this->parser, $html);

        return $this->buffer->read();
    }

    private function startHandler($parser, $name, $attrs)
    {
        if (false === $this->outer) {
            $this->outer = $name;
            return;
        }

        if ($name === $this->outer) {
            $this->depth ++;
        }

        $this->buffer->appendOpeningXmlTag($name, $attrs);
    }

    private function endHandler($parser, $name)
    {
        if ($name === $this->outer) {
            $this->depth --;
        }

        if ($this->depth < 0) {
            return;
        }

        $this->buffer->appendClosingXmlTag($name);
    }

    private function defaultHandler($parser, $data)
    {
        $this->buffer->append($data);
    }
}
