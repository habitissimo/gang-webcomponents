<?php
namespace Gang\WebComponentsTests;

use PHPUnit\Framework\TestCase;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Renderer\TreeRenderer;
use Gang\WebComponents\HTMLComponentFactory;

class TreeRendererTest extends TestCase
{
  private $renderer;

  protected function setUp()
  {
    $library = new ComponentLibrary();
    $library->loadLibrary("Gang\WebComponentsTests\WebComponents", __DIR__ . DIRECTORY_SEPARATOR .  "WebComponents");
    $this->renderer =  new TreeRenderer(
      $library,
      new HTMLComponentFactory($library)
    );
  }

  public function testBasic()
  {
    $expected = '<a role="button"></a>';
    $button = "<Button></Button>";

    $wc = new WebComponent($button, 'Button', []);
    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }

  public function testWithHtmlChild()
  {

    $expected = '<a role="button"><p>Habitissimo</p></a>';
    $button = "<Button><p>Habitissimo</p></Button>";

    $wc = new WebComponent($button, 'Button', []);
    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }

  public function testWithWebComponentChild()
  {

    $expected = '<a role="button"><a role="button">Habitissimo</a></a>';
    $button = "<Button><Button>Habitissimo</Button></Button>";

    $wc = new WebComponent($button, 'Button', []);

    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }


  public function testWithManyChilds()
  {

    $expected = '<a role="button"><a role="button">1</a><a role="button">2</a><a role="button">3</a></a>';
    $button = "<Button><Button>1</Button><Button>2</Button><Button>3</Button></Button>";

    $wc = new WebComponent($button, 'Button', []);

    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }

  public function testWithComplexChilds()
  {
    $expected = '<div><div><a role="button">1</a></div></div>';
    $button = "<Div>
                    <Div>
                      <Button>1</Button>    
                    </Div>                
               </Div>";

    $wc = new WebComponent($button, 'Div', []);

    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }


  public function testWithComplexNestedChilds()
  {

    $expected = '<div><div><a role="button">1</a><a role="button">2</a><a role="button">3</a><div><a role="button"></a></div></div><div><a role="button">4</a><a role="button">5</a><a role="button">6</a><div><a role="button"></a></div></div><div><a role="button">7</a><a role="button">8</a><a role="button">9</a><div><a role="button"></a></div></div></div>';
    $button = "<Div>
                    <Div>
                      <Button>1</Button>    
                      <Button>2</Button>    
                      <Button>3</Button>
                      <Div>
                        <Button></Button>
                      </Div>
                    </Div> 
                    <Div>
                      <Button>4</Button>    
                      <Button>5</Button>    
                      <Button>6</Button>
                      <Div>
                        <Button></Button>
                      </Div>
                    </Div>
                    <Div>
                      <Button>7</Button>    
                      <Button>8</Button>    
                      <Button>9</Button>
                      <Div>
                        <Button></Button>
                      </Div>
                    </Div>                
               </Div>";
    $wc = new WebComponent($button, 'Div', []);

    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }

}
