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
class ManaPro_FilterSeoLinks_Model_Noindex_Page extends ManaPro_FilterSeoLinks_Model_Condition
{
    public function detect($layerModel) {
        return Mage::app()->getRequest()->getParam('p') != null;
    }
}