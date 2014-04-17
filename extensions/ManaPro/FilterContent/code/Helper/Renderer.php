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

    protected $_disposableContent;
    protected $_content;

    public function get($content) {
        $content = $this->factoryHelper()->getContent($content);

        return isset($this->_content[$content->getKey()])
            ? str_replace('<echo>', '', str_replace('</echo>', '', $this->_content[$content->getKey()]))
            : false;
    }

    public function render() {
        // only render all filter specific content once
        if ($this->_content !== null) {
            return $this->_content;
        }

        // do not render anything if filter specific content feature is disabled
        if (!Mage::getStoreConfigFlag('mana_filtercontent/general/is_active')) {
            $this->_content = array();
            return $this->_content;
        }

        Mana_Core_Profiler2::start(__METHOD__);

        // initialize content based on current page type, applied filters etc.
        if (!$this->_initContent()) {
            $this->_content = array();
            return $this->_content;
        }
        $actions = array();

        // search for matching rules and in all rule providers
        $stopped = false;
        foreach ($this->factoryHelper()->getActions() as $actionSource) {
            foreach ($actionSource->read($this->_content) as $action) {
                if ($action['is_active']) {
                    $actions[] = $action;

                    $stopped = (bool)(int)$action['stop_further_processing'];
                }
                if ($stopped) {
                    break;
                }
            }
            if ($stopped) {
                break;
            }
        }

        // add final rule
        if (!$stopped) {
            $actions[] = $this->finalActionHelper()->read();
        }

        // run each rule (if relevant) templates in order of priority
        foreach ($this->factoryHelper()->getAllContent() as $key => $contentHelper) {
            $mergedTemplate = '';
            if ($contentHelper->isContentAdded()) {
                foreach ($actions as $action) {
                    foreach ($this->factoryHelper()->getAllContent() as $secondaryContentHelper) {
                        if ($secondaryContentHelper->isContentPreProcessed() &&
                            isset($action[$secondaryContentHelper->getKey()]))
                        {
                            $template = '{% spaceless %}<echo>' . $secondaryContentHelper->getAction($action) . '<echo>{% endspaceless %}';
                            $mergedTemplate .= $template;
                        }
                    }

                    if (isset($action[$contentHelper->getKey()])) {
                        $template = '{% spaceless %}<echo>' . $contentHelper->getAction($action) . '<echo>{% endspaceless %}';
                        $mergedTemplate .= $template;
                    }
                }
            }
            elseif ($contentHelper->isContentReplaced()) {
                foreach ($actions as $i => $action) {
                    foreach ($this->factoryHelper()->getAllContent() as $secondaryContentHelper) {
                        if ($secondaryContentHelper->isContentPreProcessed() &&
                            isset($action[$secondaryContentHelper->getKey()])
                        ) {
                            $template = '{% spaceless %}<echo>' . $secondaryContentHelper->getAction($action) . '<echo>{% endspaceless %}';
                            $mergedTemplate .= $template;
                        }
                    }

                    if (isset($action[$contentHelper->getKey()])) {
                        $template = '{% spaceless %}<echo>' . $contentHelper->getAction($action) . '<echo>{% endspaceless %}';
                        if ($i == count($actions) - 1) {
                            $mergedTemplate .= $template;
                        } else {
                            $mergedTemplate .= '{% set ' . $contentHelper->getKey() . ' %}' . $template . '{% endset %}';
                        }
                    }
                }
            }

            $this->_content[$contentHelper->getKey()] =
                trim($this->twigHelper()->renderContentRule($mergedTemplate, $this->_content));
        }


        // remove unaltered content
        Mana_Core_Profiler2::stop();
        return $this->_removeDisposableContent();

    }

    public function getContent() {
        return $this->render();
    }

    protected function _removeDisposableContent() {
        foreach ($this->_disposableContent as $key => $value) {
            if (isset($this->_content[$key]) && $this->_content[$key] === $value) {
                unset($this->_content[$key]);
            }
        }

        return $this->_content;
    }

    protected function _initContent() {
        $this->_disposableContent = array();
        $this->_content = array();
        if (($pageType = $this->coreHelper()->getPageTypeByRoutePath())) {
            $this->_disposableContent = $pageType->getPageContent();

            $initialContent = array_merge($this->_disposableContent, $this->filterHelper()->getPageContent());
            $this->_content = $initialContent;
            foreach ($initialContent as $key => $value) {
                $this->_content['initial_' . $key] = $value;
            }
            return true;
        }
        else {
            return false;
        }

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

    protected function _processAction($action) {
        if ($action['is_active']) {
            foreach ($this->factoryHelper()->getAllContent() as $key => $contentHelper) {
                $this->_content = $contentHelper->processAction($this->_content, $action);
            }
            return (bool)(int)$action['stop_further_processing'];
        }
        else {
            return false;
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

    /**
     * @return Mana_Twig_Helper_Data
     */
    public function twigHelper() {
        return Mage::helper('mana_twig');
    }

    #endregion
}