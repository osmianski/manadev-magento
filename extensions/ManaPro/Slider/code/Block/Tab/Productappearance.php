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
class ManaPro_Slider_Block_Tab_Productappearance extends Mage_Adminhtml_Block_Widget_Form
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

        #region Product appearance
        $fieldset = $form->addFieldset('product_fields_fieldset',
            array('legend' => Mage::helper('manapro_slider')->__('Product Fields'))
        );

        $fieldset->addField('product_field_name', 'select', array(
            'name' => 'product_field_name',
            'label' => Mage::helper('manapro_slider')->__('Show Product Name'),
            'title' => Mage::helper('manapro_slider')->__('Show Product Name'),
            'values' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
        ));

        $fieldset->addField('product_field_description', 'select', array(
            'name' => 'product_field_description',
            'label' => Mage::helper('manapro_slider')->__('Show Product Description'),
            'title' => Mage::helper('manapro_slider')->__('Show Product Description'),
            'values' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
        ));

        $fieldset->addField('product_field_price', 'select', array(
            'name' => 'product_field_price',
            'label' => Mage::helper('manapro_slider')->__('Show Product Price'),
            'title' => Mage::helper('manapro_slider')->__('Show Product Price'),
            'values' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
        ));

        $fieldset->addField('product_field_cart', 'select', array(
            'name' => 'product_field_cart',
            'label' => Mage::helper('manapro_slider')->__("Show 'Add to Cart' Button"),
            'title' => Mage::helper('manapro_slider')->__("Show 'Add to Cart' Button"),
            'values' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
        ));

        $fieldset->addField('product_field_links', 'select', array(
            'name' => 'product_field_links',
            'label' => Mage::helper('manapro_slider')->__("Show Other 'Add to ...' Links"),
            'title' => Mage::helper('manapro_slider')->__("Show Other 'Add to ...' Links"),
            'values' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
        ));

        $fieldset->addField('product_field_rating', 'select', array(
            'name' => 'product_field_rating',
            'label' => Mage::helper('manapro_slider')->__('Show Product Rating'),
            'title' => Mage::helper('manapro_slider')->__('Show Product Rating'),
            'values' => Mage::getSingleton('mana_core/source_yesno')->getOptionArray(),
        ));

        $fieldset = $form->addFieldset('product_text_fieldset',
            array('legend' => Mage::helper('manapro_slider')->__('Product Text Position and Size'))
        );

        $fieldset->addField('product_text_left', 'text', array(
            'name' => 'product_text_left',
            'label' => Mage::helper('manapro_slider')->__('Left'),
            'title' => Mage::helper('manapro_slider')->__('Left'),
            'note' => Mage::helper('manapro_slider')->__('pixels'),
        ));

        $fieldset->addField('product_text_top', 'text', array(
            'name' => 'product_text_top',
            'label' => Mage::helper('manapro_slider')->__('Top'),
            'title' => Mage::helper('manapro_slider')->__('Top'),
            'note' => Mage::helper('manapro_slider')->__('pixels'),
        ));

        $fieldset->addField('product_text_width', 'text', array(
            'name' => 'product_text_width',
            'label' => Mage::helper('manapro_slider')->__('Width'),
            'title' => Mage::helper('manapro_slider')->__('Width'),
            'note' => Mage::helper('manapro_slider')->__('pixels'),
        ));

        $fieldset->addField('product_text_height', 'text', array(
            'name' => 'product_text_height',
            'label' => Mage::helper('manapro_slider')->__('Height'),
            'title' => Mage::helper('manapro_slider')->__('Height'),
            'note' => Mage::helper('manapro_slider')->__('pixels'),
        ));

        $fieldset = $form->addFieldset('product_image_fieldset',
            array('legend' => Mage::helper('manapro_slider')->__('Product Image Position and Size'))
        );

        $fieldset->addField('product_image_left', 'text', array(
            'name' => 'product_image_left',
            'label' => Mage::helper('manapro_slider')->__('Left'),
            'title' => Mage::helper('manapro_slider')->__('Left'),
            'note' => Mage::helper('manapro_slider')->__('pixels'),
        ));

        $fieldset->addField('product_image_top', 'text', array(
            'name' => 'product_image_top',
            'label' => Mage::helper('manapro_slider')->__('Top'),
            'title' => Mage::helper('manapro_slider')->__('Top'),
            'note' => Mage::helper('manapro_slider')->__('pixels'),
        ));

        $fieldset->addField('product_image_width', 'text', array(
            'name' => 'product_image_width',
            'label' => Mage::helper('manapro_slider')->__('Width'),
            'title' => Mage::helper('manapro_slider')->__('Width'),
            'note' => Mage::helper('manapro_slider')->__('pixels'),
        ));

        $fieldset->addField('product_image_height', 'text', array(
            'name' => 'product_image_height',
            'label' => Mage::helper('manapro_slider')->__('Height'),
            'title' => Mage::helper('manapro_slider')->__('Height'),
            'note' => Mage::helper('manapro_slider')->__('pixels'),
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
        return $this->__('Product Appearance');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('Product Appearance');
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
