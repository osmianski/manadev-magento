<?php
/** 
 * @category    Mana
 * @package     ManaSlider_Tabbed
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method ManaSlider_Tabbed_Model_Tab[] getTabs()
 * @method int getHeight()
 * @method Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection getDataSource()
 */
class ManaSlider_Tabbed_Block_Slider extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    protected function _prepareLayout() {
        $this->layoutHelper()->delayPrepareLayout($this);
        return parent::_prepareLayout();
    }

    public function delayedPrepareLayout() {
        if (($xml = trim($this->getData('xml_text'))) && ($xml = simplexml_load_string("<config>$xml</config>"))) {
            foreach ($xml->children() as $propertyXml) {
                /* @var $propertyXml SimpleXmlElement */
                if ($propertyXml->getName() == 'tabs') {
                    foreach ($propertyXml->children() as $tabXml) {
                        /* @var $tabXml SimpleXmlElement */

                        $data = array();
                        foreach ($tabXml->children() as $tabPropertyXml) {
                            /* @var $tabPropertyXml SimpleXmlElement */
                            $data[$tabPropertyXml->getName()] = (string)$tabPropertyXml;
                        }

                        if (($dataSource = $data['data_source']) &&
                            ($dataSourceXml = Mage::getConfig()->getNode("manaslider_tabbed/data_sources/$dataSource")) &&
                            !empty($dataSourceXml->block))
                        {
                            unset($data['data_source']);
                            $block = $this->getLayout()->createBlock((string)$dataSourceXml->block,
                                $this->getNameInLayout().'.tab.'. $tabXml->getName(), $data);

                            $this->append($block, 'tab.' . $tabXml->getName());
                            $block->addToParentGroup('tabs');

                            list($sourceHelper, $method) = explode('::', (string)$dataSourceXml->source_helper);
                            $sourceHelper = Mage::helper($sourceHelper);
                            $block->setData('data_source', $sourceHelper->$method($block));
                        }
                    }
                }
                else {
                    $this->setData($propertyXml->getName(), (string)$propertyXml);
                }
            }
        }

        $this->setTemplate('manaslider/tabbed/tabs.phtml');
    }

    protected function _toHtml() {
        /* @var $tabs Mage_Core_Block_Abstract[] */
        $tabs = $this->getChildGroup('tabs');
        $count = count($tabs);
        if ($count > 1) {
            parent::_toHtml();
        }
        elseif ($count == 1) {
            foreach ($tabs as $tab) {
                return $tab->toHtml();
            }
        }
        else {
            return '';
        }
    }
    #region Dependencies
    /**
     * @return Mana_Core_Helper_Layout
     */
    public function layoutHelper() {
        return Mage::helper('mana_core/layout');
    }

    /**
     * @return Mage_Core_Model_Layout
     */
    public function getLayout() {
        return Mage::getSingleton('core/layout');
    }

    public function jsHelper() {
        return Mage::helper('mana_core/js');
    }

    #endregion
}