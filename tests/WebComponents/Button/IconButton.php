<?php
namespace Gang\WebComponentsTests\WebComponents\Button;

abstract class IconButton extends Button
{
    public $is_social_share = false;

    public function __construct()
    {
        $this->with_icon = true;
    }
}
