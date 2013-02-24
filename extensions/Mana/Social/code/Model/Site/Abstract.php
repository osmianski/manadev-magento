<?php
/**
 * @category    Mana
 * @package     Mana_Social
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getCode()
 * @method Mana_Social_Model_Site_Abstract setCode(string $value)
 * @method bool hasTitle()
 * @method string getTitle()
 * @method Mana_Social_Model_Site_Abstract setTitle(string $value)
 * @method bool hasLabel()
 * @method string getLabel()
 * @method Mana_Social_Model_Site_Abstract setLabel(string $value)
 * @method string getLinkCss()
 * @method Mana_Social_Model_Site_Abstract setLinkCss(string $value)
 * @method bool getOpenLinkInNewWindow()
 * @method Mana_Social_Model_Site_Abstract setOpenLinkInNewWindow(bool $value)
 */
abstract class Mana_Social_Model_Site_Abstract extends Varien_Object
{
    /**
     * @var Mana_Social_Model_Share[]
     */
    protected $_sharingActions = array();

    /**
     * @return Mana_Social_Model_Link
     */
    public function getLink()
    {
        if ($this->getConfig('links/%s_url')) {
            /* @var $link Mana_Social_Model_Link */
            $link = Mage::getModel(strtolower('Mana_Social/Link'));
            $link
                ->setTitle($this->hasTitle() ? $this->getTitle() : $this->getConfig('links/%s_title'))
                ->setLabel($this->hasLabel() ? $this->getLabel() : $this->getConfig('links/%s_title'))
                ->setCss($this->getLinkCss())
                ->setOpenInNewWindow($this->getOpenLinkInNewWindow())
                ->setUrl($this->getConfig('links/%s_url'))
                ->setSortOrder($this->getConfig('links/%s_sort_order'));
            return $link;
        }
        else {
            return false;
        }
    }

    /**
     * @param Mana_Social_Model_Share $action
     * @return Mana_Social_Model_Site_Abstract
     */
    public function addSharingAction($action) {
        $this->_sharingActions[$action->getCode()] = $action;
        return $this;
    }

    /**
     * @return Mana_Social_Model_Share[]
     */
    public function getSharingActions()
    {
        return $this->_sharingActions;
    }
    public function getConfig($key) {
        return Mage::getStoreConfig("mana_social/".sprintf($key, $this->getCode()));
    }
}