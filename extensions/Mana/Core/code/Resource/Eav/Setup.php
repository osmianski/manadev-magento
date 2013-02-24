<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Base class for setup scripts
 * @author Mana Team
 *
 */
class Mana_Core_Resource_Eav_Setup extends Mage_Eav_Model_Entity_Setup {
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge($data, array(
            'is_key'		   			=> $this->_getValue($attr, 'is_key', 0),
            'is_global'		   			=> $this->_getValue($attr, 'is_global', Mana_Core_Model_Attribute_Scope::_GLOBAL),
        	'has_default'   			=> $this->_getValue($attr, 'has_default', 0),
            'default_model'             => $this->_getValue($attr, 'default_model', ''),
            'default_source'            => $this->_getValue($attr, 'default_source', ''),
            'default_mask'  	        => $this->_getValue($attr, 'default_mask', 0),
            'default_mask_field'        => $this->_getValue($attr, 'default_mask_field', 'default_mask0'),
        ));
        return $data;
    }
	public function updateDefaultMaskFields($entity) {
		/* @var $db  Varien_Db_Adapter_Pdo_Mysql */ $db = $this->getConnection();
		$defaultMaskFields = $db->fetchCol("SELECT DISTINCT m.`default_mask_field` 
			FROM {$this->getTable('m_attribute')} AS m
			INNER JOIN {$this->getTable('eav_attribute')} AS a ON a.attribute_id = m.attribute_id
			INNER JOIN {$this->getTable('eav_entity_type')} AS t ON t.entity_type_id = a.entity_type_id
			WHERE t.`entity_type_code` = '$entity'");
		$existingFields = $db->describeTable($this->getTable($entity));
		foreach ($defaultMaskFields as $defaultMaskField) {
			if (!isset($existingFields[$defaultMaskField])) {
				$this->run("
					ALTER TABLE `{$this->getTable($entity)}` ADD COLUMN ( 
						`$defaultMaskField` int(10) unsigned NOT NULL default '0'
					);
				");
			}
		}
		return $this;
	}
	public function getDefaultEntities() {
		return array();
	}
	public function getEntityExtensions() {
		return array();
	}
	public function installEntities($entities=null) {
		parent::installEntities($entities);

        foreach ($this->getEntityExtensions() as $entityName=>$entity) {
            $frontendPrefix = isset($entity['frontend_prefix']) ? $entity['frontend_prefix'] : '';
            $backendPrefix = isset($entity['backend_prefix']) ? $entity['backend_prefix'] : '';
            $sourcePrefix = isset($entity['source_prefix']) ? $entity['source_prefix'] : '';

            foreach ($entity['attributes'] as $attrCode=>$attr) {
                if (!empty($attr['backend'])) {
                    if ('_'===$attr['backend']) {
                        $attr['backend'] = $backendPrefix;
                    } elseif ('_'===$attr['backend']{0}) {
                        $attr['backend'] = $backendPrefix.$attr['backend'];
                    } else {
                        $attr['backend'] = $attr['backend'];
                    }
                }
                if (!empty($attr['frontend'])) {
                    if ('_'===$attr['frontend']) {
                        $attr['frontend'] = $frontendPrefix;
                    } elseif ('_'===$attr['frontend']{0}) {
                        $attr['frontend'] = $frontendPrefix.$attr['frontend'];
                    } else {
                        $attr['frontend'] = $attr['frontend'];
                    }
                }
                if (!empty($attr['source'])) {
                    if ('_'===$attr['source']) {
                        $attr['source'] = $sourcePrefix;
                    } elseif ('_'===$attr['source']{0}) {
                        $attr['source'] = $sourcePrefix.$attr['source'];
                    } else {
                        $attr['source'] = $attr['source'];
                    }
                }

                $this->addAttribute($entityName, $attrCode, $attr);
            }
        }

        return $this;
	}
    // no changes here: just 1.6 version of this method is incorrect for TEXT values
	public function createEntityTables($baseName, array $options=array())
    {
        $sql = '';

        if (empty($options['no-main'])) {
            $sql = "
DROP TABLE IF EXISTS `{$baseName}`;
CREATE TABLE `{$baseName}` (
`entity_id` int(10) unsigned NOT NULL auto_increment,
`entity_type_id` smallint(8) unsigned NOT NULL default '0',
`attribute_set_id` smallint(5) unsigned NOT NULL default '0',
`increment_id` varchar(50) NOT NULL default '',
`parent_id` int(10) unsigned NULL default '0',
`store_id` smallint(5) unsigned NOT NULL default '0',
`created_at` datetime NOT NULL default '0000-00-00 00:00:00',
`updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
`is_active` tinyint(1) unsigned NOT NULL default '1',
PRIMARY KEY  (`entity_id`),
CONSTRAINT `FK_{$baseName}_type` FOREIGN KEY (`entity_type_id`) REFERENCES `{$this->getTable('eav_entity_type')}` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_{$baseName}_store` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        }

        $types = array(
            'datetime'=>'datetime',
            'decimal'=>'decimal(12,4)',
            'int'=>'int',
            'text'=>'text',
            'varchar'=>'varchar(255)',
        );
        if (!empty($options['types']) && is_array($options['types'])) {
            if ($options['no-default-types']) {
                $types = array();
            }
            $types = array_merge($types, $options['types']);
        }

        foreach ($types as $type=>$fieldType) {
            $sql .= "
DROP TABLE IF EXISTS `{$baseName}_{$type}`;
CREATE TABLE `{$baseName}_{$type}` (
`value_id` int(11) NOT NULL auto_increment,
`entity_type_id` smallint(8) unsigned NOT NULL default '0',
`attribute_id` smallint(5) unsigned NOT NULL default '0',
`store_id` smallint(5) unsigned NOT NULL default '0',
`entity_id` int(10) unsigned NOT NULL default '0',
`value` {$fieldType} NOT NULL,
PRIMARY KEY  (`value_id`),
UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
".($type!=='text' ? "
KEY `value_by_attribute` (`attribute_id`,`value`),
KEY `value_by_entity_type` (`entity_type_id`,`value`),
" : "")."
CONSTRAINT `FK_{$baseName}_{$type}` FOREIGN KEY (`entity_id`) REFERENCES `{$baseName}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_{$baseName}_{$type}_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `{$this->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_{$baseName}_{$type}_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `{$this->getTable('eav_entity_type')}` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_{$baseName}_{$type}_store` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        }

        try {
            $this->_conn->multi_query($sql);
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }
}