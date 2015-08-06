<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_ManaController extends Mage_Adminhtml_Controller_Action {
    public function hideMessageAction() {

        $this->utilsHelper()->setStoreConfig('mana/message/'. $this->getRequest()->getParam('message_key'), 0);
        Mage::app()->cleanCache();
        $this->getResponse()->setBody('ok');
    }

    public function wysiwygAction() {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock('mana_admin/v2_popup_wysiwyg', '', array(
            'editor_element_id' => $elementId. '_editor',
            'store_id'          => $storeId,
            'store_media_url'   => $storeMediaUrl,
        ));

        $this->getResponse()->setBody($content->toHtml());
    }

    protected function _isAllowed() {
        return true;
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Utils
     */
    public function utilsHelper() {
        return Mage::helper('mana_core/utils');
    }
    #endregion
}