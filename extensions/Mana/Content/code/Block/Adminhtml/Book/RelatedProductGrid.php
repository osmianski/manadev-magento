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
class Mana_Content_Block_Adminhtml_Book_RelatedProductGrid extends Mana_Admin_Block_V2_Grid {
    public function __construct() {
        parent::__construct();
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
}