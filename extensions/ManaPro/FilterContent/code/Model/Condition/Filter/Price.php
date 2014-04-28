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
class ManaPro_FilterContent_Model_Condition_Filter_Price extends ManaPro_FilterContent_Model_Condition_Filter {
    #region Operator
    public function loadOperatorOptions()
    {
        $this->setData('operator_option', array(
            'f==' => $this->contentHelper()->__('lower bound is'),
            'f!=' => $this->contentHelper()->__('lower bound is not'),
            'f>=' => $this->contentHelper()->__('lower bound equals or greater than'),
            'f<=' => $this->contentHelper()->__('lower bound equals or less than'),
            'f>' => $this->contentHelper()->__('lower bound greater than'),
            'f<' => $this->contentHelper()->__('lower bound less than'),
            't=='  => $this->contentHelper()->__('upper bound is'),
            't!='  => $this->contentHelper()->__('upper bound is not'),
            't>='  => $this->contentHelper()->__('upper bound equals or greater than'),
            't<='  => $this->contentHelper()->__('upper bound equals or less than'),
            't>'   => $this->contentHelper()->__('upper bound greater than'),
            't<'   => $this->contentHelper()->__('upper bound less than'),
        ));
        return $this;
    }
    #endregion
    #region Value
    public function getValueElementType() {
        return 'text';
    }
    #endregion
}