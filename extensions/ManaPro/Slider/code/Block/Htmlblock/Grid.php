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
class ManaPro_Slider_Block_Htmlblock_Grid extends Mana_Admin_Block_Crud_Detail_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('mSliderHtmlblockGrid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setFilterVisibility(false);
    }
    public function getGridUrl() {
        return Mage::helper('mana_admin')->getStoreUrl('*/mana_slider/htmlBlockGrid',
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
                if (isset($widgetParameters['htmlblocks_json']) && ($blockJson = $widgetParameters['htmlblocks_json'])) {
                    $blockJson = htmlspecialchars_decode($blockJson);
                    if ($blocks = json_decode($blockJson, true)) {
                        foreach ($blocks as $blockData) {
                            /* @var $block ManaPro_Slider_Model_Htmlblock */
                            $block = Mage::getModel('manapro_slider/htmlblock');
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
        $collection = Mage::getResourceModel('manapro_slider/htmlblock_collection');
        $collection
            ->addColumnToSelect(array('edit_massaction', 'html', 'position'))
            ->setEditFilter($this->getEdit());

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
        $this->addColumn('html', array(
            'header' => $this->__('HTML'),
            'index' => 'html',
            'align' => 'center',
            'header_css_class' => 'c-html',
            'renderer' => 'mana_admin/column_input',
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
}