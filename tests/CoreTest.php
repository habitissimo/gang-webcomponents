<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Doctrine\Common\Cache\FilesystemCache;
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

  <link rel="stylesheet" type="text/css" href="https://www.habitissimo.es/static/build/css/frontend.min.css?v=1550482066"/>
  <link rel="stylesheet" type="text/css" href="https://www.habitissimo.es/static/font/iconissimo_e24da5260b1055faad15d85cb5fadd90.woff"/>
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
        $this->parser = new NewParser();
        $this->renderer = $this->prophet->prophesize(TreeRenderer::class);
        $this->loader = $this->prophet->prophesize(ComponentLibrary::class);
        $this->factory = $this->prophet->prophesize(HTMLComponentFactory::class);


        $lib = new ComponentLibrary( null);
        $lib->loadLibrary("Gang\WebComponentsTests\WebComponents", __DIR__ . DIRECTORY_SEPARATOR .  "WebComponents");

        $this->controller = new WebComponentController(
          $lib, $this->renderer = new TreeRenderer($lib), $this->parser
        );
    }

    public function testPlainHTMLOnly() : void
    {
        $an_htrml_string = '<a href="foo">Goto my site</a>';
        $result = $this->controller->process($an_htrml_string);

        $this->assertEquals($an_htrml_string, $result);
    }

    public function testWebComponentOnly()
    {
        $an_htrml_string = "<Button href='foo'>Goto my site</Button>";
        $expected_render = '<a role="button" href="foo">Goto my site</a>';

        $result = $this->controller->process($an_htrml_string);
        $this->assertEquals($expected_render, $result);
    }

    public function testHTMLAndWebComponent() : void
    {
        $non_wc = '<img src="foo"/>';
        $wc = '<Button href="foo">Go to my site</Button>';
        $rendered_wc = '<a role="button" href="foo">Go to my site</a>';
        $input = $non_wc . $wc;
        $expected_render = $non_wc . $rendered_wc;

        $result = $this->controller->process($input);

        $this->assertEquals($expected_render, $result);
    }

    /**
     * @test
     */
    public function should_integrate_web_components() : void
    {
        $lib = new ComponentLibrary(null);
        $lib->loadLibrary("Gang\WebComponentsTests\WebComponents", __DIR__ . DIRECTORY_SEPARATOR .  "WebComponents");
        $controller = new WebComponentController($lib);
        $parsed_template = $controller->process(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .  "WebComponents" . DIRECTORY_SEPARATOR ."IntegrationTest.twig"));
        $this->assertEquals($this->integrationHtmlResult,$parsed_template);
    }

//     public function testComplexRadioInputGroup() : void
//     {
//         $lib = new ComponentLibrary();
//         $lib->loadLibrary("Habitissimo\Utils\Web\Src\Component", __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR. "Src" . DIRECTORY_SEPARATOR . 'Component');
//         $controller = new WebComponentController($lib);
//         $parsed_template = $controller->process(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .  "WebComponents" . DIRECTORY_SEPARATOR ."RadioInputGroup.twig"));
//         $this->assertEquals($this->integrationHtmlResult,$parsed_template);
//     }


  public function testAddAtribute()
  {
    $button = '<Button className="btn-outlined"></Button>';
    $result = $this->controller->process($button);
    $this->assertEquals('<a role="button" class=" btn-outlined"></a>', $result);
  }

  public function testLink() : void
  {
    $link = "<link rel=\"preload\" href=\"/static/build/css/yantramanav.min.css?v=1556611503\" as=\"style\" onload=\"this.onload=null;this.rel=\" 'stylesheet'\">";

    $result = $this->controller->process($link);
    $this->assertEquals($link, $result);
  }
}
