<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Model_AttributePage_StoreCustomSettings extends Mana_AttributePage_Model_AttributePage_Abstract {
    const ENTITY = 'mana_attributepage/attributePage_storeCustomSettings';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }

    public function afterCommitCallback() {
        parent::afterCommitCallback();
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            $this->getIndexerSingleton()->processEntityAction($this, self::ENTITY,
                Mage_Index_Model_Event::TYPE_SAVE);
        }
        return $this;
    }

    protected function _afterDeleteCommit() {
        parent::_afterDeleteCommit();
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            $this->getIndexerSingleton()->processEntityAction($this, self::ENTITY,
                Mage_Index_Model_Event::TYPE_DELETE);
        }
        return $this;
    }
}