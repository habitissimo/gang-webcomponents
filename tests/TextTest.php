<?php

namespace Gang\WebComponentsTests;

use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Parser\Nodes\Fragment;

class TextTest extends TestCase
{
    public function testTextConstruct() : void
    {
        $t = new Fragment("Hola");
        $this->assertInstanceOf(Fragment::class, $t);
        $this->assertEquals("Hola", $t->__toString());
    }

    public function testTextConstructSpecialChars() : void
    {
        $text = new Fragment('áéíóúÜüïÇç"¡?¿!`+*-@#~½¬%ªº=/&<');
        $this->assertEquals('áéíóúÜüïÇç"¡?¿!`+*-@#~½¬%ªº=/&<', $text->__toString());
    }

    public function testTextToString()
    {
        $text = new Fragment("printing this");
        $this->assertEquals("printing this", $text);
    }
}
