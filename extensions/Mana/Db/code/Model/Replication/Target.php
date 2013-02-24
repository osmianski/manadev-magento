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
 * PROPERTY METHODS
 * 
 * @method string getEntityName()
 * @method bool hasEntityName(()
 * @method Mana_Db_Model_Replication_Target unsEntityName(()
 * @method Mana_Db_Model_Replication_Target setEntityName(()
 * 
 * @method bool getIsKeyFilterApplied()
 * @method bool hasIsKeyFilterApplied(()
 * @method Mana_Db_Model_Replication_Target unsIsKeyFilterApplied(()
 * @method Mana_Db_Model_Replication_Target setIsKeyFilterApplied(()
 * 
 * @method bool getReplicable()
 * @method bool hasReplicable()
 * @method Mana_Db_Replication_Target unsReplicable()
 * @method Mana_Db_Replication_Target setReplicable()
 * 
 * COLLECTION METHODS
 * 
 * @method array getSourceEntityNames()
 * @method string getSourceEntityName()
 * @method bool hasSourceEntityName()
 * @method Mana_Db_Model_Replication_Target setSourceEntityName()
 * @method Mana_Db_Model_Replication_Target setSourceEntityNames()
 * @method Mana_Db_Model_Replication_Target unsSourceEntityName()
 * 
 * @method array getSavedKeys()
 * @method string getSavedKey()
 * @method bool hasSavedKey()
 * @method Mana_Db_Model_Replication_Target setSavedKey()
 * @method Mana_Db_Model_Replication_Target setSavedKeys()
 * @method Mana_Db_Model_Replication_Target unsSavedKey()
 * 
 * @method array getDeletedKeys()
 * @method string getDeletedKey()
 * @method bool hasDeletedKey()
 * @method Mana_Db_Model_Replication_Target setDeletedKey()
 * @method Mana_Db_Model_Replication_Target setDeletedKeys()
 * @method Mana_Db_Model_Replication_Target unsDeletedKey()
 * 
 * @method array getSelects()
 * @method Varien_Db_Select getSelect()
 * @method bool hasSelect()
 * @method Mana_Db_Model_Replication_Target setSelect()
 * @method Mana_Db_Model_Replication_Target setSelects()
 * @method Mana_Db_Model_Replication_Target unsSelect()
 * 
 * 
 */
class Mana_Db_Model_Replication_Target extends Mana_Core_Model_Object {
	protected $_collections = array(
		'source_entity_names' => array(),
		'saved_keys' => array(),
		'deleted_keys' => array(),
		'selects' => array(),
	);
}