<?php
namespace Gang\WebComponentsTests;

use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\HTMLComponentFactory;
use Gang\WebComponents\Parser\Nodes\Fragment;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\Renderer\TreeRenderer;
use Gang\WebComponents\WebComponentController;
use PHPUnit\Framework\TestCase;

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
    new WebComponentController($library, $this->renderer);
  }

  public function testBasic()
  {
    $expected = '<a role="button"></a>';

    $wc = new WebComponent('Button', [] , false);
    $wc->closeTag();
    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }

  public function testWithHtmlChild()
  {
    $expected = '<a role="button"><p>Habitissimo</p></a>';

    $wc = new WebComponent( 'Button', [], false);
    $wc->appendChild(new Fragment('<p>Habitissimo</p>'));
    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }

  public function testWithWebComponentChild()
  {

    $expected = '<a role="button"><a role="button">Habitissimo</a></a>';

    $button1 = new WebComponent('Button', [], false);
    $button2 = new WebComponent('Button', [], false);
    $button2->appendChild(new Fragment('Habitissimo'));
    $button2->closeTag();
    $button1->appendChild($button2);
    $button1->closeTag();

    $return = $this->renderer->render($button1);
    $this->assertEquals($expected, $return);
  }


  public function testWithManyChilds()
  {
    $expected = '<a role="button"><a role="button">1</a><a role="button">2</a><a role="button">3</a></a>';

    $wc = new WebComponent( 'Button', [], false);
    $button1 = new WebComponent( 'Button', [], false);
    $button1->appendChild(new Fragment('1'));
    $button1->closeTag();

    $button2 = new WebComponent( 'Button', [], false);
    $button2->appendChild(new Fragment('2'));
    $button2->closeTag();

    $button3 = new WebComponent( 'Button', [], false);
    $button3->appendChild(new Fragment('3'));
    $button3->closeTag();

    $wc->appendChild($button1);
    $wc->appendChild($button2);
    $wc->appendChild($button3);
    $wc->closeTag();

    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }

  public function testWithComplexChilds()
  {
    $expected = '<div><div><a role="button">1</a></div></div>';

    $wc = new WebComponent( 'Div', [], false);
    $div =  new WebComponent( 'Div', [], false);

    $button = new WebComponent( 'Button', [], false);
    $button->appendChild(new Fragment('1'));
    $button->closeTag();

    $div->appendChild($button);
    $div->closeTag();

    $wc->appendChild($div);

    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }


  public function testWithComplexNestedChilds()
  {

    $expected = '<div>
<div>
<a role="button">1</a><a role="button">2</a><a role="button">3</a><div><a role="button"></a></div>
</div>
<div>
<a role="button">4</a><a role="button">5</a><a role="button">6</a><div><a role="button"></a></div>
</div>
<div>
<a role="button">7</a><a role="button">8</a><a role="button">9</a><div><a role="button"></a></div>
</div>
</div>';
    $structure = "<Div>
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
    $wc = new WebComponent( 'Div', [], false);

    $btn = new WebComponent('Button', [], false);
    $btn->closeTag();

    $div1 = new WebComponent( 'Div', [], false);

    $button1 = new WebComponent( 'Button', [], false);
    $button1->appendChild(new Fragment('1'));
    $button1->closeTag();

    $button2 = new WebComponent( 'Button', [], false);
    $button2->appendChild(new Fragment('2'));
    $button2->closeTag();

    $button3 = new WebComponent( 'Button', [], false);
    $button3->appendChild(new Fragment('3'));
    $button3->closeTag();

    $div1_1 =  new WebComponent('Div', [], false);

    $div1_1->appendChild($btn);
    $div1_1->closeTag();

    $div1->appendChild($button1);
    $div1->appendChild($button2);
    $div1->appendChild($button3);
    $div1->appendChild($div1_1);
    $div1->closeTag();

    $div2 = new WebComponent( 'Div', [], false);

    $button4 = new WebComponent( 'Button', [], false);
    $button4->appendChild(new Fragment('4'));
    $button4->closeTag();

    $button5 = new WebComponent( 'Button', [], false);
    $button5->appendChild(new Fragment('5'));
    $button5->closeTag();

    $button6 = new WebComponent( 'Button', [], false);
    $button6->appendChild(new Fragment('6'));
    $button6->closeTag();

    $div2_1 =  new WebComponent('Div', [], false);
    $div2_1->appendChild($btn);
    $div2_1->closeTag();

    $div2->appendChild($button4);
    $div2->appendChild($button5);
    $div2->appendChild($button6);
    $div2->appendChild($div2_1);
    $div2->closeTag();

    $div3 = new WebComponent( 'Div', [], false);

    $button7 = new WebComponent( 'Button', [], false);
    $button7->appendChild(new Fragment('7'));
    $button7->closeTag();

    $button8 = new WebComponent( 'Button', [], false);
    $button8->appendChild(new Fragment('8'));
    $button8->closeTag();

    $button9 = new WebComponent( 'Button', [], false);
    $button9->appendChild(new Fragment('9'));
    $button9->closeTag();

    $div3_1 =  new WebComponent('Div', [], false);
    $div3_1->appendChild($btn);
    $div3_1->closeTag();

    $div3->appendChild($button7);
    $div3->appendChild($button8);
    $div3->appendChild($button9);
    $div3->appendChild($div3_1);
    $div3->closeTag();

    $wc->appendChild($div1);
    $wc->appendChild($div2);
    $wc->appendChild($div3);
    $wc->closeTag();

    $return = $this->renderer->render($wc);
    $this->assertEquals($expected, $return);
  }

}
