<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Model_Condition_Combine extends Mana_Core_Model_Condition_Combine {
    protected function _construct()
    {
        parent::_construct();
        $this->setData('type', 'manapro_filtercontent/condition_combine');
    }

    public function getNewChildSelectOptions()
    {
        $attributes = array();
        foreach ($this->getFilterResource()->getAttributes() as $filter) {
            $attributes[] = array(
                'value'=>"manapro_filtercontent/condition_filter_{$filter['type']}|{$filter['value']}",
                'label'=> $filter['label']);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array(
                'value' => 'manapro_filtercontent/condition_combine',
                'label' => $this->contentHelper()->__('Condition Combination')
            ),
            array(
                'label' => $this->contentHelper()->__('Page Type'),
                'value' => 'manapro_filtercontent/condition_pageType'
            ),
            array(
                'label' => $this->contentHelper()->__('Applied Filter'),
                'value' => $attributes
            ),
        ));
        return $conditions;
    }

    #region Dependencies

    /**
     * @return ManaPro_FilterContent_Resource_Filter
     */
    public function getFilterResource() {
        return Mage::getResourceSingleton('manapro_filtercontent/filter');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Data
     */
    public function contentHelper() {
        return Mage::helper('manapro_filtercontent');
    }
    #endregion
}