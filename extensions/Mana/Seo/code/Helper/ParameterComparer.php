<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Helper_ParameterComparer extends Mage_Core_Helper_Abstract {
    protected $_positions = array();

    public function setPositions($positions) {
        $this->_positions = $positions;

        return $this;
    }

    public function compare($a, $b) {
        $aSet = isset($this->_positions[$a]);
        $bSet = isset($this->_positions[$b]);
        if ($aSet) {
            if ($bSet) {
                if ($this->_positions[$a]['position'] < $this->_positions[$b]['position']) return -1;
                if ($this->_positions[$a]['position'] > $this->_positions[$b]['position']) return 1;

                if ($this->_positions[$a]['id'] < $this->_positions[$b]['id']) return -1;
                if ($this->_positions[$a]['id'] > $this->_positions[$b]['id']) return 1;

                return 0;
            }
            else {
                return -1;
            }
        }
        else {
            if ($bSet) {
                return 1;
            }
            else {
                return 0;
            }
        }
    }
}