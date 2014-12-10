<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Model_Condition_Filter_Attribute extends ManaPro_FilterContent_Model_Condition_Filter {
    #region Operator
    public function loadOperatorOptions()
    {
        $this->setData('operator_option', array(
            '{}'  => $this->contentHelper()->__('contains all of these'),
            '!{}' => $this->contentHelper()->__('does not contain any of these'),
            '()'  => $this->contentHelper()->__('contains at least one of these'),
            '!()' => $this->contentHelper()->__('does not contain at least one of these')
        ));
        return $this;
    }
    #endregion
    #region Value
    public function getValueElementType() {
        return 'multiselect';
    }

    public function loadValueOptions()
    {
        $attributeObject = $this->getAttributeObject();
        if ($attributeObject->usesSource()) {
            if ($attributeObject->getFrontendInput() == 'multiselect') {
                $addEmptyOption = false;
            } else {
                $addEmptyOption = true;
            }
            $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            $this->setData('value_option', $this->coreHelper()->getOptionArray($selectOptions));
        }
        else {
            $this->setData('value_option', array());
        }
        return $this;
    }
    #endregion
}