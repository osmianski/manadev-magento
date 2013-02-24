<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Wrapper for sources which implement source public interface only partly
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Source_Adapter extends ManaPro_ProductFaces_Model_Source_Abstract {
	protected $_source;
	public function setSource($source) {
		$this->_source = $source;
		return $this;
	}
    protected function _getAllOptions() {
    	return $this->_source->getAllOptions(true, true);
    }
}