<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Parser\XmlAttrExtractor;

class XmlAttrExtractorTest extends TestCase
{
    private $extractor;

    public function setUp()
    {
        $this->x = new XmlAttrExtractor();
    }

    /** @test */
    public function should_parse_matched_tags() : void
    {
        $x = $this->x->with("<input name=\"foo\"></input>");

        $this->assertEquals("input", $x->getName());
        $this->assertEquals(["name" => "foo"], $x->getAttrs());
    }

    /** @test */
    public function should_parse_unmatched_open_tags() : void
    {
        $x = $this->x->with("<input name=\"foo\">");

        $this->assertEquals("input", $x->getName());
        $this->assertEquals(["name" => "foo"], $x->getAttrs());
    }

    /** @test */
    public function should_be_able_to_parse_html_inside_attributes() : void
    {
        $x = $this->x->with("<Title value='<strong>Hola</strong>'>");

        $this->assertEquals(["value" => '<strong>Hola</strong>'], $x->getAttrs());
    }

    /** @test */
    public function should_parse_self_closing_tags() : void
    {
        $x = $this->x->with("<input name=\"foo\"/>");

        $this->assertEquals("input", $x->getName());
        $this->assertEquals(["name" => "foo"], $x->getAttrs());
    }

    /** @test */
    public function should_accept_single_character_tagnames_if_they_have_attributes() : void
    {
        $x = $this->x->with("<a href='foo'>");

        $this->assertEquals("a", $x->getName());
        $this->assertEquals(["href" => "foo"], $x->getAttrs());
    }

    /** @test */
    public function should_parse_single_character_tagnames() : void
    {
        $x = $this->x->with("<a>");

        $this->assertEquals("a", $x->getName());
        $this->assertEquals([], $x->getAttrs());
    }

    /** @test */
    public function should_parse_names_in_case_sensitive_mode() : void
    {
        $x = $this->x->with("<Fragment>");

        $this->assertEquals("Fragment", $x->getName());
    }



    /** @test */
    public function should_parse_attributes_without_value() : void
    {
        $x = $this->x->with("<input required />");

        $this->assertEquals(['required' => ''], $x->getAttrs());
    }

    /** @test */
    public function will_not_fail_for_single_character_tagnames_if_paired() : void
    {
        $x = $this->x->with("<a></a>");

        $this->assertEquals("a", $x->getName());
    }

    /** @test */
    public function should_allow_for_html_entities() : void
    {
        $x = $this->x->with("<input entity='&ntilde;'></input>");

        $this->assertEquals("input", $x->getName());
        $this->assertEquals(["entity" => 'ñ'], $x->getAttrs());
    }

    /** @test */
    public function should_accept_unicode_values() : void
    {
        $x = $this->x->with("<Button title='Menú'></Button>");

        $this->assertEquals("Button", $x->getName());
        $this->assertEquals(["title" => 'Menú'], $x->getAttrs());
    }

    /** @test */
    public function should_preserve_casing() : void
    {
      $this->markTestSkipped("needs something else than DOMDocument from php");
      $x = $this->x->with("<Button className='foo'></Button>");
      //$this->assertEquals(["className" => 'foo'], $x->getAttrs());
    }

    /**
     * @test
     * @expectedException \Gang\WebComponents\Parser\Exception\UnhandledXmlEntity
     */
    public function will_fail_for_unmatched_closing_tags() : void
    {
        $x = $this->x->with("</input>");

        $this->assertEquals("input", $x->getName());
    }

    /**
     * @test
     * @BUG: the saveHTML stores attributes as lowercase
     */
    public function will_fail_for_parsing_attr() : void
    {
        $x = $this->x->with('<input className="perrito">');
        $this->assertEquals("perrito", $x->getAttrs()['classname']);
    }
}
