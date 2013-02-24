<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method int getProductId()
 * @method ManaPro_Slider_Model_Product setProductId(int $value)
 * @method Mage_Catalog_Model_Product getProduct()
 * @method ManaPro_Slider_Model_Product setProduct(Mage_Catalog_Model_Product $value)
 * @method int getPosition()
 * @method ManaPro_Slider_Model_Product setPosition(int $value)
 * @method ManaPro_Slider_Model_Product setDisplay(string $value)
 */
class ManaPro_Slider_Model_Product extends Mana_Db_Model_Object {
    protected function _construct() {
        $this->_init('manapro_slider/product');
    }
    protected function _validate($result) {
        $t = Mage::helper('manapro_slider');
        // add validation logic here
    }

    public function getDisplay() {
        if (!($result = parent::getDisplay())) {
            $result = 'default';
        }
        return $result;
    }

    /**
     * @return Mage_Core_Model_Config_Element
     */
    public function getDisplayOptions() {
        return Mage::getConfig()->getNode('manapro_slider/display/product/'  . $this->getDisplay());
    }

    /**
     * @param ManaPro_Slider_Block_Slider $parent
     * @return string
     */
    public function getBlockName($parent) {
        return sprintf('%s_%s', $parent->getNameInLayout(), $this->getBlockLocalName());
    }
    public function getBlockLocalName() {
        return sprintf('product_%s', $this->getProductId());
    }
}