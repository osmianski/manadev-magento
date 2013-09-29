<?php

/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author alin.balan@innobyte.com
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Resource_Enterprise_Rewrite extends Enterprise_UrlRewrite_Model_Resource_Url_Rewrite {
	private $_target = null;
    public function loadByRequestPath(Mage_Core_Model_Abstract $object, $paths)
    {
		$_core = Mage::helper(strtolower('Mana_Core'));
        $conditionalWord = $_core->getStoreConfig('mana_filters/seo/conditional_word');
        $categorySuffix = Mage::helper('manapro_filterseolinks')->getCategoryUrlSuffix();
		$realPaths = array();	
		$params = array();
		
		$pos = array_search($conditionalWord, $paths);
		if($pos !== false ) {
			$realPaths = array_slice($paths, 0, $pos);
			$params = array_slice($paths, $pos + 1);
		} else {
			$realPaths = $paths;
		}
		
		//var_dump($realPaths, $params);die;
        $select = $this->_getReadAdapter()->select()
            ->from(array('m' => $this->getMainTable()), array(new Zend_Db_Expr('COUNT(url_rewrite_id)')))
            ->where('m.request_path in (?)', $realPaths);

        $result = $this->_getReadAdapter()->fetchOne($select);

        if (count($realPaths) == (int)$result) {
            $select = $this->_getReadAdapter()->select()
                ->from(array('m' => $this->getMainTable()))
                ->where('m.request_path = ?', array_pop($realPaths));
            $result = $this->_getReadAdapter()->fetchRow($select);

            if ($result) {
                $object->setData($result);
				$this->_target =  $result['target_path'];
            }

            $this->unserializeFields($object);
            $this->_afterLoad($object);
        }
		$parameters = implode('/', $params);
	    $query_string  = http_build_query($this->_getQueryParameters($parameters), '', '&');

		if(!empty($query_string)) {
			$object->setTargetPath($object->getTargetPath() . '?' .$query_string);
			Mage::app()->getRequest()->setRequestUri($object->getTargetPath());
			Mage::app()->getRequest()->setPathInfo($object->getTargetPath());
		}
        return $this;
    }
	private function _createParams($params) {
		if(!is_array($params)) {
			return array();
		}
		if(count($params) == 0) {
			return array();
		}
		$odds = array_flip(array_filter(array_flip($params), function($var) {
			return($var & 1);
		}));
		$even = array_flip(array_filter(array_flip($params), function($var) {
			return(!($var & 1));
		}));
		return array_combine($even, $odds);
	}

    protected function _getQueryParameters($parameters) {
    	$parameters = explode('/', $parameters);
    	$state = 0; // waiting for parameter
    	$urlValue = '';
    	$filterName = false;
    	$result = array();
        
        /* @var $_core Mana_Core_Helper_Data */ $_core = Mage::helper(strtolower('Mana_Core'));
		$showAllSuffix = $_core->getStoreConfig('mana_filters/seo/show_all_suffix');
		$showAllSeoSuffix = $showAllSuffix;

    	for ($parameterIndex = 0; $parameterIndex < count($parameters); ) {
    	    $parameter = $parameters[$parameterIndex];
    	    switch ($state) {
                case 0: // waiting for parameter
                    if ($showAllSuffix && strrpos($parameter, $showAllSeoSuffix) === strlen($parameter) - strlen($showAllSeoSuffix)) {
                        $state = 2; // show all suffix found
                    }
                    elseif ($filterName = $this->_getFilterName($parameter)) {
                        $urlValue = '';
                        $parameterIndex++;
                        $state = 1; // waiting for value
                    }
                    else {
                        $parameterIndex+=2;
                    }
                    break;

                case 1: // waiting for value
                    if (!$urlValue) {
                        $urlValue = $parameter;
                        $parameterIndex++;
                    }
                    elseif ($nextFilterName = $this->_getFilterName($parameter)) {
                        $values = array();
                        foreach (explode('_', $urlValue) as $value) {
                            $values[] = $this->_getFilterValue($filterName, $value);
                        }
                        $result[$filterName] = implode('_', $values);

                        $filterName = $nextFilterName;
                        $urlValue = '';
                        $parameterIndex++;
                    }
                    else {
                        $urlValue .= '/'.$parameter;
                        $parameterIndex++;
                    }
                    break;
                case 2: // show all suffix found
                    $requestVar = substr($parameter, 0, strlen($parameter) - strlen($showAllSeoSuffix));
                    $filterName = $this->_getFilterName($requestVar);
                    $result[$filterName . $showAllSuffix] = 1;

                    $parameterIndex++;
                    break;
            }
    	}
    	if ($state == 1 && $urlValue) {
            $values = array();
            foreach (explode('_', $urlValue) as $value) {
                $values[] = $this->_getFilterValue($filterName, $value);
            }
            $result[$filterName] = implode('_', $values);
        }
    	return $result;
    }
    protected function _getFilterName($seoName) {
		$vars = Mage::helper('manapro_filterseolinks')->getRewriteVars();
        if ($seoName == Mage::helper('manapro_filterseolinks')->getCategoryName()) {
    		return 'cat'; 
    	}
    	elseif(isset($vars[$seoName])) {
    		return $vars[$seoName];
    	}
    	else {
        	/* @var $_core Mana_Core_Helper_Data */ $_core = Mage::helper(strtolower('Mana_Core'));
    		$candidateNames = array($seoName, Mage::getStoreConfigFlag('mana_filters/seo/use_label') ? $_core->urlToLabel($seoName) : $_core->lowerCased($seoName));
    		/*@var $resource ManaPro_FilterSeoLinks_Resource_Rewrite */ $resource = Mage::getResourceModel('manapro_filterseolinks/rewrite');
    		return $resource->getFilterName($candidateNames);
    	}
    }
   	public function getCategoryId() {
		$info = $this->_target;
		if(is_null($info)) {
			return null;
		}	
		return array_pop(explode('/', $info));
	} 

	public function getStoreId() {
		return Mage::app()->getStore()->getId();
	}
    protected function _getFilterValue($name, $seoValue) {
    	/*@var $resource ManaPro_FilterSeoLinks_Resource_Rewrite */ $resource = Mage::getResourceModel('manapro_filterseolinks/rewrite');
		$vars = Mage::helper('manapro_filterseolinks')->getUrlVars();
    	if ($name == 'cat') {
    		return $resource->getCategoryValue($this, $seoValue);
    	}
    	elseif(isset($vars[$name])) {
    		return $seoValue;
    	}
    	else {
	    	/* @var $_core Mana_Core_Helper_Data */ $_core = Mage::helper(strtolower('Mana_Core'));
	    	$candidateValues = array($seoValue, $_core->urlToLabel($seoValue));
    		return $resource->getFilterValue($this, $name, $candidateValues);
    	}
    }
}
