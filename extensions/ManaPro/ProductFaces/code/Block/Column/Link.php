<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Wraps content of the column with specified formatting if product exists
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Block_Column_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
	public function _getValue(Varien_Object $row) {
		if (is_numeric($row['entity_id'])) {
			return parent::_getValue($row);
		}
		else {
			$data = $row->getData($this->getColumn()->getIndex());
            $string = is_null($data) ? $this->getColumn()->getDefault() : $data;
            return $this->escapeHtml($string);
		}
	}
}