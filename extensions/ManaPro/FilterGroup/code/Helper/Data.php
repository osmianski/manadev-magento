<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterGroup
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_FilterGroup module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterGroup_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     * @param $result
     * @param $filterBlocks
     * @return array
     */
    public function getAttributeGroups($filterBlocks) {
        $result = array(
            '' => new Varien_Object(array(
                'name' => '',
                'sort_order' => -1,
                'id' => 0,
                'filters' => array(),
            ))
        );
        $groups = Mage::getResourceModel('manapro_filtergroup/group')->getFilterableAttributeGroups();
        foreach ($filterBlocks as /* @var $filterBlock Mana_Filters_Block_Filter */ $filterBlock) {
            $code = $filterBlock->getFilterOptions()->getCode();
            $group = isset($groups[$code]) ? $groups[$code]['attribute_group_name'] : '';
            if (!isset($result[$group])) {
                $result[$group] = new Varien_Object(array(
                    'name' => $group,
                    'sort_order' => $groups[$code]['sort_order'],
                    'id' => $groups[$code]['attribute_group_id'],
                    'filters' => array(),
                ));
            }
            $filters = $result[$group]->getFilters();
            $filters[] = $filterBlock;
            $result[$group]->setFilters($filters);
        }
        uasort($result, array($this, '_compareFilterGroups'));
        return $result;
    }
    public function _compareFilterGroups($a, $b) {
        if ($a->getSortOrder() < $b->getSortOrder()) return -1;
        if ($a->getSortOrder() > $b->getSortOrder()) return 1;
        return 0;
    }
}