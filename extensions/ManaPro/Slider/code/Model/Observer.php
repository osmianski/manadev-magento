<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_Slider_Model_Observer {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_predispatch_adminhtml_widget_instance_save")
     * @param Varien_Event_Observer $observer
     */
    public function registerWidgetInstanceSaveAction($observer) {
        Mage::register('m_widget_instance_is_being_saved', 1);
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "model_save_before")
     * @param Varien_Event_Observer $observer
     */
    public function beforeWidgetInstanceSave($observer) {
        /* @var $object Mage_Widget_Model_Widget_Instance */ $object = $observer->getEvent()->getObject();
        /* @var $helper ManaPro_Slider_Helper_Data */ $helper = Mage::helper(strtolower('ManaPro_Slider'));

        if (Mage::registry('m_widget_instance_is_being_saved') && $object->getType() == 'manapro_slider/slider') {
            if (!is_array($object->getData('widget_parameters'))) {
                $object->setData('widget_parameters', unserialize($object->getData('widget_parameters')));
            }
            $widgetParameters = $object->getData('widget_parameters');
            $helper->prepareSettingsForSave($widgetParameters);

            $object->setData('widget_parameters', serialize($widgetParameters));

        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_predispatch_adminhtml_widget_buildWidget")
     * @param Varien_Event_Observer $observer
     */
    public function prepareInlineWidgetForSave($observer) {
        /* @var $helper ManaPro_Slider_Helper_Data */
        $helper = Mage::helper(strtolower('ManaPro_Slider'));

        if (Mage::app()->getRequest()->getPost('widget_type') == 'manapro_slider/slider') {
            $widgetParameters = Mage::app()->getRequest()->getPost('parameters', array());
            $helper->prepareSettingsForSave($widgetParameters);
            $_POST['parameters'] = $widgetParameters;
        }

    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "core_block_abstract_to_html_before")
     * @param Varien_Event_Observer $observer
     */
    public function removeWidgetOptionsTab($observer) {
        /* @var $block Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Properties */
        $block = $observer->getEvent()->getBlock();

        // INSERT HERE: event handler code
        if ($block instanceof Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tabs
            && Mage::registry('current_widget_instance')->getType() == 'manapro_slider/slider')
        {
            $block->removeTab('properties_section');
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_generate_blocks_before")
     * @param Varien_Event_Observer $observer
     */
    public function initializeInlineWidgetInstance($observer) {
        /* @var $action Mage_Core_Controller_Varien_Action */ $action = $observer->getEvent()->getAction();
        /* @var $layout Mage_Core_Model_Layout */ $layout = $observer->getEvent()->getLayout();

        if (strtolower($action->getFullActionName()) == 'adminhtml_widget_loadoptions') {
            /* @var $widgetInstance Mage_Widget_Model_Widget_Instance */
            $widgetInstance = Mage::getModel('widget/widget_instance');
            if ($param = $action->getRequest()->getParam('widget')) {
                if ($param = json_decode($param, true)) {

                    $widgetInstance
                        ->setPackageTheme('default_default')
                        ->setType($param['widget_type'])
                        ->setWidgetParameters($param['values']);
                }
            }
            Mage::register('current_widget_instance', $widgetInstance);
        }
    }
}