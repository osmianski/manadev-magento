<?php
/**
 * @category    Mana
 * @package     Mana_ProductLists
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
abstract class Mana_ProductLists_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->_getProduct()->getId()) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
    }

    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }
    
    abstract protected function _getCollectionType();
    abstract protected function _getLinkType();
    
    protected function _createCollection($purpose) {
    	return Mage::helper('mana_productlists')->createCollection($this->_getCollectionType(), $this->_getProduct(), $purpose);
    }
    public function _applyInProductsFilter($collection, $column) {
    	$productIds = $this->_getSelectedProducts();
        if (empty($productIds)) {
        	$productIds = array(0);
        }
        if ($column->getFilter()->hasValue()) {
	        $collection->applyProductFilter($column->getFilter()->getValue(), $productIds);
        }
    }
    protected function _prepareCollection()
    {
        $this->setCollection($this->_createCollection('all_data'));
        return parent::_prepareCollection();
    }
    public function isReadonly()
    {
        return false;
    }
    protected function _prepareColumns() {
    if (!$this->isReadonly()) {
            $this->addColumn('in_products', array(
                'header_css_class'  => 'a-center',
                'type'              => 'checkbox',
                'name'              => 'in_products',
                'values'            => $this->_getSelectedProducts(),
                'align'             => 'center',
                'index'             => 'entity_id',
            	'filter_condition_callback' => array($this, '_applyInProductsFilter')
            ));
        }

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('catalog')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('catalog')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));

        return $this;
	}
    public function getGridUrl() {
    	$options = Mage::getConfig()->getNode('mana_productlists/types/'.$this->_getLinkType());
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl((string)$options->grid_action, array('_current' => true));
    }
    protected function _getSelectedProducts()
    {
        $products = $this->getClientData();
        if (!is_array($products)) {
            $products = array_keys($this->getDbData());
        }
        return $products;
    }
    public function getDbData() {
        $products = array();
        foreach ($this->_createCollection('editable_data') as $product) {
            $products[$product->getId()] = $this->_getEditableValues($product);
        }
        return $products;
    }
    abstract protected function _getEditableValues($product);
}