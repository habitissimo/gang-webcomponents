<?php

namespace Gang\WebComponentsTests;

use Gang\WebComponents\Parser\InnerHTMLExtractor;
use PHPUnit\Framework\TestCase;

class InnerHTMLExtractorTest extends TestCase
{
    private $extractor;

    public function setUp()
    {
        $this->extractor = new InnerHTMLExtractor();
    }

    public function testSelfClosingWithAttrs() : void
    {
        $input = "<Button href='patata'/>";
        $html = InnerHTMLExtractor::extract($input, 'Button');
        $this->assertEquals("",$html);
    }

    public function testSelfClosingWithoutAttrs() : void
    {
        $input = "<Button/>";
        $html = InnerHTMLExtractor::extract($input, 'Button');
        $this->assertEquals("",$html);
    }

    public function testChildlessNonSelfclosing() : void
    {
        $input = "<Button href='patata'></Button>";
        $html = InnerHTMLExtractor::extract($input, 'Button');
        $this->assertEquals("",$html);
    }

    public function testNonSelfclosingWithChildren() : void
    {
        $input = "<Button href='patatilla'><Icon></Icon> Holi</Button>";
        $html = InnerHTMLExtractor::extract($input, 'Button');
        $this->assertEquals("<Icon></Icon> Holi",$html);
    }

    public function testAttributeWithHtmlInside() : void
    {
        $input = '<Button href="patata" data="<Button></Button>"></Button>';
        $html = InnerHTMLExtractor::extract($input, 'Button');
        $this->assertEquals("",$html);
    }
}
