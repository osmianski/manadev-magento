<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Block_Adminhtml_Filter_ContentGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setData('secondary_order', array(
            'column' => 'secondary_order',
            'dir' => 'asc',
        ));
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns() {
		$this->addColumn('position', array(
			'header' => $this->__('Position'),
			'index' => 'position',
			'width' => '50px',
			'align' => 'center',
		));
        $this->addColumn('content_is_active', array(
            'type' => 'checkbox',
            'header' => $this->__('Enable'),
            'index' => 'content_is_active',
            'width' => '100px',
            'align' => 'center',
            'cell_client_side_block_type' => 'ManaPro/FilterContent/Option/IsActiveCell',
            'readonly' => !$this->adminHelper()->isGlobal(),
        ));
        $this->addColumn('content_priority', array(
            'type' => 'input',
            'header' => $this->__('Priority'),
            'index' => 'content_priority',
            'width' => '50px',
            'align' => 'center',
            'readonly' => !$this->adminHelper()->isGlobal(),
        ));
		$this->addColumn('name', array(
			'header' => $this->__('Name'),
			'index' => 'name',
		));
        $this->addColumn('content_stop_further_processing', array(
            'type' => 'checkbox',
            'header' => $this->__('Stop Further Processing'),
            'index' => 'content_stop_further_processing',
            'width' => '100px',
            'align' => 'center',
            'select_style' => 'width: 150px;',
            'readonly' => !$this->adminHelper()->isGlobal(),
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
		if (Mage::helper('mana_admin')->isGlobal()) {
			/* @var $collection Mana_Filters_Resource_Filter2_Collection */
			$collection = Mage::getResourceModel('mana_filters/filter2_value_collection');
		}
		else {
			$collection = Mage::getResourceModel('mana_filters/filter2_value_store_collection');
			$collection->addStoreFilter(Mage::helper('mana_admin')->getStore());
			$collection->addColumnToSelect('global_id');
		}
		$collection->addFieldToFilter('filter_id', Mage::registry('m_crud_model')->getId());
        $collection->getSelect()->columns(array('secondary_order' =>
            'IF(main_table.edit_status > 0, main_table.edit_status, main_table.option_id)'));
//		$collection->addColumnToSelect(array(
//		    'position',
//		    'name',
//            'content_is_active',
//            'content_is_initialized',
//            'content_priority',
//            'content_stop_further_processing',
//            'content_layout_xml',
//            'content_widget_layout_xml',
//            'content_meta_title',
//            'content_meta_keywords',
//            'content_meta_description',
//            'content_meta_robots',
//            'content_title',
//            'content_subtitle',
//            'content_description',
//            'content_additional_description',
//		));

        if ($edit = $this->getEdit()) {
            $collection->setEditFilter($edit);
        }
        else {
            $collection->setEditFilter(true);
        }
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    public function getGridUrl() {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl('*/*/contentGrid',
                array('id' => Mage::app()->getRequest()->getParam('id')),
                array('ajax' => 1)
            );
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/contentGrid', array(
                'id' => Mage::app()->getRequest()->getParam('id'),
                'store' => $this->adminHelper()->getStore()->getId()
            ));
        }
    }

    /**
     * @param Mana_Seo_Model_Schema $row
     * @return string
     */
    public function getRowUrl($row) {
        return false;
    }

    protected function _prepareMultipleRowColumns() {
        $this->addMultipleRowColumn('content', array(
            'type' => 'form',
            'form' => 'content_form',
        ));
    }

    /**
     * @param Mana_Filters_Model_Filter2_Value $item
     * @return array|void
     */
    public function getMultipleRows($item) {
        return array($item);
    }

    public function getMultipleRowColSpan($parentItem, $childItem, $columnIndex, $column) {
        return 4;
    }

    /**
     * @param Mana_Filters_Model_Filter2_Value $childItem
     * @return string
     */
    public function getMultipleRowClass($childItem) {
        $expanded = $childItem->getData('content_is_active');
        if ($states = mage::app()->getRequest()->getPost('row_expand_collapse_states')) {
            $states = json_decode($states, true);
            if (isset($states[$childItem->getId()])) {
                $expanded = $states[$childItem->getId()];
            }
        }
        return $expanded ? '' : 'hidden';
    }

    protected function _prepareClientSideBlock() {
        parent::_prepareClientSideBlock();

        $block = $this->getData('m_client_side_block');
        $block['type'] = 'ManaPro/FilterContent/Option/Grid';
//        if (!$edit = $this->getEdit()) {
//            $block['edit'] = array('pending' => array(), 'saved' => array(), 'deleted' => array());
//        }
//        $block['edit'] = $this->coreHelper()->jsonForceObjectAndEncode($edit, array('force_object' =>
//            array('pending' => true, 'saved' => true, 'deleted' => true)));

        $this->setData('m_client_side_block', $block);

        return $this;
    }
}