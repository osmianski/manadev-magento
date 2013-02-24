<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Renders form field as a link
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Block_Form_Link extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element {
    protected $_variablePattern = '/\\$([a-z0-9_]+)/i';
	
    protected function _construct()
    {
        $this->setTemplate('manapro/productfaces/form/link.phtml');
    }
    
    public function wrap($value) {
    	if (($format = $this->_element->getFormat()) && preg_match_all($this->_variablePattern, $format, $matches)) {
            $formatedString = $format;
            foreach ($matches[0] as $matchIndex=>$match) {
                $value = $this->_element->getData($matches[1][$matchIndex]);
                $formatedString = str_replace($match, $value, $formatedString);
            }
            return $formatedString;
    	}
    	else {
    		return $value;
    	}
    }
}