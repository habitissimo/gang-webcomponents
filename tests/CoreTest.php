<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Doctrine\Common\Cache\FilesystemCache;
use Gang\WebComponents\Configuration;
use Gang\WebComponents\Parser\NewParser;
use Gang\WebComponents\WebComponentController;
use Gang\WebComponents\HTMLComponent;
use Gang\WebComponents\HTMLComponentFactory;
use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Parser;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\Renderer\Renderer;
use Gang\WebComponents\Renderer\TreeRenderer;
use Gang\WebComponentsTests\WebComponents\Button\Button;

//use Habitissimo\Utils\Web\Src\Component\Button\Button;

use PHPUnit\Framework\TestCase;

use Prophecy\Prophet;
use Prophecy\Argument;

final class CoreTests extends TestCase
{
    private $prophet;
    private $parser;
    private $renderer;
    private $loader;
    private $controller;
    private $factory;

    private $integrationHtmlResult = '<!DOCTYPE html>
<html lang="es-ES">
<head>

  <link rel="stylesheet" type="text/css" href="https://www.habitissimo.es/static/build/css/frontend.min.css?v=1550482066">
  <link rel="stylesheet" type="text/css" href="https://www.habitissimo.es/static/font/iconissimo_e24da5260b1055faad15d85cb5fadd90.woff">
  <noscript><style type="text/css">img.lazy{display:none;}</style></noscript>
  <script type="text/javascript" src="https://www.habitissimo.es/static/build/js/habitissimo-frontend.min.js?v=1550482066"></script>
  <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async></script><script type="text/javascript">window.criteo_q = window.criteo_q || [];window.criteo_q.push({ event: "setAccount", account: 0 },{ event: "setSiteType", type:"d"},{ event: "viewHome" });</script>
</head>

<body class="home">
  <a role="button">Esto es un Boton a renderizar</a>
</body>
</html>
';

    public function setup()
    {
        $this->prophet = new Prophet;
        $this->renderer = $this->prophet->prophesize(TreeRenderer::class);
        $this->loader = $this->prophet->prophesize(ComponentLibrary::class);
        $this->factory = $this->prophet->prophesize(HTMLComponentFactory::class);


        Configuration::$library_base_namespace = "Gang\WebComponentsTests\WebComponents";
        Configuration::$library_template_dir = __DIR__ . DIRECTORY_SEPARATOR .  "WebComponents";
        $lib = new ComponentLibrary();


        $this->controller = new WebComponentController(
          $lib
        );
    }

    public function testPlainHTMLOnly() : void
    {
        $a = '<a href="foo">Goto my site</a>';
        $an_htrml_string = "<!DOCTYPE html>". "\n" . $a . "\n";
        $result = $this->controller->process($an_htrml_string);

        $this->assertEquals($an_htrml_string, $result);
    }

    public function testWebComponentOnly()
    {
      $a = '<a role="button" href="foo">Goto my site</a>';
        $an_htrml_string = "<!DOCTYPE html><wc-button href='foo'>Goto my site</wc-button>";
        $expected_render = '<!DOCTYPE html>' . "\n" . $a . "\n";

        $result = $this->controller->process($an_htrml_string);
        $this->assertEquals($expected_render, $result);
    }

    public function testHTMLAndWebComponent() : void
    {
        $non_wc = '<img src="foo">';
        $wc = '<wc-button href="foo">Go to my site</wc-button>';
        $rendered_wc = '<a role="button" href="foo">Go to my site</a>';
        $input = "<!DOCTYPE html>" . $non_wc . $wc;
        $expected_render ="<!DOCTYPE html>". "\n". $non_wc . $rendered_wc . "\n";

        $result = $this->controller->process($input);

        $this->assertEquals($expected_render, $result);
    }

    /**
     * @test
     */
    public function should_integrate_web_components() : void
    {
        Configuration::$library_template_dir = __DIR__ . DIRECTORY_SEPARATOR .  "WebComponents";
        Configuration::$library_base_namespace = "Gang\WebComponentsTests\WebComponents";
        $lib = new ComponentLibrary();
        $controller = new WebComponentController($lib);
        $parsed_template = $controller->process(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .  "WebComponents" . DIRECTORY_SEPARATOR ."IntegrationTest.twig"));
        $this->assertEquals($this->integrationHtmlResult,$parsed_template);
    }


    public function testAddAtribute()
    {
      $a =  '<a role="button" class=" btn-outlined"></a>';
      $expect_data = '<!DOCTYPE html>' . "\n" . $a . "\n";
      $button = '<!DOCTYPE html><wc-button className="btn-outlined"></wc-button>';
      $result = $this->controller->process($button);
      $this->assertEquals($expect_data, $result);
    }

    public function testLink() : void
    {
      $link = "<link rel=\"preload\" href=\"/static/build/css/yantramanav.min.css?v=1556611503\" as=\"style\" onload=\"this.onload=null;this.rel='stylesheet'\">";
      $expect_data =     $expect_data = '<!DOCTYPE html>' . "\n" . $link . "\n";
      $result = $this->controller->process("<!DOCTYPE html>".$link);
      $this->assertEquals($expect_data, $result);
    }
}
