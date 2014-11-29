<?php
/** 
 * @category    Mana
 * @package     ManaPro_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_Content module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_Content_Helper_Data extends Mage_Core_Helper_Abstract {
    public function processRelatedProductIds($ids = array()) {
        if(!$current_id = Mage::app()->getRequest()->getPost('id')) {
            $current_id = Mage::app()->getRequest()->getParam('id');
        }
        $savedRelatedProductIds = array();
        if (!is_null($current_id) && substr($current_id, 0, 1) != "n") {
            $model = Mage::getModel('mana_content/page_globalCustomSettings');
            $model->load($model->getCustomSettingId($current_id));
            if(!is_null($model->getReferenceId())) {
                $current_id = $model->getReferenceId();
            }
            $savedRelatedProductIds = Mage::getModel('catalog/product')->getCollection()
                ->joinTable(array('mprp' => 'mana_content/page_relatedProduct'), 'product_id=entity_id', array('product_id'), "{{table}}.`page_global_id` = " . $current_id)
                ->getAllIds();
        }
        foreach($ids as $id) {
            if(strpos($id, 0, 1) == "-") {
                $id = strpos($id, 1, strlen($id));
                $key = array_search($id, $savedRelatedProductIds);
                unset($savedRelatedProductIds[$key]);
            } else {
                $savedRelatedProductIds[] = $id;
            }
        }
        Mage::register('related_product_ids', $savedRelatedProductIds);
    }

    public function tagStringToArray($tagNamesInString) {
        return $this->_cleanTags($this->_extractTags($tagNamesInString));
    }

    protected function _cleanTags(array $tagNamesArr)
    {
        foreach( $tagNamesArr as $key => $tagName ) {
            $tagNamesArr[$key] = trim($tagNamesArr[$key], ',');
            $tagNamesArr[$key] = trim($tagNamesArr[$key]);
            if( $tagNamesArr[$key] == '' ) {
                unset($tagNamesArr[$key]);
            }
        }
        return $tagNamesArr;
    }

    protected function _extractTags($tagNamesInString)
    {
        return explode("\n", preg_replace("/(,+)/i", "$1\n", $tagNamesInString));
    }
}