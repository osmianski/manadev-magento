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
class ManaPro_Slider_Block_Product_Grid extends Mana_Admin_Block_Crud_Detail_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('mSliderProductGrid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setFilterVisibility(false);
    }
    public function getGridUrl() {
        return Mage::helper('mana_admin')->getStoreUrl('*/mana_slider/productGrid',
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
                if (isset($widgetParameters['products_json']) && ($productJson = $widgetParameters['products_json'])) {
                    $productJson = htmlspecialchars_decode($productJson);
                    if ($products = json_decode($productJson, true)) {
                        foreach ($products as $productData) {
                            /* @var $product ManaPro_Slider_Model_Product */
                            $product = Mage::getModel('manapro_slider/product');
                            $product
                                    ->setData($productData)
                                    ->setEditStatus(-1)
                                    ->setEditSessionId($edit['sessionId'])
                                    ->save();
                            $edit['saved'][$product->getId()] = $product->getId();
                        }
                    }
                }
            }
            $this->setEdit($edit);
        }

        /* @var $collection ManaPro_Slider_Resource_Product_Collection */
        $collection = Mage::getResourceModel('manapro_slider/product_collection');
        $collection
            ->addColumnToSelect(array('edit_massaction', 'product_id', 'position', 'image_index'))
            ->setEditFilter($this->getEdit())
            ->addProductColumnsToSelect();

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
        $this->addColumn('product_id', array(
            'header' => $this->__('Product ID'),
            'index' => 'product_id',
            'width' => '100px',
            'align' => 'center',
        ));
        $this->addColumn('product_name', array(
            'header' => $this->__('Product Name'),
            'index' => 'product_name',
        ));
        $this->addColumn('position', array(
                'header' => $this->__('Position'),
            'index' => 'position',
            'header_css_class' => 'c-position',
            'renderer' => 'mana_admin/column_input',
            'width' => '50px',
            'align' => 'center'
        ));
        $this->addColumn('image_index', array(
            'header' => $this->__('Image Index'),
            'index' => 'image_index',
            'header_css_class' => 'c-image-index',
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
        Mage::helper('mana_core/js')->options('#mSliderProductGrid', array(
            'chooserUrl' => Mage::helper('mana_admin')->getStoreUrl('*/mana_slider/chooseProducts'),
        ));
        return parent::_beforeToHtml();
    }
}