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
class ManaPro_Content_Block_Adminhtml_Book_RelatedProductGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('relatedProductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
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
        $this->addColumn('entity_id',array(
                'header' => $this->__('ID'),
                'index' => 'entity_id',
                'width' => '20px',
                'align' => 'center',
            ));
        $this->addColumn('sku',array(
                'header' => $this->__('SKU'),
                'index' => 'sku',
                'width' => '50px',
                'align' => 'center',
            ));
        $this->addColumn('name',array(
                'header' => $this->__('Name'),
                'index' => 'name',
                'width' => '200px',
                'align' => 'left',
            ));
        $this->addColumn('price',array(
                'header' => $this->__('Price'),
                'index' => 'price',
                'width' => '50px',
                'align' => 'left',
        ));
        return parent::_prepareColumns();
    }

    public function getMainButtonsHtml() {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getChildHtml('add_related_products');
        $html .= $this->getChildHtml('remove_selected');
        return $html;
    }

    protected function _prepareLayout() {
        /* @var $button Mana_Admin_Block_Grid_Action */
        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.add_related_products")
            ->setData(
                array(
                    'label' => $this->__('Add Related Products'),
                    'class' => 'add',
                    'disabled' => !is_null($this->getFlatModel()->getData('reference_id')),
                )
            );
        $this->setChild('add_related_products', $button);
        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.remove_selected")
            ->setData(
                array(
                    'label' => $this->__('Remove Selected'),
                    'class' => 'delete',
                    'disabled' => !is_null($this->getFlatModel()->getData('reference_id')),
                )
            );
        $this->setChild('remove_selected', $button);

        return parent::_prepareLayout();
    }

    protected function _prepareCollection() {
        $relatedProductIds = Mage::registry('related_product_ids');
        $collection = Mage::getModel('catalog/product')->getCollection();

        foreach($relatedProductIds as $key => $id) {
            if(substr($id, 0, 1) == "-") {
                unset($relatedProductIds[$key]);
                $remove_id = substr($id, 1, strlen($id) - 1);
                unset($relatedProductIds[array_search($remove_id, $relatedProductIds)]);
            }
        }
        if(count($relatedProductIds)) {
            $collection->addIdFilter($relatedProductIds);
        } else {
            $collection->addFieldToFilter('entity_id', 0);
        }
        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('price');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareClientSideBlock() {
        parent::_prepareClientSideBlock();

        $block = $this->getMClientSideBlock();
        $newBlock = array(
            'type' => 'ManaPro/Content/Book/RelatedProductGrid'
        );
        $block = array_merge($block, $newBlock);
        $this->setMClientSideBlock($block);
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/manapro_content_book/relatedProductGrid');
    }

    /**
     * @return Mana_Content_Model_Page_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    #region Dependencies
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Db_Helper_Data
     */
    public function dbHelper() {
        return Mage::helper('mana_db');
    }

    /**
     * @return Mana_Core_Helper_Json
     */
    public function jsonHelper() {
        return Mage::helper('mana_core/json');
    }

    #endregion
}