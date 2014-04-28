<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Model_Follow_Options extends ManaPro_FilterSeoLinks_Model_Condition
{
    public function detect($layerModel) {
        return $this->_countOptionsInTheSameFilter($layerModel) <= 2;
    }
}