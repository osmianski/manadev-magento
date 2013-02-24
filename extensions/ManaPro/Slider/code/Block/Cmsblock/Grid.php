<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Slider_Block_Cmsblock_Grid extends Mana_Admin_Block_Crud_Detail_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('mSliderCmsblockGrid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setFilterVisibility(false);
    }
    public function getGridUrl() {
        return Mage::helper('mana_admin')->getStoreUrl('*/mana_slider/cmsBlockGrid',
            array('instance_id' => Mage::app()->getRequest()->getParam('instance_id')),
            array('ajax' => 1)
        );
    }
    protected function _prepareCollection() {
        if (!$this->getEdit()) {
            /* @var $db Mana_Db_Helper_Data */
            $db = Mage::helper('mana_db');
            $editSessionId = $db->beginEditingIfNotAlreadyDoneSo();
            Mage::helper('mana_core/js')->options('edit-form', array('editSessionId' => $editSessionId));
            $edit = Mage::helper('mana_db')->emptyEdit($editSessionId);

            if ($widgetParameters = $this->getWidgetInstance()->getWidgetParameters()) {
                if (!is_array($widgetParameters)) {
                    $widgetParameters = unserialize($widgetParameters);
                }
                if (isset($widgetParameters['cmsblocks_json']) && ($blockJson = $widgetParameters['cmsblocks_json'])) {
                    $blockJson = htmlspecialchars_decode($blockJson);
                    if ($blocks = json_decode($blockJson, true)) {
                        foreach ($blocks as $blockData) {
                            /* @var $block ManaPro_Slider_Model_Cmsblock */
                            $block = Mage::getModel('manapro_slider/cmsblock');
                            $block
                                    ->setData($blockData)
                                    ->setEditStatus(-1)
                                    ->setEditSessionId($edit['sessionId'])
                                    ->save();
                            $edit['saved'][$block->getId()] = $block->getId();
                        }
                    }
                }
            }
            $this->setEdit($edit);
        }

        /* @var $collection ManaPro_Slider_Resource_Cmsblock_Collection */
        $collection = Mage::getResourceModel('manapro_slider/cmsblock_collection');
        $collection
            ->addColumnToSelect(array('edit_massaction', 'block_id', 'position'))
            ->setEditFilter($this->getEdit())
            ->addBlockColumnsToSelect();

        $this->setCollection($collection);
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
        $this->addColumn('cmsblock_identifier', array(
            'header' => $this->__('Identifier'),
            'index' => 'cmsblock_identifier',
            'width' => '200px',
            'align' => 'center',
        ));
        $this->addColumn('cmsblock_name', array(
            'header' => $this->__('Block Name'),
            'index' => 'cmsblock_name',
        ));
        $this->addColumn('position', array(
                'header' => $this->__('Position'),
            'index' => 'position',
            'header_css_class' => 'c-position',
            'renderer' => 'mana_admin/column_input',
            'width' => '50px',
            'align' => 'center'
        ));

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
    /**
     * Getter
     *
     * @return Mage_Widget_Model_Widget_Instance
     */
    public function getWidgetInstance() {
        return Mage::registry('current_widget_instance');
    }

    protected function _beforeToHtml() {
        Mage::helper('mana_core/js')->options('#mSliderCmsblockGrid', array(
            'chooserUrl' => Mage::helper('mana_admin')->getStoreUrl('*/mana_slider/chooseCmsBlocks'),
        ));
        return parent::_beforeToHtml();
    }
}