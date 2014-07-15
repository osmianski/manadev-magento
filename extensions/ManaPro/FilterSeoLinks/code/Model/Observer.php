<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Model_Observer extends Mage_Core_Helper_Abstract {
    protected function _findLayeredNavigationBlock($candidates) {
        foreach ($candidates as $candidate) {
            if ($layer = Mage::getSingleton('core/layout')->getBlock($candidate)) {
                return $layer;
            }
        }
        return null;
    }
    
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_generate_blocks_after")
     * @param Varien_Event_Observer $observer
     */
    public function addAppliedFiltersToTitle($observer) {
        // this feature is obsolete when ManaPro_FilterContent module is in place and is enabled
        if (Mage::getStoreConfigFlag('mana_filtercontent/general/is_active')) {
            return;
        }

        /* @var $action Mage_Core_Controller_Varien_Action */ $action = $observer->getEvent()->getAction();
        /* @var $layout Mage_Core_Model_Layout */ $layout = $observer->getEvent()->getLayout();
        /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        /* @var $filters Mana_Filters_Helper_Data */ $filters = Mage::helper(strtolower('Mana_Filters'));

        if ($head = $layout->getBlock('head')) {
            /* @var $head Mage_Page_Block_Html_Head */
            /* @var $layer Mage_Catalog_Model_Layer */
            $layer = $filters->getLayer();
            if ($core->getRoutePath() == 'catalog/category/view') {
                $page = Mage::app()->getRequest()->getParam('p');
                $appliedFilters = $layer->getState()->getFilters();
                if ($appliedFilters || $page && $page > 1) {
                    $core->callProtectedMethod(array($action, '_renderTitles'));
                    $this->_addAppliedFiltersToCategoryTitle($head, $appliedFilters, $page);
                }
            } elseif ($core->getRoutePath() == 'catalogsearch/result/index') {
                $page = Mage::app()->getRequest()->getParam('p');
                $appliedFilters = $layer->getState()->getFilters();
                if ($appliedFilters || $page && $page > 1) {
                    $core->callProtectedMethod(array($action, '_renderTitles'));
                    $this->_addAppliedFiltersToSearchTitle($head, $appliedFilters, $page);
                }
            }
        }
    }
    protected function _addAppliedFiltersToCategoryTitle($head, $appliedFilters, $page) {
        if (count($appliedFilters) || $page && $page > 1) {
            /** @var $head Mage_Page_Block_Html_Head */
            $head->getTitle();
            $globalVars = array(
                'title' => $this->_getInitialTitle($head),
                'keywords' => $this->_getInitialKeywords($head),
                'description' => $this->_getInitialDescription($head),
                'page' => $page,
                'site' => (object)array(
                    'title' => $head->getDefaultTitle(),
                ),
                '_values' => array(),
                '_break' => false,
                '_filterPattern' => null,
                '_valuePattern' => null,
                '_valuePatterns' => array(),
                '_keyword_valuePattern' => null,
                '_keyword_valuePatterns' => array(),
                '_description_valuePattern' => null,
                '_description_valuePatterns' => array(),
            );

            foreach ($appliedFilters as /* @var $item Mana_Filters_Model_Item */$item) {
                $globalVars['_values'][] = (object)array(
                    '_obj' => $item,
                    'title' => Mage::helper('core')->stripTags($item->getLabel()),
                );
            }

            if (file_exists(BP . '/app/etc/m_page_titles.xml')) {
                $xml = simplexml_load_string(file_get_contents(BP . '/app/etc/m_page_titles.xml'));
            } else {
                $xml = simplexml_load_string(file_get_contents(BP . '/app/code/local/ManaPro/FilterSeoLinks/etc/m_page_titles.xml'));
            }

            foreach ($xml->children() as $ruleName => $rule) {
                switch ($ruleName) {
                    case 'case':
                        $this->_processCase($rule, $globalVars);
                        break;
                    case 'values':
                        $this->_processValues($rule, $globalVars);
                        break;
                    case 'keyword_values':
                        $this->_processValues($rule, $globalVars);
                        break;
                    case 'description_values':
                        $this->_processValues($rule, $globalVars);
                        break;
                    case 'filters':
                        $this->_processFilters($rule, $globalVars);
                        break;
                    case 'page':
                        $this->_processPage($rule, $globalVars);
                        break;
                    case 'finally':
                        $this->_processFinally($rule, $globalVars);
                        break;
                    default:
                        throw new Exception('Not implemented');
                }
                if ($globalVars['_break']) {
                    break;
                }
            }

            $head
                ->setTitle($globalVars['title'])
                ->setData('keywords', $globalVars['keywords'])
                ->setData('description', $globalVars['description']);
        }
    }
    protected function _addAppliedFiltersToSearchTitle($head, $appliedFilters, $page) {
        if (count($appliedFilters) || $page && $page > 1) {
            $title = array();
            foreach ($appliedFilters as $filter) {
                $title[] = Mage::helper('core')->stripTags($filter->getLabel());
            }
            if ($title = implode(', ', $title)) {
                $head->setTitle($this->_getInitialTitle($head) . ': ' . $title . ($page && $page > 1 ? " (Page $page)" : ''));
            }
        }
    }

	/**
     * @param SimpleXMLElement $rule
     * @param array $variables
     */
	protected function _processCase($rule, &$globalVars) {
        $locals = array(
            '_matches' => true,
            '_found' => array(),
        );
        if (!empty($rule['category_id'])) {
            /* @var $filters Mana_Filters_Helper_Data */
            $filters = Mage::helper(strtolower('Mana_Filters'));
            $layer = $filters->getLayer();
            $locals['_matches'] = $layer->getCurrentCategory()->getId() == ((string)$rule['category_id']);
        }
        if ($locals['_matches']) {
            foreach ($rule->children() as $instructionName => $instruction) { /* @var $instruction SimpleXMLElement */
                switch ($instructionName) {
                    case 'if': $this->_processCaseIf($instruction, $globalVars, $locals); break;
                    case 'set': if ($locals['_matches']) $this->_processSet($instruction, $globalVars, $locals); break;
                    default: throw new Exception('Not implemented');
                }
            }
        }
        if ($locals['_matches']) {
            foreach ($locals['_found'] as $index) {
                unset($globalVars['_values'][$index]);
            }
            if (!empty($rule['break'])) {
                $globalVars['_break'] = true;
            }
        }
	}
	/**
     * @param SimpleXMLElement $instruction
     * @param array $globals
     * @param array $locals
     */
	protected function _processCaseIf($instruction, &$globalVars, &$locals) {
	    foreach ($globalVars['_values'] as $index => $value) {
	        if ((string)$instruction['filter_code'] == $value->_obj->getFilter()->getFilterOptions()->getCode() &&
	            (string)$instruction['value_label'] == Mage::helper('core')->stripTags($value->_obj->getLabel()))
	        {
	            if (isset($instruction['as'])) {
	                $locals[(string)$instruction['as']] = $value;
	            }
                $locals['_found'][] = $index;
	            return;
	        }
	    }
	    $locals['_matches'] = false;
	}
	protected function _processSet($instruction, &$globalVars, $locals = null) {
	    foreach ($instruction->attributes() as $key => $value) {
	        $globalVars[$key] = $this->_processValue((string)$value, $globalVars, $locals);
	    }
	}
	protected function _processValue($__template, $__globalVars, $__locals = null) {
	    extract($__globalVars);
	    if ($__locals) {
	        extract($__locals);
	    }
	    return eval(' return "'.$__template.'";');
	}
	protected function _getValuePatternVarName($var) {
        return '_'. substr($var, 0, strlen($var) - 1).'Pattern';
    }

    protected function _getFinalVarName($var) {
        switch ($var) {
            case 'values':
                return 'title';
            case 'keyword_values':
                return 'keywords';
            case 'description_values':
                return 'description';
            default:
                throw new Exception('Not implemented');

        }
    }

    protected function _processValues($rule, &$globalVars) {
        $patternVar = $this->_getValuePatternVarName($rule->getName());
        $locals = array();
        foreach ($rule->children() as $instructionName => $instruction) { /* @var $instruction SimpleXMLElement */
            switch ($instructionName) {
                case 'if': $this->_processApplyIf($instruction, $globalVars, $locals); break;
                case 'apply':
                    if (isset($locals['code'])) {
                        $this->_processApply($instruction, $globalVars, $patternVar . 's', $locals['code']);
                    }
                    else {
                        $this->_processApply($instruction, $globalVars, $patternVar);
                    }
                    break;
                default: throw new Exception('Not implemented');
            }
        }
    }
    protected function _processApplyIf($instruction, &$globalVars, &$locals) {
        $locals['code'] = (string)$instruction['filter_code'];
    }
    protected function _processFilters($rule, &$globalVars) {
        foreach ($rule->children() as $instructionName => $instruction) { /* @var $instruction SimpleXMLElement */
            switch ($instructionName) {
                case 'apply': $this->_processApply($instruction, $globalVars, '_filterPattern'); break;
                default: throw new Exception('Not implemented');
            }
        }
    }

    protected function _processPage($rule, &$globalVars) {
        if ($globalVars['page'] && $globalVars['page'] > 1) {
            $globalVars['page'] = $this->_processValue((string)$rule['pattern'], $globalVars);
        }
    }

    protected function _processApply($instruction, &$globalVars, $var, $key = null) {
        $pattern = array(
            'pattern' => (string)$instruction['pattern'],
            'glue' => (string)$instruction['glued_by'],
            'lastGlue' => isset($instruction['last_glued_by'])
                ? (string)$instruction['last_glued_by']
                : (string)$instruction['glued_by'],
            'prefix' => isset($instruction['prefix']) ? $instruction['prefix'] : ''
        );
        if ($key === null) {
            $globalVars[$var] = $pattern;
        }
        else {
            $globalVars[$var][$key] = $pattern;
        }
    }
    protected function _prepareProcessFinally($var, &$globalVars) {
        $patternVar = $this->_getValuePatternVarName($var);
        $valuePattern = array('pattern' => '{$value->title}', 'glue' => ', ', 'lastGlue' => ', ', 'prefix' => ': ');
        $valuePattern = $globalVars[$patternVar] ? $globalVars[$patternVar] : $valuePattern;
        if ($globalVars['_filterPattern'] && $var == 'values') {
            $filters = array();
            $filterValues = array();
            foreach ($globalVars['_values'] as $value) {
                $code = $value->_obj->getFilter()->getFilterOptions()->getCode();
                if (!isset($filters[$code])) {
                    $filters[$code] = array(
                        'pattern' => isset($globalVars[$patternVar.'s'][$code])
                            ? $globalVars[$patternVar.'s'][$code]
                            : $valuePattern,
                        'options' => $value->_obj->getFilter()->getFilterOptions(),
                        'values' => array(),
                    );
                }
                $filters[$code]['values'][] = $this->_processValue($filters[$code]['pattern']['pattern'], $globalVars, compact('value'));
            }
            foreach ($filters as $filter) {
                $values = $this->_implode($filter['values'], $filter['pattern']);
                $filter = (object)array('title' => $filter['options']->getName());
                $filterValues[] = $this->_processValue($globalVars['_filterPattern']['pattern'], $globalVars, compact('values', 'filter'));
            }
            $globalVars['filters'] = $this->_implode($filterValues, $globalVars['_filterPattern']);
            if ($globalVars['filters']) {
                $globalVars['filters'] = $globalVars['_filterPattern']['prefix'] . $globalVars['filters'];
            }
        }
        else {
            $values = array();
            foreach ($globalVars['_values'] as $value) {
                $values[] = $this->_processValue($valuePattern['pattern'], $globalVars, compact('value'));
            }
            $globalVars[$var] = $this->_implode($values, $valuePattern);
            if ($globalVars[$var] && ($this->_getFinalVarName($var) != 'keywords' || $globalVars[$this->_getFinalVarName($var)])) {
                $globalVars[$var] = $valuePattern['prefix'] . $globalVars[$var];
            }
        }
    }
    protected function _processFinally($rule, &$globalVars) {
        $this->_prepareProcessFinally('values', $globalVars);
        $this->_prepareProcessFinally('keyword_values', $globalVars);
        $this->_prepareProcessFinally('description_values', $globalVars);
        foreach ($rule->children() as $instructionName => $instruction) { /* @var $instruction SimpleXMLElement */
            switch ($instructionName) {
                case 'set': $this->_processSet($instruction, $globalVars); break;
                default: throw new Exception('Not implemented');
            }
        }
    }
    protected function _implode($values, $pattern) {
        $count = count($values);
        if ($count == 0) {
            return '';
        }
        elseif ($count == 1) {
            return $values[0];
        }
        elseif ($count == 2) {
            return implode($pattern['lastGlue'], $values);
        }
        else {
            return implode($pattern['glue'], array_slice($values, 0, $count - 1)).$pattern['lastGlue'].$values[$count - 1];
        }
    }


    protected function _noindex() {
        $layerModel = $this->filterHelper()->getLayer();
        if (($head = Mage::getSingleton('core/layout')->getBlock('head'))) {
            /* @var $head Mage_Page_Block_Html_Head */
            $robots = $head->getRobots();
            $noIndex = false;
            $follow = false;
            foreach (explode(',', Mage::getStoreConfig('mana_filters/seo/no_index')) as $noIndexProcessorName) {
                if (!$noIndexProcessorName) {
                    continue;
                }

                /* @var $noIndexProcessor ManaPro_FilterSeoLinks_Model_Condition */
                $noIndexProcessor = Mage::getModel((string)Mage::getConfig()->getNode('manapro_filterseolinks/noindex')->$noIndexProcessorName->model);
                if ($noIndexProcessor->detect($layerModel)) {
                    $noIndex = true;
                    break;
                }
            }

            foreach (explode(',', Mage::getStoreConfig('mana_filters/seo/follow')) as $followProcessorName) {
                if (!$followProcessorName) {
                    continue;
                }

                /* @var $followProcessor ManaPro_FilterSeoLinks_Model_Condition */
                $followProcessor = Mage::getModel((string)Mage::getConfig()->getNode('manapro_filterseolinks/follow')->$followProcessorName->model);
                if ($followProcessor->detect($layerModel)) {
                    $follow = true;
                    break;
                }
            }

            if ($noIndex) {
                $head->setRobots($follow ? 'NOINDEX, FOLLOW' : 'NOINDEX, NOFOLLOW');
            }
        }
    }
    /**
     * Adds NOINDEX if configured so (handles event "controller_action_layout_render_before_catalog_category_view")
     * @param Varien_Event_Observer $observer
     */
    public function noindexCategoryView($observer) {
        $this->_noindex();
    }
    /**
     * Adds NOINDEX if configured so (handles event "controller_action_layout_render_before_catalogsearch_result_index  ")
     * @param Varien_Event_Observer $observer
     */
    public function noindexSearchResult($observer) {
        $this->_noindex();
    }
    /**
     * Adds NOINDEX if configured so (handles event "controller_action_layout_render_before_cms_page_view")
     * @param Varien_Event_Observer $observer
     */
    public function noindexCmsPage($observer) {
        $this->_noindex();
    }
    /**
     * Adds NOINDEX if configured so (handles event "controller_action_layout_render_before_cms_index_index")
     * @param Varien_Event_Observer $observer
     */
    public function noindexCmsIndex($observer) {
        $this->_noindex();
    }
    /**
     * Adds NOINDEX if configured so (handles event "controller_action_layout_render_before_mana_optionpage_view")
     * @param Varien_Event_Observer $observer
     */
    public function noindexOptionPage($observer) {
        $this->_noindex();
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_before_load_filter_collection")
     * @param Varien_Event_Observer $observer
     */
    public function addLowerCaseNameColumnToFilterCollection($observer) {
        /* @var $collection Mana_Filters_Resource_Filter2_Store_Collection */ $collection = $observer->getEvent()->getCollection();
        $collection->getSelect()->columns('LOWER(main_table.name) AS lower_case_name');
    }
    protected function _getInitialTitle($head) {
        /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        $title = $head->getData('title');
        if (($prefix = Mage::getStoreConfig('design/head/title_prefix')) && $core->startsWith($title, $prefix)) {
            $title = substr($title, strlen($prefix) + 1);
        }
        if (($suffix = Mage::getStoreConfig('design/head/title_suffix')) && $core->endsWith($title, $suffix)) {
            $title = substr($title, 0, strlen($title) - strlen($suffix) - 1);
        }
        return $title;
    }
    /**
     * @param Mage_Page_Block_Html_Head $head
     * @return string
     */
    protected function _getInitialKeywords($head) {
        $result = $head->getData('keywords');
        return $result;
    }

    /**
     * @param Mage_Page_Block_Html_Head $head
     * @return string
     */
    protected function _getInitialDescription($head) {
        $result = $head->getData('description');
        return $result;
    }

    //region Obsolete event handlers. Left here for easier upgrade
    public function addCategoryTitle($observer) {
    }
    public function addSearchTitle($observer) {
    }
    //endregion

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Adds columns to replication update select (handles event "m_db_update_columns")
     * @param Varien_Event_Observer $observer
     */
    public function prepareUpdateColumns($observer) {
        /* @var $target Mana_Db_Model_Replication_Target */
        $target = $observer->getEvent()->getData('target');

        switch ($target->getEntityName()) {
            case 'mana_filters/filter2_store':
                $target->getSelect('main')->columns(array(
                    'global.include_in_url AS include_in_url',
                    'global.url_position AS url_position',
                    'global.include_in_canonical_url AS include_in_canonical_url',
                ));
                break;
        }
    }

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Adds values to be updated (handles event "m_db_update_process")
     * @param Varien_Event_Observer $observer
     */
    public function processUpdate($observer) {
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getData('object');
        /* @var $values array */
        $values = $observer->getEvent()->getData('values');

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');
        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
                if (!$dbHelper->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_URL)) {
                    $object->setData('include_in_url', Mana_Seo_Model_Source_IncludeInUrl::AS_IN_SCHEMA);
                }
                if (!$dbHelper->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_URL_POSITION)) {
                    $object->setData('url_position', $values['position']);
                }
                if (!$dbHelper->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_CANONICAL_URL)) {
                    $object->setData('include_in_canonical_url', Mana_Seo_Model_Source_IncludeInUrl::AS_IN_SCHEMA);
                }
                break;
            case 'mana_filters/filter2_store':
                if (!$dbHelper->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_URL)) {
                    $object->setData('include_in_url', $values['include_in_url']);
                }
                if (!$dbHelper->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_URL_POSITION)) {
                    $object->setData('url_position', $values['url_position']);
                }
                if (!$dbHelper->hasOverriddenValue($object, $values, Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_CANONICAL_URL)) {
                    $object->setData('include_in_canonical_url', $values['include_in_canonical_url']);
                }
                break;
        }
    }

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Adds columns to replication insert select (handles event "m_db_insert_columns")
     * @param Varien_Event_Observer $observer
     */
    public function prepareInsertColumns($observer) {
        /* @var $target Mana_Db_Model_Replication_Target */
        $target = $observer->getEvent()->getData('target');

        switch ($target->getEntityName()) {
            case 'mana_filters/filter2_store':
                $target->getSelect('main')->columns(array(
                    'global.include_in_url AS include_in_url',
                    'global.url_position AS url_position',
                    'global.include_in_canonical_url AS include_in_canonical_url',
                ));
                break;
        }
    }

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Adds values to be inserted (handles event "m_db_insert_process")
     * @param Varien_Event_Observer $observer
     */
    public function processInsert($observer) {
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getData('object');
        /* @var $values array */
        $values = $observer->getEvent()->getData('values');

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
                $object->setData('include_in_url', Mana_Seo_Model_Source_IncludeInUrl::AS_IN_SCHEMA);
                $object->setData('url_position', $values['position']);
                $object->setData('include_in_canonical_url', Mana_Seo_Model_Source_IncludeInUrl::AS_IN_SCHEMA);
                break;
            case 'mana_filters/filter2_store':
                $object->setData('include_in_url', $values['include_in_url']);
                $object->setData('url_position', $values['url_position']);
                $object->setData('include_in_canonical_url', $values['include_in_canonical_url']);
                break;
        }
    }

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Adds fields into CRUD form (handles event "m_crud_form")
     * @param Varien_Event_Observer $observer
     */
    public function addFields($observer) {
        /* @var $formBlock Mana_Admin_Block_Crud_Card_Form */
        $formBlock = $observer->getEvent()->getData('form');
        $form = $formBlock->getForm();

        /* @var $t ManaPro_FilterSeoLinks_Helper_Data */
        $t = Mage::helper('manapro_filterseolinks');

        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        /* @var $adminHelper Mana_Admin_Helper_Data */
        $adminHelper = Mage::helper('mana_admin');

        switch ($formBlock->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                /* @var $filter Mana_filters_Model_Filter2 */
                $filter = $form->getData('model');

                if ($form->getId() == 'mf_general') {
                    /** @noinspection PhpParamsInspection */
                    $fieldset = $form->addFieldset(
                        'mfs_seo',
                        array(
                            'title' => $t->__('Search Engine Optimization'),
                            'legend' => $t->__('Search Engine Optimization'),
                        )
                    );
                    /** @noinspection PhpParamsInspection */
                    $fieldset->setRenderer($layout->getBlockSingleton('mana_admin/crud_card_fieldset'));

                    /* @var $includeInUrlSource Mana_Seo_Model_Source_IncludeInUrl */
                    $includeInUrlSource = Mage::getSingleton('mana_seo/source_includeInUrl');

                    if ($filter->getData('type') == 'attribute') {
                        $field = $fieldset->addField('include_in_url', 'select', array_merge( array(
                            'label' => $t->__('Include Filter Name In URL'),
                            'name' => 'include_in_url',
                            'options' => $includeInUrlSource->getOptionArray(),
                            'required' => true,
                        ), $adminHelper->isGlobal() ? array() : array(
                            'default_bit' => Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_URL,
                            'default_label' => $t->__('Same For All Stores'),
                        )));
                        /** @noinspection PhpParamsInspection */
                        $field->setRenderer($layout->getBlockSingleton('mana_admin/crud_card_field'));
                    }

                    $field = $fieldset->addField('url_position', 'text', array(
                        'label' => $t->__('Position in URL'),
                        'name' => 'url_position',
                        'required' => true,
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_URL_POSITION,
                        'default_label' => $adminHelper->isGlobal()
                            ? ($filter->getData('type') != 'category' ? $t->__('Use Attribute Configuration') : $t->__('Use Default'))
                            : $t->__('Same For All Stores'),
                    ));
                    /** @noinspection PhpParamsInspection */
                    $field->setRenderer($layout->getBlockSingleton('mana_admin/crud_card_field'));

                    $field = $fieldset->addField('include_in_canonical_url', 'select', array_merge( array(
                        'label' => $t->__('Include Applied Filter In Canonical URL'),
                        'name' => 'include_in_canonical_url',
                        'options' => $includeInUrlSource->getOptionArray(),
                        'required' => true,
                    ), $adminHelper->isGlobal() ? array() : array(
                        'default_bit' => Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_CANONICAL_URL,
                        'default_label' => $t->__('Same For All Stores'),
                    )));
                    /** @noinspection PhpParamsInspection */
                    $field->setRenderer($layout->getBlockSingleton('mana_admin/crud_card_field'));
                }

                break;
        }
    }

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Adds edited data received via HTTP to specified model (handles event "m_db_add_edited_data")
     * @param Varien_Event_Observer $observer
     */
    public function addEditedData($observer) {
        /* @var $object Mana_Db_Model_Object */
        $object = $observer->getEvent()->getData('object');
        /* @var $fields array */
        $fields = $observer->getEvent()->getData('fields');
        /* @var $useDefault array */
        $useDefault = $observer->getEvent()->getData('use_default');

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                $dbHelper->updateDefaultableField($object, 'include_in_url', Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_URL, $fields, $useDefault);
                $dbHelper->updateDefaultableField($object, 'url_position', Mana_Filters_Resource_Filter2::DM_URL_POSITION, $fields, $useDefault);
                $dbHelper->updateDefaultableField($object, 'include_in_canonical_url', Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_CANONICAL_URL, $fields, $useDefault);
                break;
        }
    }

    #endregion

    #region Dependencies

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filterHelper() {
        return Mage::helper('mana_filters');
    }
    #endregion
}