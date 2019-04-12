<?php
declare(strict_types=1);
namespace Gang\WebComponents\Parser\Nodes;

use Gang\WebComponents\Contracts\NodeInterface;


/**
 * Class Fragment: this class extends Text because it have the same behaviour
 * but because we want to keep the context of where a the Text token is use
 * we've extended here and use it, meaning that this class was created only
 * as a synonym for the Text class, with the difference that Fragment will
 * contain not only Text but HTML too.
 *
 * @package Gang\WebComponents\Parser\Nodes
 */
class Fragment implements NodeInterface
{
  private $value;

  /**
   * @param string $value
   */
  public function setValue(string $value): void
  {
    $this->value = $value;
  }

  public function __construct(string $value)
  {
      $this->value = $value;
  }

  public function __toString() : string
  {
      return $this->value;
  }
}
