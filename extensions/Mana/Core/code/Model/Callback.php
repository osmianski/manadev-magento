<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 * PROPERTY METHODS
 * 
 * 
 * @method string | object getTarget()
 * @method bool hasTarget()
 * @method Mana_Core_Model_Callback unsTarget()
 * @method Mana_Core_Model_Callback setTarget()
 * 
 * 
 * @method string getMethod()
 * @method bool hasMethod()
 * @method Mana_Core_Model_Callback unsMethod()
 * @method Mana_Core_Model_Callback setMethod()
 * 
 */
class Mana_Core_Model_Callback extends Mana_Core_Model_Object {
	public function callArray($args) {
		if ($this->hasTarget()) {
			return call_user_func_array(array($this->getTarget(), $this->getMethod()), $args);
		}
		else {
			return call_user_func_array($this->getMethod(), $args);
		}
	}
	public function call() {
		return $this->callArray(func_get_args());	
	}
}