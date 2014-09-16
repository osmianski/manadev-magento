<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Page_Block_Adminhtml_Special_ListGrid extends Mana_Admin_Block_V2_Grid  {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('title');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns() {
        $this->addColumn('title', array(
            'header' => $this->__('Title'),
            'index' => 'title',
            'width' => '200px',
            'align' => 'left',
        ));
        $this->addColumn('url_key', array(
            'header' => $this->__('SEO URL Key'),
            'index' => 'url_key',
            'width' => '200px',
            'align' => 'left',
        ));
        $this->addColumn('condition', array(
            'header' => $this->__('Condition'),
            'index' => 'condition',
            'align' => 'left',
            'type' => 'text',
            'nl2br' => true,
            'escape' => true,
        ));
        if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
            $this->addColumn('filter', array(
                'header' => $this->__('Show In Filter'),
                'index' => 'filter',
                'width' => '200px',
                'align' => 'center',
                'type'  => 'options',
                'options' => Mage::getModel('mana_filters/source_filter')->exclude('category')->getOptionArray(),
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        /* @var $collection Mana_Page_Resource_Special_Collection */
        $collection = Mage::getResourceModel("mana_page/special_collection");
        $collection->setItemObjectClass('mana_page/special');
        $collection->setData($resource->getData($this->adminHelper()->getStore()->getId()));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/grid');
    }

    /**
     * @param Mana_Page_Model_Special $row
     * @return string
     */
    public function getRowUrl($row) {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl('*/*/edit', array(
                'id' => $row->getId()
            ));
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/edit', array(
                'id' => $row->getId(),
                'store' => $this->adminHelper()->getStore()->getId()
            ));
        }
    }

    /**
     * @param Mana_Page_Model_Special $row
     * @return string
     */
    public function getRowTitle($row) {
            return $this->__("Edit Special Condition '%s'", $row->getData('title'));
    }
}