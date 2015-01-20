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
class Mana_Sorting_Model_Method extends Mana_Sorting_Model_Method_Abstract {
    const ENTITY = 'mana_sorting/method';

    public function setDefaults() {
        $this->setData('position', 0);
        $this->setData('is_active', true);
        $this->setData('attribute_id_0_sortdir', 1);
        $this->setData('attribute_id_1_sortdir', 1);
        $this->setData('attribute_id_2_sortdir', 1);
        $this->setData('attribute_id_3_sortdir', 1);
        $this->setData('attribute_id_4_sortdir', 1);
    }

    protected function _construct() {
        $this->_init(self::ENTITY);
    }
}