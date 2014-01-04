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
class Mana_Admin_Block_Crud_Detail_Serializer extends Mage_Adminhtml_Block_Template {
	protected function _construct() {
		$this->setTemplate('mana/admin/grid/serializer.phtml');
		parent::_construct();
	}
	
	protected $_grid;
	public function setGrid($name) {
		$this->_grid = $this->getLayout()->getBlock($name);
		return $this;
	}
	public function getGrid() {
		return $this->_grid;
	}
	public function getInfo() {
        /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		$result = new Varien_Object(array(
			'columns' => $this->_getColumns(),
			'cells' => $this->_getCells(),
		));
		//Mage::log(serialize($result), Zend_Log::DEBUG, 'test.log');
        //Mage::log(json_encode($result->getData(), JSON_FORCE_OBJECT), Zend_Log::DEBUG, 'test.log');
        //mage::log(json_last_error(), Zend_Log::DEBUG, 'test.log');
		return $core->jsonForceObjectAndEncode($result->getData());
	}
	public function getEdit() {
	    /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
	    if (!($result = $this->getGrid()->getEdit())) {
            $result = new Varien_Object(array(
                'pending' => $this->_getPending(), // all changed since last reload in format array($id => array($column => $value, ...), ...)
                'saved' => $this->_getSaved(), // all new/edited since last save in format array($id, ...)
                'deleted' => $this->_getDeleted(), // all deleted since last save in format array($id, ...)
            ));
            $result = $result->getData();
        }
		return $core->jsonForceObjectAndEncode($result, array('force_object' => array('pending' => true, 'saved' => true, 'deleted' => true)));
	}
	protected function _getPending() {
		return array();
	}
	protected function _getSaved() {
		return array();
	}
	protected function _getDeleted() {
		return array();
	}
	protected function _getColumns() {
		$result = array();
		foreach ($this->getGrid()->getColumns() as /* @var $column Mage_Adminhtml_Block_Widget_Grid_Column */ $column) {
			if ($column->getRenderer() instanceof Mana_Admin_Block_Column) {
				$result[$column->getId()] = $column->getRenderer()->getColumnInfo();
			}
		}
		return $result;
	}
	protected function _getCells() {
		$result = array();
		foreach ($this->getGrid()->getCollection() as $row) {
			$rowResult = null;
			foreach ($this->getGrid()->getColumns() as /* @var $column Mage_Adminhtml_Block_Widget_Grid_Column */ $column) {
				if ($column->getRenderer() instanceof Mana_Admin_Block_Column) {
					$cellResult = $column->getRenderer()->getCellInfo($row);
					if (count($cellResult)) {
						if (!$rowResult) {
							$rowResult = array();
						}
						$rowResult[$column->getId()] = $cellResult;
					}
				}
			}
			if ($rowResult) {
				$result[$row->getId()] = $rowResult;
			}
		}
		return $result;
	}
}