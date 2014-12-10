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
class Mana_Content_Block_Adminhtml_Folder_ListGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('title');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns() {
        $this->addColumn(
            'title',
            array(
                'header' => $this->__('Title'),
                'index' => 'title',
                'width' => '200px',
                'align' => 'left',
            )
        );
        $this->addColumn(
            'url_key',
            array(
                'header' => $this->__('URL Key'),
                'index' => 'url_key',
                'width' => '50px',
                'align' => 'left',
            )
        );
        $this->addColumn(
            'is_active',
            array(
                'header' => $this->__('Status'),
                'index' => 'is_active',
                'width' => '50px',
                'align' => 'left',
                'type' => 'options',
                'options' => Mage::getSingleton('mana_core/source_status')->toOptionArray(),
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        if ($this->adminHelper()->isGlobal()) {
            $collection = Mage::getResourceModel("mana_content/page_global_collection");
            $collection->getSelect()
                ->join(array('mpgcs' => $collection->getTable('mana_content/page_globalCustomSettings')), '`mpgcs`.`id` = `main_table`.`page_global_custom_settings_id`', array("title", "url_key", "is_active"));
        } else {
            $collection = Mage::getResourceModel("mana_content/page_store_collection");
            $collection->getSelect()
                ->join(array('mpg' => $collection->getTable('mana_content/page_global')), '`mpg`.`id` = `main_table`.`page_global_id`', array())
                ->join(array('mpgcs' => $collection->getTable('mana_content/page_globalCustomSettings')), '`mpgcs`.`id` = `mpg`.`page_global_custom_settings_id`', array());
            $collection->addFieldToFilter('store_id', $this->adminHelper()->getStore()->getId());
        }

        $collection->addFieldToFilter('parent_id', array('null' => true));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/grid');
    }

    /**
     * @param Mana_AttributePage_Model_AttributePage_Abstract $row
     * @return string
     */
    public function getRowUrl($row) {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl(
                '*/mana_content_book/edit',
                array(
                    'id' => $row->getId()
                )
            );
        } else {
            return $this->adminHelper()->getStoreUrl(
                '*/mana_content_book/edit',
                array(
                    'id' => $row->getData('page_global_id'),
                    'store' => $this->adminHelper()->getStore()->getId()
                )
            );
        }
    }

    /**
     * @param Mana_AttributePage_Model_AttributePage_Abstract $row
     * @return string
     */
    public function getRowTitle($row) {
        return $this->__("Edit Book Page '%s'", $row->getData('title'));
    }
}