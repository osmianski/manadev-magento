<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Resources/DB operations with model collections */
/**
 * This resource model handles DB operations with a collection of models of type Mana_Filters_Model_Filter2_Value_Store. All 
 * database specific code for operating collection of Mana_Filters_Model_Filter2_Value_Store should go here.
 * @author Mana Team
 */
class Mana_Filters_Resource_Filter2_Value_Store_Collection extends Mana_Filters_Resource_Filter2_Value_Collection
{
    /**
     * Invoked during resource collection model creation process, this method associates this 
     * resource collection model with model class and with resource model class
     */
    protected function _construct()
    {
        $this->_init(strtolower('Mana_Filters/Filter2_Value_Store'));
    }

}
