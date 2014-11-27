<?php
/** 
 * @category    Mana
 * @package     ManaPro_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Content_Model_Page_Tag extends Mage_Core_Model_Abstract {

    public function validateTag($tagNamesInString) {
        $tagsArray = $this->contentHelper()->tagStringToArray($tagNamesInString);
        $validated = array();
        foreach($tagsArray as $tag) {
            if(in_array($tag, $validated)) {
                throw new Exception($this->contentHelper()->__("Duplicate tag `%s` found.", $tag));
            }
            array_push($validated, $tag);
        }
    }
    #region Dependencies
    /**
     * @return Mana_Content_Helper_Data
     */
    public function contentHelper() {
        return Mage::helper('manapro_content');
    }
    #endregion

}