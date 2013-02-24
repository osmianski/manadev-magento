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
class Mana_Social_Block_Links extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mana/social/links.phtml');
    }

    protected $_links;

    /**
     * @return Mana_Social_Model_Link[]
     */
    public function getLinks()
    {
        if (!$this->_links) {
            /* @var $social Mana_Social_Model_Social */
            $social = Mage::getModel('mana_social/social');

            $this->_links = $social->getLinks();
        }
        return $this->_links;
    }

    public function getCount()
    {
        return count($this->getLinks());
    }
}