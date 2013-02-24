<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Obsolete, consider using identical class from Mana_Core
 * @author Mana Team
 *
 */
abstract class ManaPro_ProductFaces_Model_Source_Abstract {
	protected $_options = null;
	 
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = $this->_getAllOptions();
        }
        return $this->_options;
    }

    protected abstract function _getAllOptions();
    
    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

	public function toOptionArray() {
		return $this->getOptionArray();
	}

    protected $_attribute;
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }
    public function getAttribute()
    {
        return $this->_attribute;
    }
}