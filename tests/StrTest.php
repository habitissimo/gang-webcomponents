<?php

namespace Gang\WebComponentsTests;

use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Helpers\Str;

class StrTest extends TestCase
{
  public function testEmpty()
  {
    $this->assertEquals('', Str::snake(''));
  }

  public function testOneWord()
  {
    $this->assertEquals('snake', Str::snake('snake'));
  }

  public function testOneWordFirstUpper()
  {
    $this->assertEquals('snake', Str::snake('Snake'));
  }

  public function testCamel()
  {
    $this->assertEquals('snake_case', Str::snake('snakeCase'));
    $this->assertEquals('class_name', Str::snake('className'));
  }

  public function testAlreadySnake()
  {
    $this->assertEquals('snake_case', Str::snake('snake_case'));
  }

  public function testFirstUpperCamel()
  {
    $this->assertEquals('snake_case', Str::snake('SnakeCase'));
  }
}
