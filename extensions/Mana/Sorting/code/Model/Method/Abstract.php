<?php
/**
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Sorting_Model_Method_Abstract extends Mage_Core_Model_Abstract {
    const DM_IS_ACTIVE = 0;
    const DM_TITLE = 1;
    const DM_POSITION = 2;

    protected $rules = array(
        'title' => 'required|unique',
        'position' => 'required|numeric',
        'is_active' => 'required',
    );
    protected $captions = array(
        'is_active' => "Status",
    );

    protected $_validator;

    public function validate() {
        $validator = $this->getValidator();

        if(!$validator->passes()) {
            throw new Mana_Core_Exception_Validation($validator->getMessages());
        }
    }

    /**
     * @return Mana_Admin_Model_Validator
     */
    public function getValidator() {
        if(!$this->_validator) {
            $this->_validator = Mage::getModel('mana_admin/validator', array($this->rules, $this->getData(), $this->captions, $this, array()));
        }
        return $this->_validator;
    }

    public function afterCommitCallback() {
        parent::afterCommitCallback();
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            $this->getIndexerSingleton()->processEntityAction($this, static::ENTITY,
                Mage_Index_Model_Event::TYPE_SAVE);
        }
        return $this;
    }

    protected function _afterDeleteCommit() {
        parent::_afterDeleteCommit();
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            $this->getIndexerSingleton()->processEntityAction($this, static::ENTITY,
                Mage_Index_Model_Event::TYPE_DELETE);
        }
        return $this;
    }

    #region Dependencies
    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexerSingleton() {
        return Mage::getSingleton('index/indexer');
    }

    #endregion
}