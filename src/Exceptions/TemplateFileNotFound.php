<?php
declare(strict_types=1);
namespace Gang\WebComponents\Exceptions;

class TemplateFileNotFound extends \Exception
{
    public function __construct(string $templateFile)
    {
        parent::__construct('The template ' . $templateFile . ' does not exist');
    }
}
