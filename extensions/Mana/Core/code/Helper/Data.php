<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for Mana_Core module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Core_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     * Retrieve config value for store by path. By default uses standard Magento function to query core_config_data
     * table and use config.xml for default value. Though this could be replaced by extensions (in later versions).
     *
     * @param string $path
     * @param mixed $store
     * @return mixed
     */
	public function getStoreConfig($path, $store = null) {
		return Mage::getStoreConfig($path, $store);
	}
	public function endsWith($haystack, $needle) {
		return (strrpos($haystack, $needle) === strlen($haystack) - strlen($needle));
	}
	public function startsWith($haystack, $needle) {
		return (strpos($haystack, $needle) === 0);
	}
	public static $upperCaseCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	public static $lowerCaseCharacters = 'abcdefghijklmnopqrstuvwxyz';
	public static $whitespaceCharacters = " \t\r\n";
	protected function _explodeIdentifier($identifier) {
		$result = array(); 
		$segment = substr($identifier, 0, 1);
		$mode = 0; // not recognized
		if ($segment == '_') { $mode = 1; $result[] = ''; $segment = ''; }
		elseif ($segment == '-') { $mode = 3; $result[] = ''; $segment = ''; }
		$allUppers = !$segment || strpos(self::$upperCaseCharacters, $segment) !== false;
		for ($i = 1; $i < strlen($identifier); $i++) {
			$ch = substr($identifier, $i, 1);
			switch ($mode) {
				case 0: // not recognized
					if ($ch == '_') {
						$mode = 1; // underscored
						$result[] = $segment;
						$segment = '';
					}
					elseif ($ch == '-') {
						$mode = 3; // hyphened
						$result[] = $segment;
						$segment = '';
					}
					elseif (strpos(self::$upperCaseCharacters, $ch) !== false) {
						if (!$allUppers) {
							$mode = 2; // case separated
							$result[] = $segment;
							$segment = '';
						}
						$segment .= $ch;
					}
					else {
						if (strpos(self::$lowerCaseCharacters, $ch) !== false) $allUppers = false;
						$segment .= $ch;
					}
					break;
				case 1: // underscored
					if ($ch == '_') {
						$result[] = $segment;
						$segment = '';
					}
					else {
						$segment .= $ch;
					}
					break;
				case 2: // case separated
					if (strpos(self::$upperCaseCharacters, $ch) !== false) {
						$result[] = $segment;
						$segment = '';
					}
					$segment .= $ch;
					break;
				case 3: // hyphened
					if ($ch == '-') {
						$result[] = $segment;
						$segment = '';
					}
					else {
						$segment .= $ch;
					}
					break;
				default:
					throw new Exception('Not implemented.');
			}
		}
		if ($segment) $result[] = $segment;
		return $result;
	}
	public function pascalCased($identifier) {
		$result = '';
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			$result .= ucfirst(strtolower($segment));
		}
		return $result;
	}
	public function camelCased($identifier) {
		$result = '';
		$first = true;
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			if ($first) {
				$result .= strtolower($segment);
				$first = false;
			}
			else {
				$result .= ucfirst(strtolower($segment));
			}
		}
		return $result;
	}
	public function lowerCased($identifier) {
		$result = '';
		$separatorNeeded = false;
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			if ($separatorNeeded) $result .= '_'; else $separatorNeeded = true;
			$result .= strtolower($segment);
		}
		return $result;
	}
	public function upperCased($identifier) {
		$result = '';
		$separatorNeeded = false;
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			if ($separatorNeeded) $result .= '_'; else $separatorNeeded = true;
			$result .= strtoupper($segment);
		}
		return $result;
	}
	public function hyphenCased($identifier) {
		$result = '';
		$separatorNeeded = false;
		foreach (self::_explodeIdentifier($identifier) as $segment) {
			if ($separatorNeeded) $result .= '-'; else $separatorNeeded = true;
			$result .= strtolower($segment);
		}
		return $result;
	}
	protected $_urlSymbols;
	protected function _initUrlSymbols() {
		if (!$this->_urlSymbols) {
			$this->_urlSymbols = array();
			$this->_urlSymbols['-'] = Mage::getStoreConfig('mana_filters/seo/dash');
			$this->_urlSymbols['/'] = Mage::getStoreConfig('mana_filters/seo/slash');
			$this->_urlSymbols['+'] = Mage::getStoreConfig('mana_filters/seo/plus');
			$this->_urlSymbols['_'] = Mage::getStoreConfig('mana_filters/seo/underscore');
			$this->_urlSymbols["'"] = Mage::getStoreConfig('mana_filters/seo/quote');
			$this->_urlSymbols['"'] = Mage::getStoreConfig('mana_filters/seo/double_quote');
			$this->_urlSymbols['%'] = Mage::getStoreConfig('mana_filters/seo/percent');
            $this->_urlSymbols['#'] = Mage::getStoreConfig('mana_filters/seo/hash');
            $this->_urlSymbols['&'] = Mage::getStoreConfig('mana_filters/seo/ampersand');
            $this->_urlSymbols[' '] = Mage::getStoreConfig('mana_filters/seo/space');
        }
		return $this;
	}
	public function labelToUrl($text) {
		$this->_initUrlSymbols();
		foreach ($this->_urlSymbols as $symbol => $urlSymbol) {
            $text = str_replace($symbol, $urlSymbol, $text);
        }
		return $text;
	}
	public function urlToLabel($text) {
		$this->_initUrlSymbols();
		$result = '';
		for ($i = 0; $i < mb_strlen($text); ) {
			$found = false;
			foreach ($this->_urlSymbols as $symbol => $urlSymbol) {
				if (mb_strpos($text, $urlSymbol, $i) === $i) {
					$result .= $symbol;
					$i += mb_strlen($urlSymbol);
					$found = true;
					break;
				}
			}
			if (!$found) {
				$result .= mb_substr($text, $i++, 1);
			}
		}
		return $result;
	}
	public function translateConfig($config) {
		$this->_translateConfigRecursively($config);
		return $config;
	}
	/**
	 * Enter description here ...
	 * @param Varien_Simplexml_Element $config
	 * @param array | null $fields
	 * @param string | null $module
	 */
	protected function _translateConfigRecursively($config, $fields = null, $module = null) {
		if ($fields && in_array($config->getName(), $fields)) {
			$name = $config->getName();
			$parent = $config->getParent();
			$value = (string)$config;
			$moduleName = $module ? $module : $this->_getModuleName();
			$parent->$name = Mage::app()->getTranslator()->translate(array(new Mage_Core_Model_Translate_Expr($value, $moduleName)));
		}
		$fields = isset($config['translate']) ? explode(',', (string)$config['translate']) : null;
		$module = isset($config['module']) ? (string)$config['module'] : null;
		foreach ($config->children() as $key => $value) {
			$this->_translateConfigRecursively($value, $fields, $module);
		}
	}
    public function mergeConfig($mergeToObject, $extensions) {
        foreach ($extensions as $extension) {
                if ($extension) {
                	$mergeModel = new Mage_Core_Model_Config_Base;
                	if ($mergeModel->loadString($extension)) {
                    	$mergeToObject->extend($mergeModel->getNode(), true);
                	}
                }
        }
        return $mergeToObject;
    }
    public function getSortedXmlChildren($parent, $child, $select = '', $filter = array(), $defaultSortOrder = 0) {
		$sortedResult = array();
		$result = array();
		if ($parent && isset($parent->$child)) {
			foreach ($parent->$child->children() as $key => $options) {
				if ($this->_doesXmlConformsFilter($options, $filter)) {
					$sortOrder = isset($options->sort_order) ? (int)(string)$options->sort_order : $defaultSortOrder; 
					if ($sortOrder != 0) {
						if (!isset($sortedResult[$sortOrder])) $sortedResult[$sortOrder] = array();
						$sortedResult[$sortOrder][] = $key;
					}
					else {
						$result[] = $key;
					}
				}
			}
			ksort($sortedResult);
			$mergedResult = array();
			foreach ($sortedResult as $prioritizedResult) {
				$mergedResult = array_merge($mergedResult, $prioritizedResult);
			}
			$result = array_merge($mergedResult, $result);
		}

		$selectedResult = array();
		if ($select) {
			foreach ($result as $key) {
				$selectedResult[$key] = (string)$parent->$child->$key->$select;
			}
		}
		else {
			foreach ($result as $key) {
				$selectedResult[$key] = $parent->$child->$key;
			}
		}
		return $selectedResult;
    }
	public function arrayFind($array, $column, $value)
	{
		foreach ($array as $index => $item) {
			if ($item[$column] == $value) {
				return $index;
			}
		}
		return false;
	}
	public function collectionFind($collection, $column, $value)
	{
		$method = 'get'.$this->pascalCased($column);
		foreach ($collection as $item) {
			if ($item->$method() == $value) {
				return $item;
			}
		}
		return false;
	}
	public function countXmlChildren($xml, $filter = array()) {
		$result = 0;
		foreach ($xml->children() as $child) {
			if ($this->_doesXmlConformsFilter($child, $filter)) {
				$result++;
			}
		}
		return $result;
	}
	protected function _doesXmlConformsFilter($xml, $filter) {
		foreach ($filter as $field => $value) {
			if (((string) ($xml->$field)) != $value) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Returns rendered additional markup registered by extensions in configuration under $name key 
	 * @param string $name
	 * @param array $parameters
	 * @return string
	 */
	public function getNamedHtml($root, $name, $parameters = array()) {
		$result = '';
		foreach ($this->getSortedXmlChildren(Mage::getConfig()->getNode($root), $name) as $markup) {
			$filename = Mage::getBaseDir('design').DS.
				Mage::getDesign()->getTemplateFilename((string)$markup->template, array('_relative'=>true));
			if (file_exists($filename)) {
        		$result .= $this->_fetchHtml($filename, $parameters);
			}
		}
		return $result;
	}
	protected function _fetchHtml($filename, $parameters) {
        extract ($parameters, EXTR_OVERWRITE);
        ob_start();
        try {
            include $filename;
        } 
        catch (Exception $e) {
            ob_get_clean();
            throw $e;
        }
        return ob_get_clean();
	}
	public function getJsPriceFormat() {
		return $this->formatPrice(0);
	}
	public function formatPrice($price) {
		$store = Mage::app()->getStore();
        if ($store->getCurrentCurrency()) {
            return $store->getCurrentCurrency()->formatPrecision($price, 0, array(), false, false);
        }
        return $price;
	}
    public function getIniByteValue($setting) {
        $val = trim(ini_get($setting));
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }
        public function jsonForceObjectAndEncode($data) {
        return json_encode($this->_forceObjectRecursively($data));
    }
    protected function _forceObjectRecursively($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->_forceObjectRecursively($value);
            }
            return (object)$data;
        }
        elseif(is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->_forceObjectRecursively($value);
            }
            return $data;
        }
        else {
            return $data;
        }
    }
    public function getChildGroupHtml($block, $group) {
        $result = '';
        foreach ($block->getChildGroup($group, 'getChildHtml') as $alias => $html) {
            $result .= $html;
        }
        return $result;
    }
    public function logProfiler($filename) {
        $timers = Varien_Profiler::getTimers();

        Mage::log('--------------------------------------------------', Zend_Log::DEBUG, $filename);
        Mage::log("Code Profiler\tTime\tCnt\tEmalloc\tRealMem", Zend_Log::DEBUG, $filename);
        foreach ($timers as $name => $timer) {
            $sum = Varien_Profiler::fetch($name, 'sum');
            $count = Varien_Profiler::fetch($name, 'count');
            $realmem = Varien_Profiler::fetch($name, 'realmem');
            $emalloc = Varien_Profiler::fetch($name, 'emalloc');
            if ($sum < .0010 && $count < 10 && $emalloc < 10000) {
                continue;
            }
            Mage::log(sprintf("%s\t%s\t%s\t%s\t%s",
                $name, number_format($sum, 4), $count, number_format($emalloc), number_format($realmem)
            ), Zend_Log::DEBUG, $filename);
        }
    }
    public function getRoutePath($routePath = null) {
        $request = Mage::app()->getRequest();
        if ($routePath) {
            $routePath = explode('/', $routePath);
            if (isset($routePath[0]) && $routePath[0] == '*') $routePath[0] = $request->getRouteName();
            if (isset($routePath[1]) && $routePath[1] == '*') $routePath[1] = $request->getControllerName();
            if (isset($routePath[2]) && $routePath[2] == '*') $routePath[2] = $request->getActionName();
            return $routePath[0] . (isset($routePath[1]) ? '/' . $routePath[1] : '') . (isset($routePath[2]) ? '/' . $routePath[2] : '');
        }
        else {
            return $request->getRouteName() . '/' . $request->getControllerName() . '/' . $request->getActionName();
        }
    }
    public function isMageVersionEqualOrGreater($version) {
        $version = explode('.', $version);
        $mageVersion = array_values(Mage::getVersionInfo());
        foreach ($version as $key => $value) {
            if ($key == 1 && Mage::getConfig()->getModuleConfig('Enterprise_Enterprise')->is('active', 'true')) {
                if ((int)$mageVersion[$key] < (int)($value + 5)) {
                    return false;
                }
            }
            else {
                if ((int)$mageVersion[$key] < (int)$value) {
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * @param $input
     * @param array $separators
     * @return array | bool
     */
    public function sanitizeNumber($input, $separators = array()) {
        if (count($separators)) {
            $separator = array_shift($separators);
            if (!is_array($separator)) {
                $separator = array('sep' =>$separator);
            }
            $result = array();
            foreach (explode($separator['sep'], $input) as $value) {
                if (($sanitizedValue = $this->sanitizeNumber($value, $separators)) !== false && $sanitizedValue !== '' &&  $sanitizedValue !== null) {
                    $result[] = $sanitizedValue;
                }
            }
            if (!empty($separator['as_string'])) {
                return implode($separator['sep'], $result);
            }
            else {
                return $result;
            }
        }
        else {
            return is_numeric($input) ? $input : null;
        }
    }
    public function sanitizeRequestNumberParam($paramName, $separators = array()) {
        $param = $this->sanitizeNumber(urldecode(
            preg_replace('/__\d__/', '', Mage::app()->getRequest()->getParam($paramName))),
            $separators);
        if (isset($_GET[$paramName])) {
            if (trim($param) === '' || trim($param) === null) {
                unset($_GET[$paramName]);
            }
            else {
                $_GET[$paramName] = $param;
            }
        } else {
            if (trim($param) === '' || trim($param) === null) {
                Mage::app()->getRequest()->setParam($paramName, null);
            } else {
                Mage::app()->getRequest()->setParam($paramName, $param);
            }
        }
        return $param;
    }
    public function updateRequestParameter($paramName, $newValue, $oldValue) {
        if (isset($_GET[$paramName])) {
            if (trim($newValue) === '' || trim($newValue) === null) {
                if ($paramName == 'no_cache') {
                    Mage::app()->getRequest()->setParam('no_cache', 1);
                }
                unset($_GET[$paramName]);
                $_SERVER['REQUEST_URI'] = str_replace('?'. $paramName .'=' . $oldValue, '', $_SERVER['REQUEST_URI']);
                $_SERVER['REQUEST_URI'] = str_replace('&' . $paramName . '=' . $oldValue, '', $_SERVER['REQUEST_URI']);
                $_SERVER['REQUEST_URI'] = str_replace('&amp;' . $paramName . '=' . $oldValue, '', $_SERVER['REQUEST_URI']);
            } else {
                $_GET[$paramName] = $newValue;
                $_SERVER['REQUEST_URI'] = str_replace('?' . $paramName . '=' . $oldValue, '?' . $paramName . '=' . $newValue, $_SERVER['REQUEST_URI']);
                $_SERVER['REQUEST_URI'] = str_replace('&' . $paramName . '=' . $oldValue, '&' . $paramName . '=' . $newValue, $_SERVER['REQUEST_URI']);
                $_SERVER['REQUEST_URI'] = str_replace('&amp;' . $paramName . '=' . $oldValue, '&' . $paramName . '=' . $newValue, $_SERVER['REQUEST_URI']);
            }
        }
        elseif (isset($_POST[$paramName])) {
            if (trim($newValue) === '' || trim($newValue) === null) {
                unset($_POST[$paramName]);
            } else {
                $_POST[$paramName] = $newValue;
            }
        }
    }

    public function callProtectedMethod($callback) {
        $args = func_get_args();
        $callback = array_shift($args);

        list($object, $methodName) = $callback;
        if (is_string($object)) {
            $className = $object;
            $object = null;
        }
        else {
            $className = get_class($object);
        }

        $class = new ReflectionClass($className);
        $method = $class->getMethod($methodName);
        if (method_exists($method, 'setAccessible')) {
            $method->setAccessible(true);
            return $method->invokeArgs($callback[0], $args);
        }
        else {
            return null;
        }
    }
}