<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Model_Source_Design extends Mana_Core_Model_Source_Abstract
{
    public function getIsFullLabel() {
        return false;
    }

    protected function _getAllOptions()
    {
        $design = Mage::getModel('core/design_package')->getThemeList();
        $options = array();
        foreach ($design as $package => $themes){
            $packageOption = array('label' => $package);
            $themeOptions = array();
            foreach ($themes as $theme) {
                $themeOptions[] = array(
                    'label' => ($this->getIsFullLabel() ? $package . ' / ' : '') . $theme,
                    'value' => $package . '/' . $theme
                );
            }
            $packageOption['value'] = $themeOptions;
            $options[] = $packageOption;
        }
        array_unshift($options, array(
            'value'=>'',
            'label'=>Mage::helper('core')->__('-- Please Select --'))
        );

        return $options;
    }
}
