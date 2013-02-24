<?php
/**
 * @category    Mana
 * @package     Mana_Widget
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**widget_after.xml
 * @author Mana Team
 *
 */
class Mana_Widget_Model_Widget extends Mage_Widget_Model_Widget
{
    public function getXmlConfig()
    {
        $cachedXml = Mage::app()->loadCache('widget_config');
        if ($cachedXml) {
            $xmlConfig = new Varien_Simplexml_Config($cachedXml);
        } else {
            $config = new Varien_Simplexml_Config();
            $config->loadString('<?xml version="1.0"?><widgets></widgets>');
            Mage::getConfig()->loadModulesConfiguration('widget.xml', $config);

            $afterConfig = new Varien_Simplexml_Config();
            $afterConfig->loadString('<?xml version="1.0"?><widgets></widgets>');
            Mage::getConfig()->loadModulesConfiguration('widget_after.xml', $afterConfig);
            foreach ($afterConfig->getNode()->children() as $widgetName => $widgetXml) {
                $originalWidgetXml = $config->getNode($widgetName);
                if (isset($widgetXml['type']) || isset($originalWidgetXml['type'])) {
                    $originalWidgetXml->extend($widgetXml, true);
                }
            }

            $xmlConfig = $config;
            if (Mage::app()->useCache('config')) {
                Mage::app()->saveCache($config->getXmlString(), 'widget_config',
                    array(Mage_Core_Model_Config::CACHE_TAG));
            }
        }
        return $xmlConfig;
    }
}