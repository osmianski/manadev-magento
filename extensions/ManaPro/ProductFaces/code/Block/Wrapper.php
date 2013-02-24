<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Wraps child block into a div
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Block_Wrapper extends Mage_Core_Block_Text_List {
    protected function _toHtml() {
    	$result = '<div';
    	foreach (array('id', 'class', 'style') as $attribute) {
    		if ($this->hasData($attribute)) {
    			$result .= ' '.$attribute.'="'.$this->getData($attribute).'"';
    		}
    	}
    	$result .= '>';
    	$result .= parent::_toHtml();
    	$result .= '</div>';
    	return $result;
	}
}