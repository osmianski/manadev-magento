<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Slider_Block_Tab_Effects extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    /**
     * Prepare Widget Options Form and values according to specified type
     *
     * widget_type must be set in data before
     * widget_values may be set before to render element values
     */
    protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $form
            ->setUseContainer(false)
            ->setFieldContainerIdPrefix('manapro_slider_');
        $this->setForm($form);

        #region Effects
        $fieldset = $form->addFieldset('effects_fieldset',
            array('legend' => Mage::helper('manapro_slider')->__('Effects'))
        );

        $fieldset->addField('effect_rotation_interval', 'text', array(
            'name' => 'effect_rotation_interval',
            'label' => Mage::helper('manapro_slider')->__('Rotate Products Each'),
            'title' => Mage::helper('manapro_slider')->__('Rotate Products Each'),
            'note' => Mage::helper('manapro_slider')->__('millisecond(s)'),
        ));

        $fieldset->addField('effect_hide', 'select', array(
            'name' => 'effect_hide',
            'label' => Mage::helper('manapro_slider')->__('Hide Effect'),
            'title' => Mage::helper('manapro_slider')->__('Hide Effect'),
            'values' => Mage::getSingleton('manapro_slider/source_effect_hide')->getOptionArray(),
        ));

        $fieldset->addField('effect_hide_random', 'multiselect', array(
            'name' => 'effect_hide_random',
            'label' => Mage::helper('manapro_slider')->__('Use These Hide Effects in Random Sequence'),
            'title' => Mage::helper('manapro_slider')->__('Use These Hide Effects in Random Sequence'),
            'values' => Mage::getSingleton('manapro_slider/source_effect_random')->getAllOptions(),
        ));

        $fieldset->addField('effect_hide_blind_direction', 'select', array(
            'name' => 'effect_hide_blind_direction',
            'label' => Mage::helper('manapro_slider')->__('Blind Effect Direction'),
            'title' => Mage::helper('manapro_slider')->__('Blind Effect Direction'),
            'values' => Mage::getSingleton('manapro_slider/source_direction')->getOptionArray(),
        ));

        $fieldset->addField('effect_hide_clip_direction', 'select', array(
            'name' => 'effect_hide_clip_direction',
            'label' => Mage::helper('manapro_slider')->__('Clip Effect Direction'),
            'title' => Mage::helper('manapro_slider')->__('Clip Effect Direction'),
            'values' => Mage::getSingleton('manapro_slider/source_direction')->getOptionArray(),
        ));

        $fieldset->addField('effect_hide_drop_direction', 'select', array(
            'name' => 'effect_hide_drop_direction',
            'label' => Mage::helper('manapro_slider')->__('Drop Effect Direction'),
            'title' => Mage::helper('manapro_slider')->__('Drop Effect Direction'),
            'values' => Mage::getSingleton('manapro_slider/source_side')->getOptionArray(),
        ));

        $fieldset->addField('effect_hide_interval', 'text', array(
            'name' => 'effect_hide_interval',
            'label' => Mage::helper('manapro_slider')->__('Play Hide Effect For'),
            'title' => Mage::helper('manapro_slider')->__('Play Hide Effect For'),
            'note' => Mage::helper('manapro_slider')->__('millisecond(s)'),
        ));

        $fieldset->addField('effect_show', 'select', array(
            'name' => 'effect_show',
            'label' => Mage::helper('manapro_slider')->__('Show Effect'),
            'title' => Mage::helper('manapro_slider')->__('Show Effect'),
            'values' => Mage::getSingleton('manapro_slider/source_effect_show')->getOptionArray(),
        ));

        $fieldset->addField('effect_show_random', 'multiselect', array(
            'name' => 'effect_show_random',
            'label' => Mage::helper('manapro_slider')->__('Use These Show Effects in Random Sequence'),
            'title' => Mage::helper('manapro_slider')->__('Use These Show Effects in Random Sequence'),
            'values' => Mage::getSingleton('manapro_slider/source_effect_random')->getAllOptions(),
        ));

        $fieldset->addField('effect_show_blind_direction', 'select', array(
            'name' => 'effect_show_blind_direction',
            'label' => Mage::helper('manapro_slider')->__('Blind Effect Direction'),
            'title' => Mage::helper('manapro_slider')->__('Blind Effect Direction'),
            'values' => Mage::getSingleton('manapro_slider/source_direction')->getOptionArray(),
        ));

        $fieldset->addField('effect_show_clip_direction', 'select', array(
            'name' => 'effect_show_clip_direction',
            'label' => Mage::helper('manapro_slider')->__('Clip Effect Direction'),
            'title' => Mage::helper('manapro_slider')->__('Clip Effect Direction'),
            'values' => Mage::getSingleton('manapro_slider/source_direction')->getOptionArray(),
        ));

        $fieldset->addField('effect_show_drop_direction', 'select', array(
            'name' => 'effect_show_drop_direction',
            'label' => Mage::helper('manapro_slider')->__('Drop Effect Direction'),
            'title' => Mage::helper('manapro_slider')->__('Drop Effect Direction'),
            'values' => Mage::getSingleton('manapro_slider/source_side')->getOptionArray(),
        ));

        $fieldset->addField('effect_show_interval', 'text', array(
            'name' => 'effect_show_interval',
            'label' => Mage::helper('manapro_slider')->__('Play Show Effect For'),
            'title' => Mage::helper('manapro_slider')->__('Play Show Effect For'),
            'note' => Mage::helper('manapro_slider')->__('millisecond(s)'),
        ));
        #endregion

        Mage::helper('manapro_slider')->prepareFormValues($form);

        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return $this->__('Effects');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('Effects');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab() {
        return $this->getWidgetInstance()->isCompleteToCreate() &&
                $this->getWidgetInstance()->getType() == 'manapro_slider/slider';
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden() {
        return false;
    }

    /**
     * Getter
     *
     * @return age_Widget_Model_Widget_Instance
     */
    public function getWidgetInstance() {
        return Mage::registry('current_widget_instance');
    }

    /**
     * Prepare block children and data.
     * Set widget type and widget parameters if available
     *
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Properties
     */
    protected function _prepareLayout() {
        $this->setWidgetType($this->getWidgetInstance()->getType())
                ->setWidgetValues($this->getWidgetInstance()->getWidgetParameters());
        return parent::_prepareLayout();
    }
}
