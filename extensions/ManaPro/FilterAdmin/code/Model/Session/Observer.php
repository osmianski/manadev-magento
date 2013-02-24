<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdmin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Injects session related behavior
 * @author Mana Team
 *
 */
class ManaPro_FilterAdmin_Model_Session_Observer {
	const REMEMBER = 1;
	const RESTORE = 2;
	const REMOVE = 3;
	
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Remembers category filters or applies remembered filters (handles event "controller_action_predispatch_catalog_category_view")
	 * @param Varien_Event_Observer $observer
	 */
	public function rememberCategoryFilters($observer) {
		/* @var $action Mage_Catalog_CategoryController */ $action = $observer->getEvent()->getControllerAction();

		if (Mage::getStoreConfigFlag('mana_filters/session/save_applied_filters')) {
			if ($categoryId = (int) $action->getRequest()->getParam('id', false)) {
			    $this->_rememberOrRestoreFilter($action, 'm_category_filters_'.$categoryId);
			}
		}
	}

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Remembers category filters or applies remembered filters (handles event "controller_action_predispatch_catalogsearch_result_index")
     * @param Varien_Event_Observer $observer
     */
    public function rememberSearchFilters($observer) {
        /* @var $action Mage_Catalog_CategoryController */
        $action = $observer->getEvent()->getControllerAction();

        if (Mage::getStoreConfigFlag('mana_filters/session/save_applied_search_filters')) {
            $this->_rememberOrRestoreFilter($action, 'm_search_filters');
        }
    }

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Remembers category filters or applies remembered filters (handles event "controller_action_predispatch_cms_index_index")
     * @param Varien_Event_Observer $observer
     */
    public function rememberHomeFilters($observer) {
        /* @var $action Mage_Catalog_CategoryController */
        $action = $observer->getEvent()->getControllerAction();

        if (Mage::getStoreConfigFlag('mana_filters/session/save_applied_cms_filters')) {
            $this->_rememberOrRestoreFilter($action, 'm_home_filters');
        }
    }

    /* BASED ON SNIPPET: Models/Event handler */
    /**
     * Remembers category filters or applies remembered filters (handles event "controller_action_predispatch_cms_page_view")
     * @param Varien_Event_Observer $observer
     */
    public function rememberCmsFilters($observer) {
        /* @var $action Mage_Catalog_CategoryController */
        $action = $observer->getEvent()->getControllerAction();

        if (Mage::getStoreConfigFlag('mana_filters/session/save_applied_cms_filters')) {
            if ($pageId = (int)$action->getRequest()->getParam('id', false)) {
                $this->_rememberOrRestoreFilter($action, 'm_category_filters_' . $pageId);
            }
        }
    }

    protected function _rememberOrRestoreFilter($action, $localKey) {
        extract($this->_getAppliedFilters($action));
        /* @var $locals array */
        /* @var $globals array */
        /* @var $specials array */
        /* @var $do int */
//        Mage::log('------------', Zend_Log::DEBUG, 'filter_session.log');
//        Mage::log('locals: ' . json_encode($locals), Zend_Log::DEBUG, 'filter_session.log');
//        Mage::log('globals: ' . json_encode($globals), Zend_Log::DEBUG, 'filter_session.log');
//        Mage::log('specials: ' . json_encode($specials), Zend_Log::DEBUG, 'filter_session.log');
        /* @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('core/session');
        //if (!count($specials) && !count($locals) && !count($globals)) {
        if (empty($specials['m-layered'])) {
            // restore
            $query = array();
            if ($session->hasData('m_global_filters')) {
                $query = array_merge($query, $session->getData('m_global_filters'));
                foreach ($globals as $key => $value) {
                    if (!isset($query[$key])) {
                        $query[$key] = null;
                    }
                }
            }
            if ($session->hasData($localKey)) {
                $query = array_merge($query, $session->getData($localKey));
                foreach ($locals as $key => $value) {
                    if (!isset($query[$key])) {
                        $query[$key] = null;
                    }
                }
            }
            $params = array('_current' => true, '_use_rewrite' => true);
            $url = Mage::getUrl('*/*/*', array_merge($params, array('_query' => $query)));
            if ($url != Mage::getUrl('*/*/*', $params)) {
                // redirect to URL with applied filters
                $action->getResponse()->setRedirect($url);
                $action->getRequest()->setDispatched(true);
            }
        }
        else {
            // remember/remove
            $session->setData('m_global_filters', $globals);
            $session->setData($localKey, $locals);
        }
    }
	protected static $_specialParameters;
	protected function _getSpecialParameters() {
		if (!self::$_specialParameters) {
			self::$_specialParameters = array(
            	Mage::getBlockSingleton('catalog/product_list_toolbar')->getPageVarName(),
            	Mage::getBlockSingleton('catalog/product_list_toolbar')->getLimitVarName(),
            	Mage::getBlockSingleton('catalog/product_list_toolbar')->getOrderVarName(),
            	Mage::getBlockSingleton('catalog/product_list_toolbar')->getDirectionVarName(),
            	Mage::getBlockSingleton('catalog/product_list_toolbar')->getModeVarName(),
            	'm-ajax',
            	'm-layered',
			);
		}
		return self::$_specialParameters;
	}
	protected $_globalParameters;
	protected function _getGlobalParameters() {
	    if (!$this->_globalParameters) {
            $this->_globalParameters = array();
	        if (($codes = Mage::getStoreConfig('mana_filters/session/globally_applied_filters')) && trim($codes)) {
                $codes = explode(',', $codes);
                foreach ($codes as $param) {
                    $this->_globalParameters[trim($param)] = trim($param);
                }
                $this->_globalParameters = array_values($this->_globalParameters);
            }
	    }
	    return $this->_globalParameters;
	}


	protected function _getAppliedFilters($action) {
	    $locals = array();
	    $globals = array();
	    $specials = array();

        foreach (array_keys($action->getRequest()->getQuery()) as $param) {
            if (in_array($param, $this->_getSpecialParameters())) {
                $specials[$param] = $action->getRequest()->getParam($param);
            }
            elseif (in_array($param, $this->_getGlobalParameters())) {
                $globals[$param] = $action->getRequest()->getParam($param);
            }
            elseif (in_array($param, $this->_getFilterNames())) {
                $locals[$param] = $action->getRequest()->getParam($param);
            }
        }

        if (count($specials) > 0) {
            if (count($locals) + count($globals) > 0) {
                $do = self::REMEMBER;
            }
            else {
                $do = self::REMOVE;
            }
        }
        else {
            $do = self::RESTORE;
        }

        return compact('locals', 'globals', 'specials', 'do');
    }

    protected $_filterNames;
    protected function _getFilterNames() {
        if (!$this->_filterNames) {
            /* @var $db Mage_Core_Model_Resource */
            $db = Mage::getSingleton('core/resource');
            /* @var $connection Varien_Db_Adapter_Pdo_Mysql */
            $connection = $db->getConnection('core_read');
            /* @var $select Varien_Db_Select */
            $select = $connection->select();
            $select
                ->from(array('a' => $db->getTableName('eav_attribute')), 'attribute_code')
                ->join(array('t' => $db->getTableName('eav_entity_type')), 't.entity_type_id = a.entity_type_id', null)
                ->where('t.entity_type_code = ?', 'catalog_product');

            $this->_filterNames = $connection->fetchCol($select);
        }
        return $this->_filterNames;
    }
}