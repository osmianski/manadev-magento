<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 * ARRAY METHODS
 * 
 * @method array getErrors()
 * @method string getError()
 * @method Mana_Db_Model_Validation unsErrors()
 * @method Mana_Db_Model_Validation unsError()
 * @method Mana_Db_Model_Validation addError()
 * @method Mana_Db_Model_Validation setErrors()
 * 
 */
class Mana_Db_Model_Validation extends Mana_Core_Model_Object {
	protected $_arrays = array(
		'errors' => array(),
	);
} 