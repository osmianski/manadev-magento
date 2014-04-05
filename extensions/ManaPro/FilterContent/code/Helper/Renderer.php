<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Helper_Renderer extends Mage_Core_Helper_Abstract {

//    /**
//     * @param string $routePath
//     * @param Mana_Filters_Model_Item $filters
//     * @param string[] $toolbarParams
//     *
//     * @return $this
//     */
//    public function init($routePath, $filters, $toolbarParams) {
//        return $this;
//    }

    protected $_initialContent;
    protected $_content;

    public function render() {
        // only render all filter specific content once
        if ($this->_content !== null) {
            return $this->_content;
        }

        $this->_initContent();

        // do not render anything if filter specific content feature is disabled
        if (!Mage::getStoreConfigFlag('mana_filtercontent/general/is_active')) {
            return $this->_removeUnalteredContent();
        }

//        // initialize renderer with route path, currently applied filters and other paging/sorting display options
//        /** @noinspection PhpParamsInspection */
//        $renderer = $this->rendererHelper()->init(
//            $this->coreHelper()->getRoutePath(),
//            $this->filterHelper()->getLayer()->getState()->getFilters(),
//            $this->coreHelper()->getProductToolbarParameters()
//        );

        // run page type based initialization templates

        // run custom initialization templates

        // search for matching rules and in all rule providers

        // run each rule (if relevant) templates in order of priority

        // run custom finalization templates
        $this->_processAction($this->finalActionHelper()->read());


        // remove unaltered content
        return $this->_removeUnalteredContent();

    }

    public function getContent() {
        return $this->render();
    }

    protected function _removeUnalteredContent() {
        foreach ($this->_initialContent as $key => $value) {
            if (isset($this->_content[$key]) && $this->_content[$key] === $value) {
                unset($this->_content[$key]);
            }
        }

        return $this->_content;
    }

    protected function _initContent() {
        $this->_initialContent = array();
        if ($pageType = $this->coreHelper()->getPageTypeByRoutePath()) {
            $this->_initialContent = $pageType->getPageContent();
        }

        $pageContent = array();
        foreach ($this->_initialContent as $key => $value) {
            $pageContent['page_' . $key] = $value;
        }

        $this->_content = array_merge($this->_initialContent, $pageContent, $this->filterHelper()->getPageContent());
    }

    /**
     * @param string $provider
     * @return array
     */
    protected function _getTemplates($provider) {
        list($provider, $method) = explode('::', $provider);
        $args = func_get_args();
        array_shift($args);
        return call_user_func_array(array(Mage::helper($provider), $method), $args);
    }

    protected function _processAction($actions) {
        foreach ($this->factoryHelper()->getAllContentHelpers() as $key => $contentHelper) {
            $this->_content = $contentHelper->processActions($this->_content, $actions);
        }
    }


    #region Dependencies

    /**
     * @return ManaPro_FilterContent_Helper_Factory
     */
    public function factoryHelper() {
        return Mage::helper('manapro_filtercontent/factory');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Action_Final
     */
    public function finalActionHelper() {
        return Mage::helper('manapro_filtercontent/action_final');
    }

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filterHelper() {
        return Mage::helper('mana_filters');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    #endregion
}