<?php
namespace Gang\WebComponentsTests\WebComponents\Button;

use Gang\WebComponents\HTMLComponent;

class Button extends HTMLComponent
{
    const LEFT_POSITION = 'left';
    const RIGHT_POSITION = 'right';

    /**
     * Add id attribute to button
     */
    public $id;

    /**
     * Add size class to button
     */
    public $size = 'md';

    /**
     * Add type of button
     */
    public $type = 'primary';

    /**
     * Add disabled attribute and class
     * to button element
     */
    public $disabled;

    /**
     * Add link to button
     */
    public $href;

    /**
     * Move button to center, right or left
     */
    public $position;

    /**
     * Add role to button
     */
    public $role = 'button';

    /**
     * Make button with to 100%
     */
    public $block = false;

    /**
     * Add class icon to button class
     */
    public $with_icon = false;

    public $title;

    public $rel;

    public $target;

    public $onclick;

    public function getTemplate() : string
    {
        return  __DIR__ . DIRECTORY_SEPARATOR . 'Button.twig';
    }
}
