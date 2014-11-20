<?php
/**
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Resource_Page_RelatedProduct_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct() {
        $this->_init('mana_content/page_relatedProduct');
    }

    public function unlinkProducts($page_global_id, $product_ids = array()) {
        if(!empty($product_ids)) {
            $cond = "page_global_id = ". $page_global_id ." AND product_id IN (". implode(",", $product_ids) .")";
            $this->getConnection()->delete($this->getMainTable(), $cond);
        }
    }

    public function linkProducts($page_global_id, $product_ids) {
        foreach($product_ids as $product_id) {
            $record = array(
                'page_global_id' => $page_global_id,
                'product_id' => $product_id,
            );
            $this->getConnection()->insert($this->getMainTable(), $record);
        }
    }

}