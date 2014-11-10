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
class Mana_Content_Block_Adminhtml_Book_RelatedProductGrid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('relatedProductGrid');
        $this->setDefaultSort('title');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareColumns() {
        $this->addColumn('id',array(
                'header' => $this->__('ID'),
                'index' => 'id',
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
        return $html;
    }

    protected function _prepareLayout() {
        /* @var $button Mana_Admin_Block_Grid_Action */
        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.add_related_products")
            ->setData(
                array(
                    'label' => $this->__('Add Related Products'),
                    'class' => 'add',
                )
            );
        $this->setChild('add_related_products', $button);

        return parent::_prepareLayout();
    }

    protected function _prepareCollection() {
//        if ($this->adminHelper()->isGlobal()) {
            $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('price')
                    ->joinTable(array('mprp' => 'mana_content/page_relatedProduct'), 'product_id=entity_id', array('*'));
            $collection->getSelect()->where('`mprp`.`page_global_id` = ?', Mage::registry('m_flat_model')->getId());
//        } else {
//
//        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareClientSideBlock() {
        parent::_prepareClientSideBlock();

        $block = $this->getMClientSideBlock();
        $newBlock = array(
            'type' => 'Mana/Content/Book/RelatedProductGrid'
        );
        $block = array_merge($block, $newBlock);
        $this->setMClientSideBlock($block);
    }

    public function getGridUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/grid');
    }


    protected function _prepareMassaction() {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(true);

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/delete'),
            'confirm' => $this->__('Are you sure?'),
        ));

        return $this;
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