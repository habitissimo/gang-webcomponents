<?php

namespace Gang\WebComponentsTests\WebComponents\Button\ShareSocial;

use Gang\WebComponents\Contracts\TemplateFolderInterface;

class TwitterShareSocialButton extends ShareSocialButton implements TemplateFolderInterface
{
  public $icon_type = 'twitter';

  public function getTemplate(): string
  {
    return parent::getTemplate();
  }
}
