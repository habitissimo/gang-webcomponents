<?php
declare(strict_types=1);

namespace Gang\WebComponentsTests;

use Gang\WebComponents\WebComponentController;
use Gang\WebComponents\HTMLComponent;
use Gang\WebComponents\HTMLComponentFactory;
use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Parser;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\Renderer\Renderer;

use Habitissimo\Utils\Web\Src\Component\Button\Button;
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
<html lang="es-ES" >
<head>

  <link rel="stylesheet" type="text/css" href="https://www.habitissimo.es/static/build/css/frontend.min.css?v=1550482066" />
  <link rel="stylesheet" type="text/css" href="https://www.habitissimo.es/static/font/iconissimo_e24da5260b1055faad15d85cb5fadd90.woff" />
  <noscript><style type="text/css">img.lazy{display:none;}</style></noscript>
  <script type="text/javascript" src="https://www.habitissimo.es/static/build/js/habitissimo-frontend.min.js?v=1550482066"></script>
  <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async></script><script type="text/javascript">window.criteo_q = window.criteo_q || [];window.criteo_q.push({ event: "setAccount", account: 0 },{ event: "setSiteType", type:"d"},{ event: "viewHome" });</script>
</head>

<body class="home">
  <a
       role = "button"
  class ="
          btn btn-primary btn-md




  "
>
    Esto es un Boton a renderizar

</a>

</body>
</html>
';

    public function setup()
    {
        $this->prophet = new Prophet;
        $this->parser = $this->prophet->prophesize(Parser::class);
        $this->renderer = $this->prophet->prophesize(Renderer::class);
        $this->loader = $this->prophet->prophesize(ComponentLibrary::class);
        $this->factory = $this->prophet->prophesize(HTMLComponentFactory::class);
        $this->controller = new WebComponentController(
            $this->parser->reveal(),
            $this->renderer->reveal(),
            $this->loader->reveal(),
            $this->factory->reveal()
        );
    }

    public function testPlainHTMLOnly() : void
    {
        $an_htrml_string = "<a href='foo'>Goto my site</a>";
        $this->parser
            ->parse($an_htrml_string)
            ->willReturn([new Fragment($an_htrml_string)]);
        $result = $this->controller->process($an_htrml_string);

        $this->assertEquals($an_htrml_string, $result);
    }

    public function testWebComponentOnly()
    {
        $an_htrml_string = "<Button href='foo'>Goto my site</Button>";
        $expected_render = "<a href='foo'>Goto my site</a>";

        $this->parser
            ->parse($an_htrml_string)
            ->willReturn([new WebComponent($an_htrml_string, "Button", ["href" => "foo"])]);

        $this->parser
            ->parse($expected_render)
            ->willReturn([new Fragment($expected_render)]);
        $this->factory->create(new WebComponent($an_htrml_string, "Button", ["href" => "foo"]))->willReturn(new Button());
        $this->renderer->render(new Button())->willReturn($expected_render);

        $result = $this->controller->process($an_htrml_string);

        $this->assertEquals($expected_render, $result);
    }

    public function testHTMLAndWebComponent() : void
    {
        $non_wc = '<img src="foo"/>';
        $wc = '<Button href="foo">Go to my site</Button>';
        $rendered_wc = '<a href="foo">Go to my site</a>';
        $input = $non_wc . $wc;
        $expected_render = $non_wc . $rendered_wc;

        $this->parser->parse($input)->willReturn([
            new Fragment($non_wc), new WebComponent($wc, "Button", ["href" => "foo"])
        ]);
        $this->parser->parse($rendered_wc)->willReturn([
            new Fragment($rendered_wc)
        ]);
        $this->parser->parse(Argument::type('string'))->shouldBeCalledTimes(2);
        $this->factory->create(Argument::type(WebComponent::class))
            ->willReturn(new Button());
        $this->renderer->render(Argument::type(HTMLComponent::class))
            ->willReturn($rendered_wc);

        $result = $this->controller->process($input);

        $this->assertEquals($expected_render, $result);
        $this->prophet->checkPredictions();
    }

    /**
     * @test
     */
    public function should_integrate_web_components() : void
    {
        $lib = new ComponentLibrary();
        $lib->loadLibrary("Gang\WebComponents\Tests\WebComponents", __DIR__ . DIRECTORY_SEPARATOR .  "WebComponents");
        $controller = new WebComponentController(null, null, $lib, null, null);
        $parsed_template = $controller->process(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .  "WebComponents" . DIRECTORY_SEPARATOR ."IntegrationTest.twig"));
        $this->assertEquals($this->integrationHtmlResult,$parsed_template);
    }

    public function testComplexRadioInputGroup() : void
    {
        $lib = new ComponentLibrary();
        $lib->loadLibrary("Habitissimo\Utils\Web\Src\Component", __DIR__ . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR. "Src" . DIRECTORY_SEPARATOR . 'Component');
        $controller = new WebComponentController(null, null, $lib, null, null);
        $parsed_template = $controller->process(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .  "WebComponents" . DIRECTORY_SEPARATOR ."RadioInputGroup.twig"));
        $this->assertEquals($this->integrationHtmlResult,$parsed_template);
    }
}
