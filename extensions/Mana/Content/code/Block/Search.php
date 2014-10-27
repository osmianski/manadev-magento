<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Block_Search extends Mage_Core_Block_Template {

    public function __construct() {
        $this->setTemplate('mana/content/search.phtml');
    }

    protected function _prepareLayout() {
        // create client-side block
        $this->_prepareClientSideBlock();

    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');

        $data = array(
            'type' => 'Mana/Content/Tree/Search',
            'load_url' => $this->getUrl('content/tree/load'),
        );

        $this->setData('m_client_side_block', $data);
        return $this;
    }
}