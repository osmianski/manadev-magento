<?php

class ManaPro_FilterColors_Block_Grid extends Mana_Admin_Block_Crud_Detail_Grid {
	public function __construct() {
        parent::__construct();
        $this->setId('colorsGrid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
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
		$collection->addColumnToSelect(array('position', 'name', 'color', 'normal_image', 'selected_image', 
			'normal_hovered_image', 'selected_hovered_image'));

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
	protected function _prepareColumns() {
	    $filter = Mage::registry('m_crud_model');
		$this->addColumn('position', array(
			'header' => $this->__('Position'),
			'index' => 'position',
			'width' => '50px',
			'align' => 'center', 
		));
		$this->addColumn('name', array(
			'header' => $this->__('Name'),
			'index' => 'name',
		));
		$this->addColumn('color', array_merge(array(
			'header' => $this->__('Color'),
			'index' => 'color',
			'header_css_class' => 'c-color',
			'renderer' => 'manapro_filtercolors/column_color',
			'width' => '50px',
            'image_width' => $filter->getImageWidth(),
            'image_height' => $filter->getImageHeight(),
            'image_border_radius' => $filter->getImageBorderRadius(),
			'align' => 'center',
		), Mage::helper('mana_admin')->isGlobal() ? array() : array(
			'default_bit' => Mana_Filters_Resource_Filter2_Value::DM_COLOR,
			'default_label' => $this->__('Same For All Stores'),
		)));
		$this->addColumn('normal_image', array_merge(array(
			'header' => $this->__('Image'),
			'index' => 'normal_image',
            'header_css_class' => 'c-normal_image',
            'renderer' => 'manapro_filtercolors/column_image',
			'width' => '50px',
			'image_width' => $filter->getImageWidth(),
            'image_height' => $filter->getImageHeight(),
            'image_border_radius' => $filter->getImageBorderRadius(),
			'align' => 'center',
		), Mage::helper('mana_admin')->isGlobal() ? array() : array(
			'default_bit' => Mana_Filters_Resource_Filter2_Value::DM_NORMAL_IMAGE,
			'default_label' => $this->__('Same For All Stores'),
		)));
        $this->addColumn('selected_image', array_merge(array(
            'header' => $this->__('Selected Image'),
            'index' => 'selected_image',
            'header_css_class' => 'c-selected_image',
            'renderer' => 'manapro_filtercolors/column_image',
            'width' => '50px',
            'image_width' => $filter->getImageWidth(),
            'image_height' => $filter->getImageHeight(),
            'image_border_radius' => $filter->getImageBorderRadius(),
            'align' => 'center',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => Mana_Filters_Resource_Filter2_Value::DM_SELECTED_IMAGE,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $this->addColumn('normal_hovered_image', array_merge(array(
            'header' => $this->__('Mouse Over'),
            'index' => 'normal_hovered_image',
            'header_css_class' => 'c-normal_hovered_image',
            'renderer' => 'manapro_filtercolors/column_image',
            'width' => '50px',
            'image_width' => $filter->getImageWidth(),
            'image_height' => $filter->getImageHeight(),
            'image_border_radius' => $filter->getImageBorderRadius(),
            'align' => 'center',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => Mana_Filters_Resource_Filter2_Value::DM_NORMAL_HOVERED_IMAGE,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $this->addColumn('selected_hovered_image', array_merge(array(
            'header' => $this->__('Selected Mouse Over'),
            'index' => 'selected_hovered_image',
            'header_css_class' => 'c-selected_hovered_image',
            'renderer' => 'manapro_filtercolors/column_image',
            'width' => '50px',
            'image_width' => $filter->getImageWidth(),
            'image_height' => $filter->getImageHeight(),
            'image_border_radius' => $filter->getImageBorderRadius(),
            'align' => 'center',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => Mana_Filters_Resource_Filter2_Value::DM_SELECTED_HOVERED_IMAGE,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $this->addColumn('state_image', array_merge(array(
             'header' => $this->__('In State'),
             'index' => 'state_image',
             'header_css_class' => 'c-state_image',
             'renderer' => 'manapro_filtercolors/column_image',
             'width' => '50px',
             'image_width' => $filter->getStateWidth(),
             'image_height' => $filter->getStateHeight(),
             'image_border_radius' => $filter->getStateBorderRadius(),
             'align' => 'center',
         ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
             'default_bit' => Mana_Filters_Resource_Filter2_Value::DM_STATE_IMAGE,
             'default_label' => $this->__('Same For All Stores'),
         )));
		parent::_prepareColumns();
		return $this;
	}
    public function getGridUrl() {
    	return Mage::helper('mana_admin')->getStoreUrl('*/*/tabColorsGrid', 
			array('id' => Mage::app()->getRequest()->getParam('id')), 
			array('ajax' => 1)
		);
    }
}