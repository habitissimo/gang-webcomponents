<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Gang\WebComponents\ComponentLibrary;
use PHPUnit\Framework\TestCase;

class ComponentLibraryTest extends TestCase
{
    private $componentLibrary;
    private const PACKAGE_DIR = "packages" .DIRECTORY_SEPARATOR . "Tests" . DIRECTORY_SEPARATOR;
    private const TEST_DIR = "TestContent" . DIRECTORY_SEPARATOR ;
    private const COMPONENTS = ["Button","Input"];

    private function generateBasicExpectedData(string $namespace)
    {
        $expected_library = [];
        foreach (self::COMPONENTS as $component) {
            $expected_library[$component] = [
                ComponentLibrary::KEY_NAMESPACE     => $namespace.'\\'.$component,
                ComponentLibrary::KEY_FILE          => self::PACKAGE_DIR . self::TEST_DIR . $component . DIRECTORY_SEPARATOR . $component . '.php',
                ComponentLibrary::COMPONENT_FOLDER  => self::PACKAGE_DIR . mb_substr(self::TEST_DIR, 0, -1),
            ];
        }
        return $expected_library;
    }

    public function setUp(): void
    {
        $this->componentLibrary = new ComponentLibrary();
        $this->createDir();
    }

    private function createDir($directory = self::PACKAGE_DIR . self::TEST_DIR): void
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0700, true);
        }
    }

    private function createComponentClass(string $component, string $namespace, string $directory, ?bool $withTwig = true) : void
    {
        if ($withTwig === true) {
            file_put_contents(
                $directory .  $component . '.twig',
                'Twig template'
            );
        }

        file_put_contents(
            $directory . $component . '.php',
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

    private function createFiles($namespace)
    {
        foreach (self::COMPONENTS as $component) {
            $this->createDir(self::PACKAGE_DIR . self::TEST_DIR . $component . DIRECTORY_SEPARATOR) ;
            $this->createComponentClass($component, $namespace. "\\" . $component, self::PACKAGE_DIR . self::TEST_DIR . $component . DIRECTORY_SEPARATOR);
        }
    }

    public function tearDown(): void
    {
        foreach (self::COMPONENTS as $comp) {
            if (file_exists(self::PACKAGE_DIR . self::TEST_DIR . $comp . DIRECTORY_SEPARATOR . $comp . '.php')) {
                unlink(self::PACKAGE_DIR . self::TEST_DIR . $comp . DIRECTORY_SEPARATOR . $comp . '.php');
                unlink(self::PACKAGE_DIR . self::TEST_DIR . $comp . DIRECTORY_SEPARATOR . $comp . '.twig');
                rmdir(self::PACKAGE_DIR . self::TEST_DIR . $comp . DIRECTORY_SEPARATOR);
            }
        }
        if (file_exists(self::PACKAGE_DIR . self::TEST_DIR)) {
            rmdir(self::PACKAGE_DIR . self::TEST_DIR);
        }
    }

    public function testLoadLibraryWithDifferentNamespace(): void
    {
        $namespace = 'Habitissimo\Utils';
        $this->createFiles($namespace);
        $directory = self::PACKAGE_DIR . self::TEST_DIR;
        $this->componentLibrary->loadLibrary($namespace, $directory);
        $this->assertEquals(
            $this->generateBasicExpectedData($namespace),
            $this->componentLibrary->getLibrary()
        );
    }

    public function testLoadLibraryFromNamespace(): void
    {
        $namespace = "packages\Tests\TestContent";
        $this->createFiles($namespace);
        $this->componentLibrary->loadLibrary($namespace);
        $this->assertEquals($this->generateBasicExpectedData($namespace), $this->componentLibrary->getLibrary());
    }

    public function testCreateComponent(): void
    {
        $namespace = 'Habitissimo\Utils';
        $directory = self::PACKAGE_DIR . self::TEST_DIR;

        $this->createFiles($namespace);
        $this->componentLibrary->loadLibrary($namespace, $directory);
        $this->assertEquals('Habitissimo\Utils\Button\Button', $this->componentLibrary->getComponentClass("Button"));
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
        $namespace = "Habitissimo\Utils";
        $directory = self::PACKAGE_DIR . self::TEST_DIR;
        $this->createFiles($namespace);
        $this->componentLibrary->loadLibrary($namespace, $directory);
        $expected_content = "Twig template";
        $this->assertEquals($expected_content, $this->componentLibrary->getTemplateContent("Button", ".twig"));
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
        $second_dir = "TestingLib/Components/";
        $namespace2 = "Templates\Utils\Component";
        $this->createDir($second_dir."Nav/");
        $this->createComponentClass("Nav", $namespace2, $second_dir."Nav/");
        $this->createFiles("Habitissimo\Utils");
        $this->componentLibrary->loadLibrary('Habitissimo\Utils', self::PACKAGE_DIR . self::TEST_DIR);
        $this->componentLibrary->loadLibrary($namespace2, $second_dir);

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


        //Borrar seconddir y ns2
        unlink("TestingLib/Components/Nav/Nav.twig");
        unlink("TestingLib/Components/Nav/Nav.php");
        rmdir("TestingLib/Components/Nav");
        rmdir("TestingLib/Components");
        rmdir("TestingLib");
    }

    public function testStrangeFolder() : void
    {
        $this->createDir('Testinglib/Components/old');
        $this->createDir('Testinglib/Components/newcollection');
        $this->createDir('Testinglib/Components/Fragment');
        $this->createComponentClass("Button", 'Habitissimo\Utils\old', "Testinglib/Components/old/");
        $this->createComponentClass("NewButton", 'Habitissimo\Utils\old', "Testinglib/Components/old/");
        $this->createComponentClass("Fragment", 'Habitissimo\Utils\old', "Testinglib/Components/Fragment/");
        $this->componentLibrary->loadLibrary('Habitissimo\Utils', "Testinglib/Components/");
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
        unlink("Testinglib/Components/old/Button.php");
        unlink("Testinglib/Components/old/Button.twig");
        unlink("Testinglib/Components/old/NewButton.php");
        unlink("Testinglib/Components/old/NewButton.twig");
        unlink("Testinglib/Components/Fragment/Fragment.twig");
        unlink("Testinglib/Components/Fragment/Fragment.php");
        rmdir("Testinglib/Components/newcollection");
        rmdir('Testinglib/Components/old');
        rmdir('Testinglib/Components/Fragment');
        rmdir("Testinglib/Components");
        rmdir("Testinglib");
    }
}
