<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method int getBlockId()
 * @method ManaPro_Slider_Model_Cmsblock setBlockId(int $value)
 * @method Mage_Cms_Model_Block getBlock()
 * @method ManaPro_Slider_Model_Cmsblock setBlock(Mage_Cms_Model_Block $value)
 * @method int getPosition()
 * @method ManaPro_Slider_Model_Cmsblock setPosition(int $value)
 * @method ManaPro_Slider_Model_Cmsblock setDisplay(string $value)
 */
class ManaPro_Slider_Model_Cmsblock extends Mana_Db_Model_Object {
    protected function _construct() {
        $this->_init('manapro_slider/cmsblock');
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
        return Mage::getConfig()->getNode('manapro_slider/display/cmsblock/'  . $this->getDisplay());
    }

    /**
     * @param ManaPro_Slider_Block_Slider $parent
     * @return string
     */
    public function getBlockName($parent) {
        return sprintf('%s_%s', $parent->getNameInLayout(), $this->getBlockLocalName());
    }
    public function getBlockLocalName() {
        return sprintf('cmsblock_%s', $this->getBlockId());
    }
}