<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Crud_Detail_Tab extends Mage_Adminhtml_Block_Text_List implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    protected $_tabTitle;
    protected $_tabUrl;
    protected $_tabUrlParams = array('id');

    ///////////////////////////////////////////////////////
    // TAB PROPERTIES
    ///////////////////////////////////////////////////////

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel() {
        return $this->__($this->_tabTitle);
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__($this->_tabTitle);
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
        return false;
    }

    public function getAjaxUrl() {
        $params = array('ajax' => 1);
        foreach ($this->_tabUrlParams as $paramKey) {
            $params[$paramKey] = Mage::app()->getRequest()->getParam($paramKey);
        }
        return Mage::helper('mana_admin')->getStoreUrl($this->_tabUrl, $params);
    }
}