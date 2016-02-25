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
    protected $_pageTypes;
    /**
     * @var Mage_Catalog_Model_Category
     */
    protected $_rootCategory;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $_actualCurrentStore;

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
    public function jsonForceObjectAndEncode($data, $options = array()) {
        return json_encode($this->_forceObjectRecursively($data, $options));
    }
    protected function _forceObjectRecursively($data, $options = array()) {
        $forceObject = false;
        if (isset($options['force_object'])) {
            $forceObject = $options['force_object'];
            unset($options['force_object']);
        }
        if (is_array($data)) {
            $convert = false;
            foreach ($data as $key => $value) {
                if (!is_numeric($key)) {
                    $convert = true;
                }
                $data[$key] = $this->_forceObjectRecursively($value,
                    $forceObject !== false && isset($forceObject[$key])
                        ? array_merge(array('force_object' => $forceObject[$key]), $options)
                        : $options);
            }
            return $forceObject === true || $convert ? (object)$data : $data;
        }
        elseif(is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->_forceObjectRecursively($value,
                    $forceObject !== false && isset($forceObject[$key])
                        ? array_merge(array('force_object' => $forceObject[$key]), $options)
                        : $options);
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

    public function getRouteParams() {
        $request = Mage::app()->getRequest();

        $result = '';
        foreach ($request->getUserParams() as $key => $value) {
            if (!is_object($value)) {
                $result .= '/' . $key . '/' . $value;
            }
        }
        return $result;
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

    public function setProtectedProperty($object, $propertyName, $value) {
        $className = get_class($object);
        $class = new ReflectionClass($className);
        $property = $class->getProperty($propertyName);
        if (method_exists($property, 'setAccessible')) {
            $property->setAccessible(true);
            $property->setValue($object, $value);
        }
    }


    public function base64EncodeUrl($url) {
        return base64_encode(Mage::getSingleton('core/url')->sessionUrlVar($url));
    }

    public function inAdmin() {
        return $this->getRequestModule(Mage::app()->getRequest()) ==
            ((string)Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName'));
    }

    public function getRequestModule(Zend_Controller_Request_Http $request) {
        $p = $this->getExplodedPath($request);
        if ($request->getModuleName()) {
            $result = $request->getModuleName();
        }
        else {
            $result = $p[0];
        }

        return $result;
    }

    public function getRequestController(Zend_Controller_Request_Http $request) {
        $p = $this->getExplodedPath($request);
        if ($request->getControllerName()) {
            $result = $request->getControllerName();
        }
        else {
            $result = $p[1];
        }

        return $result;
    }

    public function getRequestAction(Zend_Controller_Request_Http $request) {
        $p = $this->getExplodedPath($request);
        if ($request->getActionName()) {
            $result = $request->getActionName();
        }
        else {
            $result = $p[2];
        }

        return $result;
    }

    public function getExplodedPath(Zend_Controller_Request_Http $request) {
        $defaultPath = array(
            !empty($defaultPath[0]) ? $defaultPath[0] : '',
            !empty($defaultPath[1]) ? $defaultPath[1] : 'index',
            !empty($defaultPath[2]) ? $defaultPath[2] : 'index'
        );

        $path = trim($request->getPathInfo(), '/');

        if ($path) {
            $path = explode('/', $path);
        }
        else {
            $path = $defaultPath;
        }

        return $path;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     */
    public function getBlockAlias($block) {
        if (($parent = $block->getParentBlock()) && $this->startsWith($block->getNameInLayout(), $parent->getNameInLayout().'.')) {
            return substr($block->getNameInLayout(), strlen($parent->getNameInLayout() . '.'));
        }
        else {
            return $block->getNameInLayout();
        }
    }

    public function indexArray($source, $key) {
        $result = array();
        foreach ($source as $value) {
            $result[$value[$key]] = $value;
        }

        return $result;
    }

    protected $_attributes = array();

    public function getAttribute($entityType, $attributeCode, $columns) {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');
        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $res->getConnection('core_read');

        $key = $entityType . '-' . $attributeCode . '-' . implode('-', $columns);
        if (!isset($this->_attributes[$key])) {
            $this->_attributes[$key] = $db->fetchRow($db->select()
                ->from(array('a' => $res->getTableName('eav_attribute')), $columns)
                ->join(array('t' => $res->getTableName('eav_entity_type')), 't.entity_type_id = a.entity_type_id', null)
                ->where('a.attribute_code = ?', $attributeCode)
                ->where('t.entity_type_code = ?', $entityType));
        }

        return $this->_attributes[$key];
    }

    public function getAttributeTable($attribute, $baseTable = 'catalog_category_entity') {
        return $attribute['backend_table'] ?
            $attribute['backend_table'] :
            Mage::getSingleton('core/resource')->getTableName($baseTable . '_' . $attribute['backend_type']);
    }

    /**
     * @param Varien_Db_Adapter_Pdo_Mysql $connection
     * @param $tableName
     * @param array $fields
     * @param bool $onDuplicate
     * @return string
     */
    public function insert($connection, $tableName, $fields = array(), $onDuplicate = true) {
        $sql = "INSERT INTO `{$tableName}` ";
        $sql .= "(`" . implode('`,`', array_keys($fields)) . "`) ";
        $sql .= "VALUES (" . implode(',', $fields) . ") ";

        if ($onDuplicate && $fields) {
            $sql .= " ON DUPLICATE KEY UPDATE";
            $updateFields = array();
            foreach ($fields as $key => $field) {
                $key = $connection->quoteIdentifier($key);
                $updateFields[] = "{$key}=VALUES({$key})";
            }
            $sql .= " " . implode(', ', $updateFields);
        }

        return $sql;
    }

    public function addDotToSuffix($suffix) {
        if ($suffix && $suffix != '/' && strpos($suffix, '.') !== 0) {
            $suffix = '.' . $suffix;
        }

        return $suffix;
    }

    /**
     * @param string $helper
     * @return Mana_Core_Helper_PageType[]
     */
    public function getPageTypes($helper = 'helper') {
        if (!isset($this->_pageTypes[$helper])) {
            $result = array();

            foreach ($this->getSortedXmlChildren(Mage::getConfig()->getNode('mana_core'), 'page_types') as $key => $pageTypeXml) {
                /* @var $pageType Mana_Seo_Helper_PageType */
                $pageType = Mage::helper((string)$pageTypeXml->$helper);
                $pageType->setCode($key);
                $result[$key] = $pageType;
            }
            $this->_pageTypes[$helper] = $result;
        }

        return $this->_pageTypes[$helper];
    }

    /**
     * @param string $type
     * @param string $helper
     * @return Mana_Core_Helper_PageType
     */
    public function getPageType($type, $helper = 'helper') {
        $pageTypes = $this->getPageTypes($helper);

        return $pageTypes[$type];
    }

    /**
     * @param string $helper
     * @return Mana_Core_Helper_PageType|null
     */
    public function getPageTypeByRoutePath($routePath = null, $helper = 'helper') {
        foreach ($this->getPageTypes($helper) as $pageType) {
            if ($pageType->getRoutePath() == $this->getRoutePath($routePath)) {
                return $pageType;
            }
        }
        return null;
    }

    public function isManadevLayeredNavigationInstalled() {
        return $this->isModuleEnabled('Mana_Filters');
    }

    public function isManadevPaidLayeredNavigationInstalled() {
        return $this->isModuleEnabled('ManaPro_FilterAdmin');
    }

    public function isManadevLayeredNavigationCheckboxesInstalled() {
        return $this->isModuleEnabled('ManaPro_FilterCheckboxes');
    }

    public function isManadevSeoLayeredNavigationInstalled() {
        return $this->isModuleEnabled('ManaPro_FilterSeoLinks');
    }

    public function isManadevSeoInstalled() {
        return $this->isModuleEnabled('Mana_Seo');
    }

    public function isManadevAttributePageInstalled() {
        return $this->isModuleEnabled('Mana_AttributePage');
    }

    public function isManadevSortingInstalled() {
        return $this->isModuleEnabled('Mana_Sorting');
    }

    public function isManadevLayeredNavigationTreeInstalled() {
        return $this->isModuleEnabled('ManaPro_FilterTree');
    }

    public function isManadevLayeredNavigationColorInstalled()
    {
        return $this->isModuleEnabled('ManaPro_FilterColors');
    }

    public function isManadevDependentFilterInstalled()
    {
        return $this->isModuleEnabled('ManaPro_FilterDependent');
    }

    public function isEnterpriseUrlRewriteInstalled() {
        return $this->isModuleEnabled('Enterprise_UrlRewrite');
    }

    public function isSpecialPagesInstalled() {
        return $this->isModuleEnabled('Mana_Page');
    }

    public function isManadevCMSProInstalled() {
        return $this->isModuleEnabled('ManaPro_Content');
    }

    public function isManadevCMSInstalled() {
        return $this->isModuleEnabled('Mana_Content');
    }

    public function isManadevManySKUInstalled() {
        return $this->isModuleEnabled('ManaPro_ProductFaces');
    }

    protected $_accentTranslations = array(
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ý' => 'y',
        'ÿ' => 'y',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
    );

    protected $_accentTranslationsOld = array(
        'from' => 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
        'to' =>   'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
    );

    public function unaccent($s) {
        return strtr($s, $this->_accentTranslations);
    }

    public function getEmptyBlockHtml($block) {
        Mage::dispatchEvent('core_block_abstract_to_html_before', array('block' => $block));
        if (Mage::getStoreConfig('advanced/modules_disable_output/' . $block->getModuleName())) {
            return '';
        }

        /**
         * Use single transport object instance for all blocks
         */
        $transportObject = new Varien_Object;
        $transportObject->setHtml('');
        Mage::dispatchEvent('core_block_abstract_to_html_after', array('block' => $block, 'transport' => $transportObject));
        $html = $transportObject->getHtml();

        return $html;
    }
    public function getOptionArray($allOptions)
    {
        $_options = array();
        foreach ($allOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * @return string[]
     */
    public function getProductToolbarParameters() {
        $result = array();
        $request = Mage::app()->getRequest();
        foreach (array('p', 'mode', 'order', 'dir', 'limit') as $key) {
            if ($value = $request->getParam($key)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function initLayoutMessages($messagesStorage) {
        if (!is_array($messagesStorage)) {
            $messagesStorage = array($messagesStorage);
        }
        $layout = Mage::getSingleton('core/layout');
        foreach ($messagesStorage as $storageName) {
            $storage = Mage::getSingleton($storageName);
            if ($storage) {
                $block = $layout->getMessagesBlock();
                $block->addMessages($storage->getMessages(true));
                $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
                $block->addStorageType($storageName);
            } else {
                Mage::throwException(
                    Mage::helper('core')->__('Invalid messages storage "%s" for layout messages initialization', (string)$storageName)
                );
            }
        }

        return $this;
    }

    public function getRootCategory() {
        if (!$this->_rootCategory) {
            $this->_rootCategory = Mage::getModel('catalog/category');
            $this->_rootCategory
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load(Mage::app()->getStore()->getRootCategoryId());

        }
        return $this->_rootCategory;
    }

    public function setCurrentStore($store) {
        if (!$this->_actualCurrentStore) {
            $this->_actualCurrentStore = Mage::app()->getStore();
        }

        Mage::app()->setCurrentStore(Mage::app()->getStore($store));
    }

    public function restoreCurrentStore() {
        if ($this->_actualCurrentStore) {
            Mage::app()->setCurrentStore($this->_actualCurrentStore);
            $this->_actualCurrentStore = null;
        }
    }
}