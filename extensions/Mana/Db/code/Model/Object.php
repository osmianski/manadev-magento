<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Db_Model_Object extends Mage_Core_Model_Abstract {
	public function getEntityName() {
		return $this->getResourceName();
	}
	public function loadByGlobalId($globalId, $storeId) {
        $this->_getResource()->loadByGlobalId($this, $globalId, $storeId);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
	}

    public function afterCommitCallback() {
		parent::afterCommitCallback();
		if (!$this->getdata('_m_prevent_replication')) {
			Mage::helper('mana_db')->replicate(array(
				'trackKeys' => true,
				'filter' => array($this->getResourceName() => array('saved' => array($this->getId()))),
			));
		}
	}
	protected function _afterDeleteCommit() {
		parent::_afterDeleteCommit();
		if (!$this->getdata('_m_prevent_replication')) {
			Mage::helper('mana_db')->replicate(array(
				'trackKeys' => true,
				'filter' => array($this->getResourceName() => array('deleted' => array($this->getId()))),
			));
		}
	}
	public function addEditedData($fields, $useDefault) {
		$this->getResource()->addEditedData($this, $fields, $useDefault);
	}
    public function addEditedDetails($request) {
        $this->getResource()->addEditedDetails($this, $request);
    }
	public function validateKeys() {
		$result = Mage::getModel('mana_db/validation');
		$this->_validateKeys($result);
		Mage::dispatchEvent('m_db_validateKeys', array('object' => $this, 'result' => $result));
		if (count($result->getErrors())) {
			throw new Mana_Db_Exception_Validation($result->getErrors());
		}
	}
	public function validate() {
		$result = Mage::getModel('mana_db/validation');
		$this->_validate($result);
		Mage::dispatchEvent('m_db_validate', array('object' => $this, 'result' => $result));
		if (count($result->getErrors())) {
			throw new Mana_Db_Exception_Validation($result->getErrors());
		}
	}
	protected function _validateKeys($result) {
	}
	protected function _validate($result) {
	}
    public function assignDefaultValues() {
        $this->_assignDefaultValues();
        Mage::dispatchEvent('m_db_assign_defaults', array('object' => $this));
        return $this;
    }
    protected function _assignDefaultValues() {
    }
}