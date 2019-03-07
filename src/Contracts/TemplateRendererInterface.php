<?php

namespace Gang\WebComponents\Contracts;

interface TemplateRendererInterface
{
    public function render($file_content, $context);

    public function getFileExtension(): string;
}
