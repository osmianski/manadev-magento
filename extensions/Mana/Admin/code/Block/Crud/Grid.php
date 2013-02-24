<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Crud_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	public function getEntityName() {
		return $this->getCollection()->getEntityName();
	}
    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }
    protected function _prepareCollection() {
		Mage::dispatchEvent('m_crud_grid_collection', array('grid' => $this));
        parent::_prepareCollection();
		return $this;
    }
    protected function _prepareColumns() {
		Mage::dispatchEvent('m_crud_grid_columns', array('grid' => $this));
		parent::_prepareColumns();
		return $this;
    }
    public function canDisplayContainer()
    {
        return $this->getRenderScripts();
    }
}