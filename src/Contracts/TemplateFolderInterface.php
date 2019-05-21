<?php
declare(strict_types=1);

namespace Gang\WebComponents\Contracts;

interface TemplateFolderInterface
{
  public function getTemplate(): string;
}
