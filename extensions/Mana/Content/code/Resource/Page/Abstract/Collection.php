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
abstract class Mana_Content_Resource_Page_Abstract_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected $_parentFilterEnabled = false;
    protected $_parentId;

    public function setParentFilter($parentId) {
        $this->_parentFilterEnabled = true;
        $this->_parentId = $parentId;
        return $this;
    }

    public function clearParentFilter() {
        $this->_parentFilterEnabled = false;
    }
}