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
class Mana_Content_Resource_Page_RelatedProduct extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct() {
        $this->_init('mana_content/page_relatedProduct', 'id');
    }

}