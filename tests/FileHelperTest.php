<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Gang\WebComponents\Helpers\File;
use PHPUnit\Framework\TestCase;

class FileHelperTest extends TestCase
{
  public function testGetClassNameFromNameSpace(): void
  {
    $this->assertEquals('Fragment', File::getClassFromNameSpace('Habitissimo\Utils\Web\Src\Component\Fragment\Fragment'));
  }

  public function testRelativePath(): void
  {
    $this->assertEquals(
      '/Navbar/Collapsable/CollapsableNavbar.php',
      File::getRelativePath('habitissimo/Navbar/Collapsable/CollapsableNavbar.php', 'habitissimo')
    );
  }

  public function testRelativeDir(): void
  {
    $this->assertEquals(
      '/Navbar/Collapsable',
      File::getRelativeDir('/Navbar/Collapsable/CollapsableNavbar.php')
    );
  }

  public function testNameSpaceFromFOlder(): void
  {
    $this->assertEquals(
      '\Navbar\Collapsable',
      File::getNameSpaceFromFolder('/Navbar/Collapsable')
    );
  }

  public function testAnonymousClassName(): void
  {
    $class = new class()
    {
    };
    $instance = new $class();
    $this->assertEquals(get_class($instance), File::getClassFromNameSpace(get_class($class)));
  }
}
