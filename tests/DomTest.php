<?php


namespace Gang\WebComponentsTests;


use Doctrine\Common\Cache\FilesystemCache;
use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Configuration;
use Gang\WebComponents\Helpers\Dom;
use Gang\WebComponents\HTMLComponentFactory;
use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\Renderer\Renderer;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;
use Gang\WebComponents\WebComponentController;
use Habitissimo\DI\Container;
use PHPUnit\Framework\TestCase;
use sfConfig;

class DomTest extends TestCase
{
private $html = '<html>

<head>

<title>Your Title Here</title>

</head>

<body BGCOLOR="FFFFFF">

<center><img SRC=\"clouds.jpg\" ALIGN=\"BOTTOM\"> </center>

<hr>

<a href=\"http://somegreatsite.com\">Link Name</a>

is a link to another nifty site

<h1>This is a Header</h1>

<h2>This is a Medium Header</h2>

Send me mail at <a href=\"mailto:support@yourcompany.com\">

support@yourcompany.com</a>.

<P> This is a new paragraph!

<p> <b>This is a new paragraph!</b>

<br> <b><i>This is a new sentence without a paragraph break, in bold italics.</i></b>

<hr>

</body>

</html>';


private $html1 = "<div><img src='ewf3ewf'/><p>holaegergerger wefwefwe //// 'wefñwjemfpomwe'</p></div>";

private $html2 = "<div><wc-fragment><wc-button>This is a button</wc-button></wc-fragment></div>";
private $html3 = "<div><wc-tabs><wc-tab>Hola</wc-tab></wc-tabs></div>";


private $dom ;
private $lib;
private $factory;
private $twig;
private $render;
private $parentElement;

  public function setUp(): void
  {
    Configuration::$twig_cache_path = sfConfig::get('sf_cache_dir') . '/webcomponents/twig';
    Configuration::$library_cache_driver = new FilesystemCache(sfConfig::get('sf_cache_dir') . '/webcomponents/library');
    Configuration::$library_base_namespace = "Habitissimo\Web\Components";
    Configuration::$library_template_dir = Container::get('path.root') . "/Habitissimo/Web/Components";
    new WebComponentController();

    $this->dom = new \DOMDocument();

    $this->lib = new ComponentLibrary();
    $this->factory = new HTMLComponentFactory($this->lib);
    $this->twig = new TwigTemplateRenderer();
    $this->render = new Renderer($this->twig, $this->lib);
  }
  public function testDom() : void
  {
    $this->dom = Dom::domFromString($this->html3);
    while($this->getNextWebComponent($this->dom)){
      $webcomponent = $this->getNextWebComponent($this->dom);
      $this->renderWC($webcomponent);
    };
     dd($this->dom->saveHTML());

  }


  private function getNextWebComponent(\DOMDocument $dom)
  {
    $xpath = new \DOMXpath($dom);

    return $xpath->query("//*[starts-with(local-name(), 'wc-')]")[0];
  }


  private function renderWC($dom_wc)
  {
    $wc = $this->createWebComponent($dom_wc);

    $htmlComponent = $this->factory->create($wc);

    $renderer_component = $this->render->render($htmlComponent);

    $DOM = Dom::domFromString($renderer_component);

    $dom_element_renderer = $DOM->childNodes[1];

    foreach($dom_wc->childNodes as $child){
      $dom_element_renderer->appendChild($DOM->importNode($child, true));
    }

    $parent_node = $dom_wc->parentNode;

    $parent_node->replaceChild($this->dom->importNode($dom_element_renderer, true),$dom_wc);
  }


  private function createWebComponent ($dom_wc){
    $attributes = [];
    if ($dom_wc->hasAttributes()) {
      foreach ($dom_wc->attributes as $attr) {
        $attributes[$attr->nodeName] = $attr->nodeValue;
      }
    }
    $wc = new WebComponent($dom_wc->nodeName, $attributes, false);
    return $wc;
  }

  private function createFragment($value){
    return new Fragment($value);
  }

}
