<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Model_AttributePage_Store extends Mana_AttributePage_Model_AttributePage_Abstract {
    const ENTITY = 'mana_attributepage/attributePage_store';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }

    public function canShow() {
        return $this->getData('is_active');
    }

    public function getUrl() {
        return Mage::getUrl('mana/attributePage/view', array(
            'id' => $this->getId(),
            '_use_rewrite' => true,
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure(),
        ));
    }

    public function loadByOptionPageStoreId($optionPageStoreId) {
        if ($id = $this->getResource()->getIdByOptionPageStoreId($optionPageStoreId)) {
            $this->load($id);
        }

        return $this;
    }

    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    public function getOptionPages() {
        $collection = $this->createOptionPageCollection()
            ->addAttributePageFilter($this->getData('attribute_page_global_id'))
            ->addStoreFilter($this->getData('store_id'))
            ->setOrder('title', 'ASC');

        $collection->getSelect()->columns(array(
            'alpha' => new Zend_Db_Expr("LEFT(main_table.title, 1)"),
        ));
        return $collection;
    }

    /**
     * @return Mana_AttributePage_Resource_AttributePage_Store
     */
    public function getResource() {
        return parent::getResource();
    }

    #region Dependencies
    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    public function createOptionPageCollection() {
        return Mage::getResourceModel('mana_attributepage/optionPage_store_collection');
    }
    #endregion
}