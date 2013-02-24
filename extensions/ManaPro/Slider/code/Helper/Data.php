<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_Slider module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_Slider_Helper_Data extends Mage_Core_Helper_Abstract {
    protected static $_defaultSettings = array(
        'product_field_name' => 1,
        'product_field_description' => 1,
        'product_field_price' => 1,
        'product_field_cart' => 1,
        'product_field_links' => 1,
        'product_field_rating' => 1,

        'effect_rotation_interval' => 5000,
        'effect_hide' => 'fade',
        'effect_hide_random' => '',
        'effect_hide_blind_direction' => 'vertical',
        'effect_hide_clip_direction' => 'vertical',
        'effect_hide_drop_direction' => 'left',
        'effect_hide_interval' => 1000,
        'effect_show' => 'none',
        'effect_show_random' => '',
        'effect_show_blind_direction' => 'vertical',
        'effect_show_clip_direction' => 'vertical',
        'effect_show_drop_direction' => 'left',
        'effect_show_interval' => 1000,

        'prev_next' => 'none',
        'fast_switch' => 'numbers',
        'fast_switch_position' => 'bottom',
        'fast_switch_event' => 'click',

        'height' => 160,
        'css_class' => '',
        'auto_start' => 1,
        'random_start' => 0,
        'shuffle' => 0,

        'product_text_left' => 10,
        'product_text_top' => 10,
        'product_text_width' => 400,
        'product_text_height' => 140,

        'product_image_left' => 420,
        'product_image_top' => 10,
        'product_image_width' => 140,
        'product_image_height' => 140,

    );
    public function getDefaultSettings() {
        return self::$_defaultSettings;
    }

    public function getWidgetInstance() {
        return Mage::registry('current_widget_instance');
    }

    public function prepareFormValues($form) {
        $object = $this->getWidgetInstance();
        if (!is_array($object->getData('widget_parameters'))) {
            $widgetParameters = unserialize($object->getData('widget_parameters'));
        } else {
            $widgetParameters = $object->getData('widget_parameters');
        }
        if (empty($widgetParameters)) {
            $values = array();
            foreach (Mage::helper('manapro_slider')->getDefaultSettings() as $field => $defaultValue) {
                $values[$field] = isset($widgetParameters[$field]) ? $widgetParameters[$field] : $defaultValue;
            }
            $widgetParameters = $values;
        }

        $form->addValues($widgetParameters);
    }
    public function prepareSettingsForSave(&$params, $request = null) {
        if (!$request) {
            $request = Mage::app()->getRequest();
        }
        foreach (array_keys(Mage::helper('manapro_slider')->getDefaultSettings()) as $field) {
            $params[$field] = $request->getParam($field);
        }

        if (($edit = $request->getParam('mSliderProductGrid_table'))) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::helper('mana_admin')->processPendingEdits('manapro_slider/product', $edit);

            /* @var $collection ManaPro_Slider_Resource_Product_Collection */
            $collection = Mage::getResourceModel('manapro_slider/product_collection');
            $collection->setEditFilter($edit);
            $products = array();
            foreach ($collection as $product) {
                /* @var $product ManaPro_Slider_Model_Product */
                $productData = $product->getData();
                foreach (array('id', 'edit_session_id', 'edit_status', 'edit_massaction') as $key) {
                    if (isset($productData[$key])) {
                        unset($productData[$key]);
                    }
                }
                $products[] = $productData;
            }
            $params['products_json'] = htmlspecialchars(json_encode($products));
        }

        if (($edit = $request->getParam('mSliderCmsblockGrid_table'))) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::helper('mana_admin')->processPendingEdits('manapro_slider/cmsblock', $edit);

            /* @var $collection ManaPro_Slider_Resource_Cmsblock_Collection */
            $collection = Mage::getResourceModel('manapro_slider/cmsblock_collection');
            $collection->setEditFilter($edit);
            $blocks = array();
            foreach ($collection as $block) {
                /* @var $block ManaPro_Slider_Model_Cmsblock */
                $blockData = $block->getData();
                foreach (array('id', 'edit_session_id', 'edit_status', 'edit_massaction') as $key) {
                    if (isset($blockData[$key])) {
                        unset($blockData[$key]);
                    }
                }
                $blocks[] = $blockData;
            }
            $params['cmsblocks_json'] = htmlspecialchars(json_encode($blocks));
        }

        if (($edit = $request->getParam('mSliderHtmlblockGrid_table'))) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::helper('mana_admin')->processPendingEdits('manapro_slider/htmlblock', $edit);

            /* @var $collection ManaPro_Slider_Resource_Htmlblock_Collection */
            $collection = Mage::getResourceModel('manapro_slider/htmlblock_collection');
            $collection->setEditFilter($edit);
            $blocks = array();
            foreach ($collection as $block) {
                /* @var $block ManaPro_Slider_Model_Htmlblock */
                $blockData = $block->getData();
                foreach (array('id', 'edit_session_id', 'edit_status', 'edit_massaction') as $key) {
                    if (isset($blockData[$key])) {
                        unset($blockData[$key]);
                    }
                }
                $blocks[] = $blockData;
            }
            $params['htmlblocks_json'] = htmlspecialchars(json_encode($blocks));
        }
    }
}