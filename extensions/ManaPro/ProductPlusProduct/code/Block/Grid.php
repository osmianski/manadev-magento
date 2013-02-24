<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductPlusProduct
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_ProductPlusProduct_Block_Grid extends Mana_ProductLists_Block_Grid {
    public function __construct()
    {
        parent::__construct();
        $this->setId(ManaPro_ProductPlusProduct_Resource_Setup::LINK_TYPE.'_link_grid');
    }
    protected function _getCollectionType() {
    	return 'manapro_productplusproduct/collection';
    }
	protected function _getLinkType() {
		return ManaPro_ProductPlusProduct_Resource_Setup::LINK_TYPE;
	}
    protected function _prepareColumns() {
    	parent::_prepareColumns();
        $this->addColumn('position', array(
            'header'            => Mage::helper('catalog')->__('Position'),
            'name'              => 'position',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'width'             => 60,
            'editable'          => true,
            'edit_only'         => !$this->_getProduct()->getId()
        ));
    	$this->sortColumnsByOrder();
    	return $this;
    }
	protected function _getEditableValues($product) {
		return array('position' => 1*$product->getPosition());
	}	
}