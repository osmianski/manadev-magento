<?php
/**
 * @category    Mana
 * @package     M_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class M_Theme_Block_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar {
    protected function _getCustomListModes() {
        /* @var $t M_Theme_Helper_Data */
        $t = Mage::helper(strtolower('M_Theme'));
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        $config = $t->getConfig()->getNode();
        if (isset($config->catalog->product->list_modes)) {
            $listModes = array();
            foreach ($core->getSortedXmlChildren($config->catalog->product, 'list_modes') as $listMode) {
                $listModes[] = array(
                    'value' => $listMode->getName(),
                    'label' => (string)$listMode->label
                );
            }
            return $listModes;
        }
        else {
            return false;
        }
    }
    protected function _getConfigListMode() {
        /* @var $t M_Theme_Helper_Data */
        $t = Mage::helper(strtolower('M_Theme'));

        $config = $t->getConfig()->getNode();
        if (isset($config->catalog->product->list_mode_config)) {
            $key = (string)$config->catalog->product->list_mode_config;
        }
        else {
            $key = 'catalog/frontend/list_mode';
        }
        return Mage::getStoreConfig($key);
    }
    protected function _construct() {
        if ($customListModes = $this->_getCustomListModes()) {
            if ($this->hasData('template')) {
                $this->setTemplate($this->getData('template'));
            }

            $this->_orderField = Mage::getStoreConfig(
                Mage_Catalog_Model_Config::XML_PATH_LIST_DEFAULT_SORT_BY
            );

            $this->_availableOrder = $this->_getConfig()->getAttributeUsedForSortByArray();

            $possibleModes = array();
            foreach ($customListModes as $customListMode) {
                $possibleModes[$customListMode['value']] = $customListMode['label'];
            }
            $this->_excludeTwoColumnModeIfNotEnoughSpace($possibleModes);

            $modes = array();
            foreach (explode('-', $this->_getConfigListMode()) as $mode) {
                if (isset($possibleModes[$mode])) {
                    $modes[$mode] = $possibleModes[$mode];
                }
            }
            $this->_availableMode = $modes;

            $this->setTemplate('catalog/product/list/toolbar.phtml');
        }
        else {
            parent::_construct();
        }
    }
    protected function _excludeTwoColumnModeIfNotEnoughSpace(&$modes) {
        if (isset($modes['two_column'])) {
            $category = Mage::getSingleton('catalog/layer')->getCurrentCategory();
            $pageLayout = $category->getPageLayout();
            $pageTemplate = '';
            if (!$pageLayout) {
                /* @var $layout Mage_Core_Model_Layout */ $layout = Mage::getSingleton('core/layout');
                if ($page = $layout->getBlock('root')) {
                    $pageTemplate = $page->getTemplate();
                }
            }
            if ($pageLayout == 'three_columns' || $pageTemplate == 'page/3columns.phtml') {
                unset($modes['two_column']);
            }
        }
    }
    public function getDefaultPerPageValue() {
        if ($this->_getCustomListModes()) {
            /* @var $t M_Theme_Helper_Data */
            $t = Mage::helper(strtolower('M_Theme'));

            $config = $t->getConfig()->getNode();
            $mode = $this->getCurrentMode();
            if (isset($config->catalog->product->list_modes->$mode)) {
                $configKey = (string)$config->catalog->product->list_modes->$mode->default_per_page_config;
                $blockField = (string)$config->catalog->product->list_modes->$mode->default_block_field;
                if ($blockField && ($default = $this->getData($blockField))) {
                    return $default;
                }
                if ($configKey) {
                    return Mage::getStoreConfig($configKey);
                }
            }
            return 10;
        }
        else {
            return parent::getDefaultPerPageValue();
        }
    }
    public function getAvailableLimit() {
        if ($this->_getCustomListModes()) {
            $limit = $this->_defaultAvailableLimit;
            /* @var $t M_Theme_Helper_Data */
            $t = Mage::helper(strtolower('M_Theme'));
            $config = $t->getConfig()->getNode();
            $mode = $this->getCurrentMode();
            if (isset($config->catalog->product->list_modes->$mode)) {
                $configKey = (string)$config->catalog->product->list_modes->$mode->per_page_values_config;
                if ($configKey) {
                    $limit = explode(',', Mage::getStoreConfig($configKey));
                    $limit = array_combine($limit, $limit);
                }
            }

            if (Mage::getStoreConfigFlag('catalog/frontend/list_allow_all')) {
                return ($limit + array('all'=>$this->__('All')));
            } else {
                return $limit;
            }
        }
        else {
            return parent::getAvailableLimit();
        }
    }
}