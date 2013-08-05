<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterTree
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterTree_Model_Observer {
	/**
	 * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_load_before")
	 * @param Varien_Event_Observer $observer
	 */
	public function beforeLayoutLoad(/** @noinspection PhpUnusedParameterInspection */ $observer) {
	    if ($this->coreHelper()->getRoutePath() == 'adminhtml/mana_filters/edit') {
            if (Mage::getStoreConfigFlag('mana/message/make_all_categories_anchor_for_tree_filter')) {
                $cssClass = 'make_all_categories_anchor_for_tree_filter';
                $this->getSessionSingleton()->addNotice($this->treeHelper()->__("Category tree requires all categories to have 'Is Anchor' set to 'Yes'.") .
                    ' <a href="#" class="' . $cssClass . '-action">' . $this->filterAdminHelper()->__('Make all categories anchored') . '</a>' .
                    $this->treeHelper()->__(" or ") .
                    ' <a href="#" class="' . $cssClass . '-message">' . $this->adminHelper()->__('Hide this message') . '</a> '
                );
            }
        }
	}

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_render_before")
     * @param Varien_Event_Observer $observer
     */
    public function beforeLayoutRender($observer) {
        if ($this->coreHelper()->getRoutePath() == 'adminhtml/mana_filters/edit') {
            $this->jsHelper()->setConfig('filterTree.make_all_categories_anchor_for_tree_filter_message_url',
                $this->urlTemplateHelper()->encodeAttribute($this->getUrlModel()->getUrl('*/mana/hideMessage',
                array('message_key' => 'make_all_categories_anchor_for_tree_filter'))));
            $this->jsHelper()->setConfig('filterTree.make_all_categories_anchor_for_tree_filter_action_url',
                $this->urlTemplateHelper()->encodeAttribute($this->getUrlModel()->getUrl('*/mana_filters/makeAllCategoriesAnchor',
                $this->adminHelper()->isGlobal() ? array() : array('store' => Mage::app()->getRequest()->getParam('store', 0)))));
        }
    }
	#region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
	    return Mage::helper('mana_core');
	}

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    public function getSessionSingleton() {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @return ManaPro_FilterTree_Helper_Data
     */
    public function treeHelper() {
        return Mage::helper('manapro_filtertree');
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return ManaPro_FilterAdmin_Helper_Data
     */
    public function filterAdminHelper() {
        return Mage::helper('manapro_filteradmin');
    }

    /**
     * @return Mana_Core_Helper_Js
     */
    public function jsHelper() {
        return Mage::helper('mana_core/js');
    }

    /**
     * @return Mana_Core_Helper_UrlTemplate
     */
    public function urlTemplateHelper() {
        return Mage::helper('mana_core/urlTemplate');
    }

    /**
     * @return Mage_Adminhtml_Model_Url
     */
    public function getUrlModel() {
        return Mage::getModel('adminhtml/url');
    }
    #endregion
}