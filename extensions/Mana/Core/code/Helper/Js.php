<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Contains methods which makes javascript intensive programming easier.
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Js extends Mage_Core_Helper_Abstract {
	/**
	 * Makes translations of specified strings to be available in client-side scripts.
	 * @param array $translations
	 * @return Mana_Core_Helper_Js
	 */
	public function translations($translations) {
		/* @var $layout Mage_Core_Model_Layout */ $layout = Mage::getSingleton(strtolower('Core/Layout'));
		/* @var $jsBlock Mana_Core_Block_Js */ $jsBlock = $layout->getBlock('m_js');
		$jsBlock->translations($translations);
		return $this; 
	}
	/**
	 * Makes options (specified in $options key-value pair array) for HTML element (selected with $selector) 
	 * to be available in client-side scripts. 
	 * @param string $selector
	 * @param array $options
	 * @return Mana_Core_Helper_Js
	 */
	public function options($selector, $options) {
		/* @var $layout Mage_Core_Model_Layout */ $layout = Mage::getSingleton(strtolower('Core/Layout'));
		/* @var $jsBlock Mana_Core_Block_Js */ $jsBlock = $layout->getBlock('m_js');
		$jsBlock->options($selector, $options);
		return $this; 
	}
	public function optionsToHtml($selector, $options) {
		$options = json_encode(array($selector => $options));
		return <<<EOT
<script type="text/javascript"> 
//<![CDATA[
jQuery.options($options);
//]]>
</script> 
EOT;
	}
}