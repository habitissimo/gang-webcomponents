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

    /** @test */
    public function should_extract_inner_content() : void
    {
        $input = "<Fragment><Button>Hi</Button></Fragment>";
        $x = InnerHTMLExtractor::extract($input, 'Fragment');
        $this->assertEquals("<Button>Hi</Button>", $x);
    }

    /** @test */
    public function should_extract_inner_content_with_attributes() : void
    {
        $input = "<Fragment><Button foo=\"asd\">Hi</Button></Fragment>";
        $x = InnerHTMLExtractor::extract($input,"Fragment");

        $this->assertEquals("<Button foo=\"asd\">Hi</Button>", $x);
    }

    /** @test */
    public function should_extract_inner_content_with_entities() : void
    {
        $input = "<Fragment><Button foo=\"&ntilde;\">Hi</Button></Fragment>";
        $x = InnerHTMLExtractor::extract($input, 'Fragment');
        $this->assertEquals("<Button foo=\"&ntilde;\">Hi</Button>", $x);
    }    
}
