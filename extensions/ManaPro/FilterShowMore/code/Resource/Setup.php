<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterShowMore
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Adds columns to filter options
 * @author Mana Team
 *
 */
class ManaPro_FilterShowMore_Resource_Setup extends Mana_Core_Resource_Eav_Setup {
	public function getEntityExtensions() {
		return array('m_filter' => array(
			'attributes' => array(
				'show_more_item_count' => array(
					// storage
                    'type'              => 'int',
                    'default'           => '',
					'is_global'			=> Mana_Core_Model_Attribute_Scope::_STORE, 
					
					// editing
					'label'             => 'Item Limit',
					'note'				=> "In case filter has more than specified number of items, only first items are displayed, as well as 'Show More' and 'Show Less' actions.",
					'input'				=> 'text', 
					'required'          => true,
		
					// default chain
					'has_default'		=> true,
					'default_model'		=> 'mana_core/config_default',
					'default_source'	=> 'mana_filters/display/show_more_item_count',
					'default_mask'		=> 0x0000000000000008,
				),
			), 
		));
	}
}