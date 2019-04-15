<?php

declare(strict_types=1);
namespace Gang\WebComponents\Renderer;

use Gang\WebComponents\ComponentLibrary;
use Gang\WebComponents\Parser\Buffer;
use Gang\WebComponents\Parser\Nodes\WebComponent;
use Gang\WebComponents\Renderer\TwigTemplateRenderer;
use Gang\WebComponents\HTMLComponentFactory;
use Gang\WebComponents\Contracts\NodeInterface;
use rg\tools\phpnsc\ConsoleOutput;

class TreeRenderer
{
  private $buffers = [];

  public function __construct(ComponentLibrary $library, ?HTMLComponentFactory $factory = null)
  {
      $this->renderer = new Renderer(new TwigTemplateRenderer(), $library);
      $this->factory = $factory ?? new HTMLComponentFactory($library);
  }

  public function render(WebComponent $component)
  {
    $this->postOrderTraverse($component);
    $htmlComponent = $this->factory->create($component);
    $rendered = $this->renderer->render($htmlComponent);
    return $rendered;
  }

  /**
   * Traverse the tree in post order
   *
   * https://stackoverflow.com/questions/20062527/scan-tree-structure-from-bottom-up
   */
  private function postOrderTraverse(WebComponent $component)
  {
    $buffer = $this->getBuffer($component);

    foreach ($component->getChildren() as $child) {
      if ($child instanceof WebComponent) {
        $this->postOrderTraverse($child);
        $html_component = $this->factory->create($child);

        $buffer->append($this->renderer->render($html_component));
      } else {
        $buffer->append((string) $child);
      }
    }

    if (!$buffer->empty()) {
      $component->setInnerHtml($buffer->read());
    }
  }

  private function getBuffer(WebComponent $component)
  {
    $hash = spl_object_hash($component);

    if (!isset($this->buffers[$hash])) {
      $this->buffers[$hash] = new Buffer();
    }

    return $this->buffers[$hash];
  }
}
