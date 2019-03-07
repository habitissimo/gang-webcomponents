<?php
declare(strict_types=1);
namespace Gang\WebComponents\Parser;

class SaxLikeParser
{
    private $defaultHandler = null;
    private $startElementHandler = null;
    private $endElementHandler = null;
    private $voidElementHandler = null;

    public function parse(string $content)
    {
        $matches = [];

        preg_match_all("/<[\/]?[A-Z][^>]*>/", $content, $matches, PREG_OFFSET_CAPTURE);

        $offsets = [];
        foreach ($matches[0] as list($tag, $offset)) {
            $offsets[] = $offset;
            $offsets[] = $offset + strlen($tag);
        }
        $offsets[] = strlen($content);

        $prev = 0;
        foreach ($offsets as $offset) {
            $this->process(substr($content, $prev, $offset - $prev));
            $prev = $offset;
        }
    }

    public function setDefaultHandler(callable $handler)
    {
        $this->defaultHandler = $handler;
    }

    public function setElementHandler(callable $startElementHandler, callable $endElementHandler)
    {
        $this->startElementHandler = $startElementHandler;
        $this->endElementHandler = $endElementHandler;
    }

    public function setVoidElementHandler(callable $voidElementHandler)
    {
        $this->voidElementHandler = $voidElementHandler;
    }

    private function process(string $node)
    {
        if ($this->isSelfClosingTag($node)) {
            $extractor = new XmlAttrExtractor();
            $extractor->with($node);
            call_user_func(
                $this->voidElementHandler,
                $this,
                $extractor->getName(),
                $extractor->getAttrs()
      );
        } elseif ($name = $this->isEndTag($node)) {
            call_user_func(
                $this->endElementHandler,
                $this,
                $name
      );
        } elseif ($this->isStartTag($node)) {
            $extractor = new XmlAttrExtractor();
            $extractor->with($node);
            call_user_func(
                $this->startElementHandler,
                $this,
                $extractor->getName(),
                $extractor->getAttrs()
      );
        } else {
            call_user_func($this->defaultHandler, $this, $node);
        }
    }

    private function getTagName(string $node): string
    {
        $extractor = new XmlAttrExtractor();
        return $extractor->with($node)->getName();
    }

    private function getTagAttributes(string $node): array
    {
        $extractor = new XmlAttrExtractor();
        return $extractor->with($node)->getAttrs();
    }

    private function isSelfClosingTag(string $node): bool
    {
        return 1 === preg_match("/<[A-Z][^>]*\/>/", $node);
    }

    private function isStartTag(string $node): bool
    {
        return 1 === preg_match("/<[A-Z][^>]*>/", $node);
    }

    private function isEndTag(string $node): string
    {
        preg_match("/<\/([A-Z][^>]*)>/", $node, $m);
        if (count($m)) {
            return $m[1];
        }
        return "";
    }
}
