<?php
/** 
 * @category    Mana
 * @package     ManaPro_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Content_Block_Filter_RelatedProduct extends Mage_Core_Block_Template {

    protected $_items;

    public function __construct() {
        $this->setTemplate('manapro/content/filter/relatedproduct.phtml');
    }

    public function getItems() {
        return $this->_items;
    }

    protected function _beforeToHtml() {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    protected  function _prepareData() {
        $this->_items = Mage::getModel('catalog/product')->getCollection()
            ->joinTable(array('mprp' => 'mana_content/page_relatedProduct'), 'product_id=entity_id', array('product_id'))
            ->addAttributeToSelect('name')
            ->addStoreFilter()
            ->distinct(true);
    }


}