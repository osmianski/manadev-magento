<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSuperSlider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterSuperSlider_Model_Attribute extends Mana_Filters_Model_Filter_Attribute {
    public function getLowestPossibleLabel() {
        $items = array_values($this->getItems());
        return $items[0]['label'];
    }
    public function getHighestPossibleLabel() {
        $items = array_values($this->getItems());
        return $items[count($items) - 1]['label'];
    }
    protected function _getLabelByValue($value) {
        foreach ($this->getItems() as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }
    public function getCurrentRangeLowerLabel() {
        $value = $this->getCurrentRangeLowerBound();
        if ($label = $this->_getLabelByValue($value)) {
            return $label;
        }
        else {
            return $this->getLowestPossibleLabel();
        }
    }
    public function getCurrentRangeHigherLabel() {
        $value = $this->getCurrentRangeHigherBound();
        if ($label = $this->_getLabelByValue($value)) {
            return $label;
        }
        else {
            return $this->getHighestPossibleLabel();
        }
    }
    public function getLowestPossibleValue() {
        $items = array_values($this->getItems());
        return $items[0]['value'];
    }
    public function getHighestPossibleValue() {
        $items = array_values($this->getItems());
        return $items[count($items) - 1]['value'];
    }
    public function getCurrentRangeLowerBound() {
        foreach($this->getItems() as $item) {
            if ($item->getMSelected()) {
                return $item->getValue();
            }
        }

        return $this->getLowestPossibleValue();
    }
    public function getCurrentRangeHigherBound() {
        foreach (array_reverse($this->getItems()) as $item) {
            if ($item->getMSelected()) {
                return $item->getValue();
            }
        }

        return $this->getHighestPossibleValue();
    }
    public function getExistingValues() {
        $result = array();
        foreach ($this->getItems() as $item) {
            /* @var $item Mana_Filters_Model_Item */
            $itemData = $item->getSeoData();
            $itemData['label'] = $item->getData('label');
            $result[] = $itemData;
        }
        if ($this->coreHelper()->isManadevSeoLayeredNavigationInstalled()) {
            usort($result, array($this, '_sortExistingValues'));
        }
        return $result;
    }
    public function _sortExistingValues($a, $b) {
        if ($a['position'] < $b['position']) return -1;
        if ($a['position'] > $b['position']) return 1;
        return 0;
    }

    protected function _getItemsData() {
        $selectedOptionIds = $this->getMSelectedValues();

        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $key = $this->getLayer()->getStateKey() . '_' . $this->_requestVar;
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        /* @var $query Mana_Filters_Model_Query */
        $query = $this->getQuery();

        if ($data === null && $this->itemHelper()->isEnabled() &&
            $this->_getIsFilterable() == self::OPTIONS_ONLY_WITH_RESULTS)
        {
            $data = $query->getFilterCounts($this->getFilterOptions()->getCode());
        }
        if ($data === null) {
            $options = $attribute->getFrontend()->getSelectOptions();
            $optionsCount = $query->getFilterCounts($this->getFilterOptions()->getCode());
            $data = array();

            foreach ($options as $option) {
                if (is_array($option['value'])) {
                    continue;
                }
                if (Mage::helper('core/string')->strlen($option['value'])) {
                    $data[] = $current = array(
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                        'm_selected' => in_array($option['value'], $selectedOptionIds),
                    );
                }
            }
        }

        $tags = array(
            Mage_Eav_Model_Entity_Attribute::CACHE_TAG . ':' . $attribute->getId()
        );

        $tags = $this->getLayer()->getStateTags($tags);

        $sortMethod = $this->getFilterOptions()->getSortMethod() ? $this->getFilterOptions()->getSortMethod() : 'byPosition';
        foreach ($data as $position => &$item) {
            $item['position'] = $position;
        }
        usort($data, array(Mage::getSingleton('mana_filters/sort'), $sortMethod));

        $first = $last = -1;
        if ($rangeAndLabels = $this->_getRangeAndLabels()) {
            extract($rangeAndLabels);
            /* @var $from string */
            /* @var $to string */
        }
        else {
            $from = $to = -1;
        }
        if ($this->_getIsFilterable() != 2) {
            foreach ($data as $index => $current) {
                if ($current['count'] || $current['value'] == $from) {
                    $first = $index;
                    break;
                }
            }
            foreach (array_reverse($data) as $index => $current) {
                if ($current['count'] || $current['value'] == $to) {
                    $last = count($data) - $index - 1;
                    break;
                }
            }
            if ($first != -1) {
                $data = array_slice($data, $first, $last - $first + 1);
            }
            else {
                $data = array();
            }
        }

        $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);

        return $data;
    }

    protected function _getRangeAndLabels($request = null) {
        if (!$request) {
            $request = Mage::app()->getRequest();
        }
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return false;
        }

        $text = array();
        foreach ($this->getMSelectedValues() as $optionId) {
            $text[] = $this->getAttributeModel()->getFrontend()->getOption($optionId);
        }

        if ($filter && $text && strpos($filter, '_') !== false) {
            list($from, $to) = explode('_', $filter);
            return compact('text', 'from', 'to');
        }
        else {
            return false;
        }
    }

    protected function _isVisible($rangeAndLabels) {
        if ($rangeAndLabels === false) {
            return false;
        }
        extract($rangeAndLabels);
        /* @var $text array */
        /* @var $from string */
        /* @var $to string */
        $isInside = false;
        $found = false;
        foreach ($this->_getItemsData() as $item) {
            if ($item['value'] == $from) {
                if ($item['value'] != $to) {
                    $isInside = true;
                }
                else {
                    $found = true;
                }
            }
            elseif ($item['value'] == $to) {
                if ($isInside) {
                    $isInside = false;
                    $found = true;
                }
            }
        }

        return $found;
    }
    public function getItemsCount() {
        $count = count($this->getItems());
        return $count > 1 ? $count : 0;
    }

    protected function _applyToCollection($collection, $value = null)
    {
        if (($rangeAndLabels = $this->_getRangeAndLabels()) && $this->_isVisible($rangeAndLabels)) {
            extract($rangeAndLabels);
            /* @var $text array */
            /* @var $from string */
            /* @var $to string */
            $isInside = false;
            $items = $this->_getItemsData();
            $values = array();
            foreach ($items as $item) {
                if ($item['value'] == $from) {
                    if ($item['value'] != $to) {
                        $isInside = true;
                    }
                    $values[] = $item['value'];
                } elseif ($item['value'] == $to) {
                    if ($isInside) {
                        $isInside = false;
                        $values[] = $item['value'];
                    }
                } elseif ($isInside) {
                    $values[] = $item['value'];
                }
            }
            parent::_applyToCollection($collection, $values);
        }


        //$this->_getResource()->applyToCollection($collection, $this, $this->getMSelectedValues());
    }

    public function addToState() {
        if (($rangeAndLabels = $this->_getRangeAndLabels()) && $this->_isVisible($rangeAndLabels)) {
            extract($rangeAndLabels);
            /* @var $text array */
            $this->getLayer()->getState()->addFilter($this->_createItemEx(array(
                'label' => $text[0] . ' - ' . $text[count($text) - 1],
                'value' => Mage::app()->getRequest()->getParam($this->_requestVar),
                'm_selected' => true,
                'm_show_selected' => false,
                'remove_url' => $this->getRemoveUrl(),
            )));
        }
    }
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        return $this;
    }

    public function isFilterAppliedWhenCounting($modelToBeApplied) {
        if ($this->_getIsFilterable() != 2) {
            return $modelToBeApplied != $this && $modelToBeApplied->getFilterOptions()->getDisplay() != 'slider';
        }
        else {
            return false;
        }
    }
}