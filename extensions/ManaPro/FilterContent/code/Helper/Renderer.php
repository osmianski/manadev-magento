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

    protected $_content;

    public function render() {
        // only render all filter specific content once
        if ($this->_content !== null) {
            return $this->_content;
        }

        $initialContent = $this->_getInitialContent();
        $this->_content = array_merge($initialContent, $this->_getAdditionalContent());

        // do not render anything if filter specific content feature is disabled
        if (!Mage::getStoreConfigFlag('mana_filtercontent/general/is_active')) {
            return $this->_removeUnalteredContent($initialContent);
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
        return $this->_removeUnalteredContent($initialContent);

    }

    protected function _removeUnalteredContent($initialContent) {
        foreach ($initialContent as $key => $value) {
            if (isset($this->_content[$key]) && $this->_content[$key] === $value) {
                unset($this->_content[$key]);
            }
        }

        return $this->_content;
    }

    protected function _getInitialContent() {
        $result = array();
        foreach ($this->factoryHelper()->getAllContentHelpers() as $key => $contentHelper) {
            $result = $contentHelper->getInitialContent($result);
        }
        return $result;
    }

    protected function _getAdditionalContent() {
        return array(
            'filters' => array(
                array(
                    'label' => '4 & up',
                )
            )
        );
    }


    public function replacePlaceholders($content) {
        foreach ($this->factoryHelper()->getAllContentHelpers() as $key => $contentHelper) {
            if (($contentHelper->hasInitialContentReplacement())) {
                $content = str_replace($contentHelper->getPlaceholder(), $contentHelper->getInitialContentReplacement(), $content);
            }
        }
        return $content;
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

    #endregion
}