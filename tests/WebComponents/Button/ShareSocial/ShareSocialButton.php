<?php
namespace Gang\WebComponentsTests\WebComponents\Button\ShareSocial;

use Gang\WebComponentsTests\WebComponents\Button\IconButton;

abstract class ShareSocialButton extends IconButton
{
    public function __construct()
    {
        $this->is_social_share = true;
        parent::__construct();
    }
}
