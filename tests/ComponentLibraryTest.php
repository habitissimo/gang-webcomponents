<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Gang\WebComponents\ComponentLibrary;
use PHPUnit\Framework\TestCase;

class ComponentLibraryTest extends TestCase
{
  private $componentLibrary;
  private const PACKAGE_DIR = "packages" .DIRECTORY_SEPARATOR . "Tests";
  private const TEST_DIR = "TestContent" ;
  private const COMPONENTS = ["Button" => self::PACKAGE_DIR . DIRECTORY_SEPARATOR . self::TEST_DIR
                              ,"Input" => self::PACKAGE_DIR . DIRECTORY_SEPARATOR . self::TEST_DIR];

  private function generateBasicExpectedData(string $namespace, array $components)
  {
    $expected_library = [];
    foreach ($components as $component => $directory) {
      $expected_library[$component] = [
        ComponentLibrary::KEY_NAMESPACE     => $namespace.'\\'.$component,
        ComponentLibrary::KEY_FILE          =>  $directory . DIRECTORY_SEPARATOR . $component . DIRECTORY_SEPARATOR . $component . '.php',
        ComponentLibrary::COMPONENT_FOLDER  => $directory,
      ];
    }
    return $expected_library;
  }

  public function setUp(): void
  {
    $this->componentLibrary = new ComponentLibrary(null);
  }

  private function createStructureDir($namespace ,$components){

    foreach ($components as $component => $directory){
      $this->createFiles($namespace, $component, $directory);
    }

  }

  private function createDir($directory): void
  {
    if (!file_exists($directory)) {
      mkdir($directory, 0700, true);
    }
  }

  private function createFiles($namespace, $component , $directory)
  {
    $this->createDir($directory . DIRECTORY_SEPARATOR . $component);
    $this->createComponentClass($component, $namespace. "\\" . $component, $directory . DIRECTORY_SEPARATOR . $component );
  }

  private function createComponentClass(string $component, string $namespace, string $directory, ?bool $withTwig = true) : void
  {
    if ($withTwig === true) {
      file_put_contents(
        $directory . DIRECTORY_SEPARATOR .  $component . '.twig',
        'Twig template'
      );
    }

    file_put_contents(
      $directory . DIRECTORY_SEPARATOR . $component . '.php',
      '<?php
namespace '.$namespace.';

class '.$component.' extends HTMLComponent '.  $withTwig ?? 'implements TemplateFolderInterface' .'
{
    protected $id;
    public function setId($value)
    {
        $this->id = $value;
    }
}'
    );
  }


  public function cleanDirectories($components, $directories): void
  {
    foreach ($components as $comp => $dir) {
      if (file_exists($dir . DIRECTORY_SEPARATOR . $comp . DIRECTORY_SEPARATOR . $comp. '.php')) {
        unlink($dir . DIRECTORY_SEPARATOR . $comp . DIRECTORY_SEPARATOR . $comp. '.php');
        unlink($dir . DIRECTORY_SEPARATOR . $comp . DIRECTORY_SEPARATOR . $comp. '.twig');
        rmdir($dir . DIRECTORY_SEPARATOR . $comp);
      }
    }

    foreach ($directories as $dirname) {
      if (file_exists($dirname)) {
        rmdir($dirname);
      }
    }
  }

  public function testLoadLibraryWithDifferentNamespace(): void
  {
    $namespace = 'Habitissimo\Utils';
    $directory = self::PACKAGE_DIR . DIRECTORY_SEPARATOR . self::TEST_DIR;

    $this->createStructureDir($namespace, self::COMPONENTS);

    $this->componentLibrary->loadLibrary($namespace, $directory);
    $this->assertEquals(
      $this->generateBasicExpectedData($namespace , self::COMPONENTS),
      $this->componentLibrary->getLibrary()
    );
    $this->cleanDirectories(self::COMPONENTS, [$directory]);
  }

  public function testLoadLibraryFromNamespace(): void
  {
    $namespace = "packages\Tests\TestContent";
    $directory = self::PACKAGE_DIR . DIRECTORY_SEPARATOR . self::TEST_DIR;

    $this->createStructureDir($namespace, self::COMPONENTS);
    $this->componentLibrary->loadLibrary($namespace, $directory);
    $this->assertEquals($this->generateBasicExpectedData($namespace,self::COMPONENTS), $this->componentLibrary->getLibrary());
    $this->cleanDirectories(self::COMPONENTS, [$directory]);

  }

  public function testCreateComponent(): void
  {
    $namespace = 'Habitissimo\Utils';
    $directory = self::PACKAGE_DIR . DIRECTORY_SEPARATOR . self::TEST_DIR;
    $this->createStructureDir($namespace, self::COMPONENTS);

    $this->componentLibrary->loadLibrary($namespace, $directory);
    $this->assertEquals('Habitissimo\Utils\Button\Button', $this->componentLibrary->getComponentClass("Button"));
    $this->cleanDirectories(self::COMPONENTS, [$directory]);
  }

  /**
   * @expectedException Gang\WebComponents\Exceptions\ComponentClassNotFound
   */
  public function testComponentLoadException() : void
  {
    $this->componentLibrary->getComponentClass("Holakease");
  }

  public function testGetFileContent(): void
  {
    $namespace = 'Habitissimo\Utils';
    $directory = self::PACKAGE_DIR . DIRECTORY_SEPARATOR . self::TEST_DIR;
    $this->createStructureDir($namespace, self::COMPONENTS);

    $this->componentLibrary->loadLibrary($namespace, $directory);
    $expected_content = "Twig template";
    $this->assertEquals($expected_content, $this->componentLibrary->getTemplateContent("Button", ".twig"));

    $this->cleanDirectories(self::COMPONENTS, [$directory]);
  }

  /**
   * @expectedException Gang\WebComponents\Exceptions\ComponentClassNotFound
   */
  public function testComponentContentException() : void
  {
    $this->componentLibrary->getTemplateContent("Holakease", ".twig");
  }

  public function testManySources() : void
  {
    $components = ["Input" => "packages/Tests/TestContent"
      ,"Button" => "packages/Tests/TestContent"
      ];
    $components2 = ["Nav" => "TestingLib/Components"];
    $this->createStructureDir("Habitissimo\Utils", $components);
    $this->createStructureDir("Templates\Utils\Component", $components2);

    $this->componentLibrary->loadLibrary('Habitissimo\Utils', self::PACKAGE_DIR . DIRECTORY_SEPARATOR . self::TEST_DIR);
    $this->componentLibrary->loadLibrary("Templates\Utils\Component", "TestingLib/Components");

    $expected_lib = [
      "Input" =>  [
        ComponentLibrary::KEY_FILE => "packages/Tests/TestContent/Input/Input.php",
        ComponentLibrary::KEY_NAMESPACE => "Habitissimo\Utils\Input",
        ComponentLibrary::COMPONENT_FOLDER => 'packages/Tests/TestContent'
      ],
      "Button" =>  [
        ComponentLibrary::KEY_FILE => "packages/Tests/TestContent/Button/Button.php",
        ComponentLibrary::KEY_NAMESPACE => "Habitissimo\Utils\Button",
        ComponentLibrary::COMPONENT_FOLDER => 'packages/Tests/TestContent'
      ],
      "Nav" =>  [
        ComponentLibrary::KEY_FILE => "TestingLib/Components/Nav/Nav.php",
        ComponentLibrary::KEY_NAMESPACE => "Templates\Utils\Component\Nav",
        ComponentLibrary::COMPONENT_FOLDER => 'TestingLib/Components'
      ]
    ];
    $this->assertEquals($expected_lib, $this->componentLibrary->getLibrary());

    $this->cleanDirectories($components, [self::PACKAGE_DIR . DIRECTORY_SEPARATOR . self::TEST_DIR]);
    $this->cleanDirectories($components2, ["TestingLib/Components", "TestingLib"]);
  }

  public function testStrangeFolder() : void
  {
    $this->markTestSkipped("We don't know the function of this method yet");
    $components = ["Button" => "Testinglib/Components/old"
    ,"NewButton" => "Testinglib/Components/old"
    ,"Fragment" => "Testinglib/Components/Fragment"];

    $this->createStructureDir('Habitissimo\Utils\old', $components);
    $this->componentLibrary->loadLibrary('Habitissimo\Utils', "Testinglib/Components");
    $expected_lib = [
      "Button"    => [
        ComponentLibrary::KEY_FILE  => "Testinglib/Components/old/Button.php",
        ComponentLibrary::KEY_NAMESPACE => "Habitissimo\Utils\old",
        ComponentLibrary::COMPONENT_FOLDER => 'Testinglib/Components'
      ],
      "NewButton"  => [
        ComponentLibrary::KEY_FILE => "Testinglib/Components/old/NewButton.php",
        ComponentLibrary::KEY_NAMESPACE => 'Habitissimo\Utils\old',
        ComponentLibrary::COMPONENT_FOLDER => 'Testinglib/Components'
      ],
      "Fragment"  => [
        ComponentLibrary::KEY_FILE  => "Testinglib/Components/Fragment/Fragment.php",
        ComponentLibrary::KEY_NAMESPACE => 'Habitissimo\Utils\Fragment',
        ComponentLibrary::COMPONENT_FOLDER => 'Testinglib/Components'
      ]
    ];

    $this->assertEquals($expected_lib, $this->componentLibrary->getLibrary());

    $this->cleanDirectories($components, ["Testinglib/Components/Fragment", "Testinglib/Components/old", "Testinglib/Components", "Testinglib"]);
  }
}
