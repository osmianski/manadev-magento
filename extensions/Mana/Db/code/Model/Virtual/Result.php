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
 * @method array getColumns()
 * @method string getColumn()
 * @method Mana_Db_Model_Virtual_Result unsColumns()
 * @method Mana_Db_Model_Virtual_Result unsColumn()
 * @method Mana_Db_Model_Virtual_Result addColumn()
 * @method Mana_Db_Model_Virtual_Result setColumns()
 * 
 */
class Mana_Db_Model_Virtual_Result extends Mana_Core_Model_Object {
	protected $_arrays = array(
		'columns' => array(),
	);
}