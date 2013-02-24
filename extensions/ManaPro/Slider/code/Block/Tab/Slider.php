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
class ManaPro_Slider_Block_Tab_Slider extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset = $form->addFieldset('slider_fieldset',
            array('legend' => Mage::helper('manapro_slider')->__('Slider'))
        );

        $fieldset->addField('height', 'text', array(
            'name' => 'height',
            'label' => Mage::helper('manapro_slider')->__('Height'),
            'title' => Mage::helper('manapro_slider')->__('Height'),
            'note' => Mage::helper('manapro_slider')->__('pixels'),
        ));

        $fieldset->addField('css_class', 'text', array(
            'name' => 'css_class',
            'label' => Mage::helper('manapro_slider')->__('CSS Classes'),
            'title' => Mage::helper('manapro_slider')->__('CSS Classes'),
            'note' => Mage::helper('manapro_slider')->__('separate CSS classes with spaces'),
        ));

        $fieldset->addField('auto_start', 'select', array(
            'name' => 'auto_start',
            'label' => Mage::helper('manapro_slider')->__('Auto Start'),
            'title' => Mage::helper('manapro_slider')->__('Auto Start'),
            'values' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
        ));

        $fieldset->addField('random_start', 'select', array(
            'name' => 'random_start',
            'label' => Mage::helper('manapro_slider')->__('Random Start'),
            'title' => Mage::helper('manapro_slider')->__('Random Start'),
            'values' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
        ));

        $fieldset->addField('shuffle', 'select', array(
            'name' => 'shuffle',
            'label' => Mage::helper('manapro_slider')->__('Shuffle'),
            'title' => Mage::helper('manapro_slider')->__('Shuffle'),
            'values' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
        ));

        Mage::helper('manapro_slider')->prepareFormValues($form);

        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return $this->__('Slider');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('Slider');
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
