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
    const DM_ATTRIBUTE_ID_0 = 3;
    const DM_ATTRIBUTE_ID_1 = 4;
    const DM_ATTRIBUTE_ID_2 = 5;
    const DM_ATTRIBUTE_ID_3 = 6;
    const DM_ATTRIBUTE_ID_4 = 7;
    const DM_URL_KEY = 8;


    protected $rules = array(
        'title' => 'required|unique',
        'position' => 'required|numeric',
        'is_active' => 'required',
        'attribute_id_0' => 'required',
        'attribute_id_1' => 'required',
        'url_key' => 'required|unique',
    );
    protected $captions = array(
        'is_active' => "Status",
        'attribute_id_0' => "Attribute",
        'attribute_id_1' => "Attribute",
        'url_key' => "URL Key",
    );
    protected $nullableFields = array(
        'attribute_id_2',
        'attribute_id_3',
        'attribute_id_4',
    );

    protected $_validator;

    public function save() {
        foreach($this->nullableFields as $field) {
            if(trim($this->getData($field)) == "") {
                $this->setData($field, null);
            }
        }
        return parent::save();
    }

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