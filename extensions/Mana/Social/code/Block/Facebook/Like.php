<?php
/**
 * @category    Mana
 * @package     Mana_Social
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Social_Block_Facebook_Like extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mana/social/facebook/like.phtml');
    }
}