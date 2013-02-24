<?php
/**
 * @category    Mana
 * @package     Mana_Widget
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Widget_Model_Widget_Instance extends Mage_Widget_Model_Widget_Instance
{
    public function getWidgetConfig()
    {
        if ($this->_widgetConfigXml === null) {
            $this->_widgetConfigXml = Mage::getSingleton('widget/widget')
                ->getXmlElementByType($this->getType());
            if ($this->_widgetConfigXml) {
                $configFile = Mage::getSingleton('core/design_package')->getBaseDir(array(
                    '_area'    => $this->getArea(),
                    '_package' => $this->getPackage(),
                    '_theme'   => $this->getTheme(),
                    '_type'    => 'etc'
                )) . DS . 'widget.xml';
                if (is_readable($configFile)) {
                    $themeWidgetsConfig = new Varien_Simplexml_Config();
                    $themeWidgetsConfig->loadFile($configFile);

                    if ($themeWidgetTypeConfig = $themeWidgetsConfig->getNode($this->_widgetConfigXml->getName())) {
                        $this->_widgetConfigXml->extend($themeWidgetTypeConfig);
                    }
                }

                $additionalThemeWidgetsConfig = new Varien_Simplexml_Config();
                $additionalThemeWidgetsConfig->loadString('<?xml version="1.0"?><widgets></widgets>');
                Mage::getConfig()->loadModulesConfiguration('widget_theme.xml', $additionalThemeWidgetsConfig);
                if ($additionalThemeWidgetsTypeConfig = $additionalThemeWidgetsConfig->getNode($this->_widgetConfigXml->getName())) {
                    $this->_widgetConfigXml->extend($additionalThemeWidgetsTypeConfig);
                }
            }
        }
        return $this->_widgetConfigXml;
    }
}