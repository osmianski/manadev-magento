<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Video_Block_Grid extends Mana_Admin_Block_Crud_Detail_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('mVideoGrid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setFilterVisibility(false);
    }
    public function getGridUrl() {
        return Mage::helper('mana_admin')->getStoreUrl('*/product_video/grid',
            array('id' => Mage::app()->getRequest()->getParam('id')),
            array('ajax' => 1)
        );
    }
    protected function _prepareCollection() {
        $this->setCollection(Mage::helper('manapro_video')->getBackendVideos(
            Mage::registry('m_crud_model'), $this->getEdit()));
        parent::_prepareCollection();
        return $this;
    }
    protected function _prepareColumns() {
        $this->addColumn('edit_massaction', array(
            'header' => $this->__('Selected'),
            'index' => 'edit_massaction',
            'header_css_class' => 'c-edit_massaction',
            'renderer' => 'mana_admin/column_checkbox_massaction',
            'width' => '50px',
            'align' => 'center',
        ));
        $this->addColumn('service', array_merge(array(
            'header' => $this->__('Video Service'),
            'index' => 'service',
            'header_css_class' => 'c-service',
            'renderer' => 'mana_admin/column_select',
            'options' => Mage::getModel('manapro_video/source_service')->getOptionArray(),
            'width' => '150px',
            'align' => 'center',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => ManaPro_Video_Resource_Video::DM_SERVICE,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $this->addColumn('service_video_id', array_merge(array(
            'header' => $this->__('Video ID'),
            'index' => 'service_video_id',
            'header_css_class' => 'c-service_video_id',
            'renderer' => 'mana_admin/column_input',
            'width' => '150px',
            'align' => 'left',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => ManaPro_Video_Resource_Video::DM_SERVICE_VIDEO_ID,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $this->addColumn('label', array_merge(array(
            'header' => $this->__('Label'),
            'index' => 'label',
            'header_css_class' => 'c-label',
            'renderer' => 'mana_admin/column_input',
            'align' => 'left',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => ManaPro_Video_Resource_Video::DM_LABEL,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $this->addColumn('thumbnail', array_merge(array(
            'header' => $this->__('Thumbnail'),
            'index' => 'thumbnail',
            'header_css_class' => 'c-thumbnail',
            'renderer' => 'mana_admin/column_image',
            'width' => '56px',
            'no_repeat' => true,
            'align' => 'left',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => ManaPro_Video_Resource_Video::DM_THUMBNAIL,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $this->addColumn('position', array_merge(array(
            'header' => $this->__('Position'),
            'index' => 'position',
            'header_css_class' => 'c-position',
            'renderer' => 'mana_admin/column_input',
            'width' => '50px',
            'align' => 'center',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => ManaPro_Video_Resource_Video::DM_POSITION,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $this->addColumn('is_base', array_merge(array(
            'header' => $this->__('Base Video'),
            'index' => 'is_base',
            'header_css_class' => 'c-is_base',
            'renderer' => 'mana_admin/column_checkbox_single',
            'width' => '50px',
            'align' => 'center',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => ManaPro_Video_Resource_Video::DM_IS_BASE,
            'default_label' => $this->__('Same For All Stores'),
        )));
        $this->addColumn('is_excluded', array_merge(array(
            'header' => $this->__('Excluded'),
            'index' => 'is_excluded',
            'header_css_class' => 'c-is_excluded',
            'renderer' => 'mana_admin/column_checkbox',
            'width' => '50px',
            'align' => 'center',
        ), Mage::helper('mana_admin')->isGlobal() ? array() : array(
            'default_bit' => ManaPro_Video_Resource_Video::DM_IS_EXCLUDED,
            'default_label' => $this->__('Same For All Stores'),
        )));

        parent::_prepareColumns();
        return $this;
    }
    protected function _prepareLayout() {
        $this->setChild('add_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'label' => $this->__('Add'),
            'class' => 'add m-add',
        )));
        $this->setChild('remove_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'label' => $this->__('Remove Selected'),
            'class' => 'delete m-remove',
        )));
        return parent::_prepareLayout();
    }
    public function getMainButtonsHtml() {
        $html = '';
        $html .= $this->getChildHtml('add_button');
        $html .= $this->getChildHtml('remove_button');
        $html .= parent::getMainButtonsHtml();
        return $html;
    }
}