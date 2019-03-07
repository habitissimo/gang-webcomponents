<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Parser\InnerHtmlExtractor;

class InnerHtmlExtractorTest extends TestCase
{
    private $extractor;

    public function setUp()
    {
        $this->x = new InnerHtmlExtractor();
    }

    /** @test */
    public function should_extract_inner_content() : void
    {
        $input = "<Fragment><Button>Hi</Button></Fragment>";
        $x = $this->x->extract($input);

        $this->assertEquals("<Button>Hi</Button>", $x);
    }

    /** @test */
    public function should_extract_inner_content_with_attributes() : void
    {
        $input = "<Fragment><Button foo=\"asd\">Hi</Button></Fragment>";
        $x = $this->x->extract($input);

        $this->assertEquals("<Button foo=\"asd\">Hi</Button>", $x);
    }

    /** @test */
    public function should_extract_inner_content_with_entities() : void
    {
        $input = "<Fragment><Button foo=\"&ntilde;\">Hi</Button></Fragment>";
        $x = $this->x->extract($input);

        $this->assertEquals("<Button foo=\"&ntilde;\">Hi</Button>", $x);
    }
}
