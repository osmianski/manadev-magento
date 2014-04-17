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
class ManaPro_FilterContent_Helper_Action_Option extends ManaPro_FilterContent_Helper_Action {
    protected $_value;

    /**
     * @param array $content
     * @return array
     */
    public function read($content) {
        if (!$this->_value) {
            $this->_value = array();
            if (!empty($content['initial_filters'])) {
                $optionIds = array();
                /* @var $item Mana_Filters_Model_Item */
                foreach ($content['initial_filters'] as $item) {
                    /* @var $filter Mana_Filters_Model_Filter2_Store */
                    $filter = $item->getFilter()->getData('filter_options');

                    if ($filter->getType() == 'attribute') {
                        $optionIds[$item->getData('value')] = $item->getData('value');
                    }
                }
                foreach ($this->getOptionResource()->getOptionActions($optionIds) as $action) {
                    $this->_value[] = array(
                        'is_active' => $action['content_is_active'],
                        'stop_further_processing' => $action['content_stop_further_processing'],
                        'layout_xml' => $action['content_layout_xml'],
                        'widget_layout_xml' => $action['content_widget_layout_xml'],
                        'meta_title' => $action['content_meta_title'],
                        'meta_keywords' => $action['content_meta_keywords'],
                        'meta_description' => $action['content_meta_description'],
                        'meta_robots' => $action['content_meta_robots'],
                        'title' => $action['content_title'],
                        'subtitle' => $action['content_subtitle'],
                        'description' => $action['content_description'],
                        'additional_description' => $action['content_additional_description'],
                        'common_directives' => $action['content_common_directives'],
                        'background_image' => $action['content_background_image'],
                        'cache_key' => 'option/' . $action['option_id'],
                    );
                }
            }
        }
        return $this->_value;
    }

    #region Dependencies

    /**
     * @return ManaPro_FilterContent_Resource_Option
     */
    public function getOptionResource() {
        return Mage::getResourceSingleton('manapro_filtercontent/option');
    }
    #endregion
}