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
class ManaPro_Slider_Block_Tab_Navigation extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset = $form->addFieldset('prev_next_fieldset',
            array('legend' => Mage::helper('manapro_slider')->__("'Previous', 'Next' Actions"))
        );

        $fieldset->addField('prev_next', 'select', array(
            'name' => 'prev_next',
            'label' => Mage::helper('manapro_slider')->__('Display As'),
            'title' => Mage::helper('manapro_slider')->__('Display As'),
            'values' => Mage::getSingleton('manapro_slider/source_navigation_prevnext')->getOptionArray(),
        ));

        $fieldset = $form->addFieldset('fast_switch_fieldset',
            array('legend' => Mage::helper('manapro_slider')->__("Fast Switching Actions"))
        );

        $fieldset->addField('fast_switch', 'select', array(
            'name' => 'fast_switch',
            'label' => Mage::helper('manapro_slider')->__('Display As'),
            'title' => Mage::helper('manapro_slider')->__('Display As'),
            'values' => Mage::getSingleton('manapro_slider/source_navigation_switch')->getOptionArray(),
        ));

        $fieldset->addField('fast_switch_position', 'select', array(
            'name' => 'fast_switch_position',
            'label' => Mage::helper('manapro_slider')->__('Position'),
            'title' => Mage::helper('manapro_slider')->__('Position'),
            'values' => Mage::getSingleton('manapro_slider/source_navigation_position')->getOptionArray(),
        ));

        $fieldset->addField('fast_switch_event', 'select', array(
            'name' => 'fast_switch_event',
            'label' => Mage::helper('manapro_slider')->__('Activate On'),
            'title' => Mage::helper('manapro_slider')->__('Activate On'),
            'values' => Mage::getSingleton('manapro_slider/source_navigation_event')->getOptionArray(),
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
        return $this->__('Navigation');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('Navigation');
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
