<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Contains methods which makes javascript intensive programming easier.
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Js extends Mage_Core_Helper_Abstract {
    #region Dynamic JS and CSS file inclusion
    protected $_fileConfig;
    protected $_moduleIndex = array();
    protected $_tagNames = array('block', 'reference');
    protected $_pathKeys = array('full_path', 'minified_path');
    /**
     * @param Mage_Core_Controller_Varien_Action $action
     * @param Mage_Core_Model_Layout $layout
     */
    public function processFiles($action, $layout) {
        // TODO: refactor phases, find positions, then insert all, then delete all
        Mana_Core_Profiler::start(__METHOD__);

        $layoutXml = $layout->getNode();
        $layoutDom = new DOMDocument('1.0');
        $layoutNode = $layoutDom->importNode(dom_import_simplexml($layoutXml), true);
        $layoutDom->appendChild($layoutNode);
        $layoutXPath = new DOMXPath($layoutDom);
        $layoutModified = false;

        $headBlock = $this->_findHeadBlock($layoutXPath);
        $prototypeAction = $this->_findPrototypeAction($layoutXPath);

        $insertOnDemand = array();
        $insertEverywhere = array();
        $delete = array();

        $onDemandInsertPlace = null;
        $xpath = '';
        foreach ($this->_getFileConfig() as $name => $fileConfig) {
            /* @var $config array */
            /* @var $options array */
            extract($fileConfig);

            if ($xpath) {
                $xpath .= ' | ';
            }
            $xpath .= $this->_getActionsXPath($config);

        }
        foreach ($layoutXPath->query($xpath) as $element) {
            $onDemandInsertPlace = $element;
            break;
        }

        // handle javascript minification, merging and inclusion on all pages
        foreach ($this->_getFileConfig() as $name => $fileConfig) {
            /* @var $config array */
            /* @var $options array */
            extract($fileConfig);

            if (in_array('skip', $options)) {
                continue;
            }

            // find all statically defined actions of including a given script
            $fileActions = $this->_findDomActions($layoutXPath, $config);

            // prepare layout XML action depending on script parameters
            $action = $this->_createConfigurableAction($layoutDom, $config, $options);

            // insert script where and if appropriate
            if (in_array('ondemand', $options)) {
                if ($fileAction = $this->_getFirstElement($fileActions)) {
                    $insertOnDemand[] = array($action, $onDemandInsertPlace);
                }
                $delete[] = $fileActions;
            }
            elseif (in_array('everywhere', $options)) {
                $insertEverywhere[] = $action;
                foreach (array_reverse($insertOnDemand) as $insertOptions) {
                    list($action, $fileAction) = $insertOptions;
                    array_unshift($insertEverywhere, $action);
                }
                $insertOnDemand = array();
                $delete[] = $fileActions;
            }
            elseif (in_array('unload', $options)) {
                $delete[] = $fileActions;
            }
        }

        // insert configurable on demand js files
        foreach ($insertOnDemand as $insertOptions) {
            list($action, $fileAction) = $insertOptions;
            /* @var $fileAction DOMElement */

            $fileAction->parentNode->insertBefore($action, $fileAction);
            $layoutModified = true;
        }

        // insert configurable global js files
        if ($prototypeAction) {
            foreach (array_reverse($insertEverywhere) as $action) {
                $prototypeAction->parentNode->insertBefore($action, $prototypeAction->nextSibling);
                $layoutModified = true;
            }
        }
        elseif ($headBlock) {
            foreach (array_reverse($insertEverywhere) as $action) {
                $headBlock->insertBefore($action, $headBlock->firstChild);
                $layoutModified = true;
            }
        }

        // delete js files includes via layout XML instructions
        foreach ($delete as $fileActions) {
            foreach ($fileActions as $firstFileAction) {
                /* @var $firstFileAction DOMElement */
                $firstFileAction->parentNode->removeChild($firstFileAction);
                $layoutModified = true;
            }
        }

        if ($layoutModified) {
            $layout->loadDom($layoutNode);
        }
        Mana_Core_Profiler::stop(__METHOD__);
    }

    public function pageContains($scriptName) {
        $config = $this->_getFileConfig();
        if (isset($config[$scriptName])) {
            $fileConfig = $config[$scriptName];

            /* @var $config array */
            /* @var $options array */
            extract($fileConfig);

            if (in_array('ondemand', $options) || in_array('skip', $options)) {
                /* @var $layout Mage_Core_Model_Layout */
                $layout = Mage::getSingleton('core/layout');

                return count($this->_findSimpleXmlActions($layout->getNode(), $config)) > 0;
            }
            elseif (in_array('everywhere', $options)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param DOMXPath $layoutXPath
     * @return DOMElement|null
     */
    protected function _findPrototypeAction($layoutXPath) {
        return $this->_getFirstElement($layoutXPath->query("//block[@name='head']/action" .
            "[@method='addJs' and script='prototype/prototype.js']"));
    }

    /**
     * @param DOMXPath $layoutXPath
     * @return DOMElement|null
     */
    protected function _findHeadBlock($layoutXPath) {
        return $this->_getFirstElement($layoutXPath->query("//block[@name='head']"));
    }

    /**
     * @param DOMNodeLIst | DOMNode[] $elements
     * @return DOMElement | null
     */
    protected function _getFirstElement($elements) {
        foreach ($elements as $element) {
            return $element;
        }
        return null;
    }

    protected function _getActionsXPath($config) {
        $xpath = '';
        foreach ($this->_tagNames as $tagName) {
            foreach ($this->_pathKeys as $pathKey) {
                if (!$config[$pathKey]) {
                    continue;
                }

                if ($xpath) $xpath .= ' | ';
                $xpath .= "//{$tagName}[@name='head']/action";
                if ($config['skinnable']) {
                    $xpath .= "[@method='addItem' and type='skin_js' and script='{$config[$pathKey]}']";
                }
                else {
                    $xpath .= "[@method='addJs' and script='{$config[$pathKey]}']";
                }
            }
        }
        return $xpath;
    }

    /**
     * @param DOMXPath $layoutXPath
     * @param array $config
     * @return DOMNode[]
     */
    protected function _findDomActions($layoutXPath, $config) {
        $xpath = $this->_getActionsXPath($config);
        $result = array();
        foreach ($layoutXPath->query($xpath) as $element) {
            $result[] = $element;
        }
        return $result;
    }

    /**
     * @param SimpleXMLElement $layoutSimpleXml
     * @param array $config
     * @return SimpleXMLElement[]
     */
    protected function _findSimpleXmlActions($layoutSimpleXml, $config) {
        $xpath = $this->_getActionsXPath($config);
        return $layoutSimpleXml->xpath($xpath);
    }

    /**
     * @param DOMDocument $layoutDom
     * @param array $config
     * @param array $options
     * @return DOMElement
     */
    protected function _createConfigurableAction($layoutDom, $config, $options) {
        $pathKey = 'full_path';
        if (in_array('min', $options) && !empty($config['minified_path'])) {
            $pathKey = 'minified_path';
        }
        $jsXml = $config['skinnable']
            ? '<action method="addItem"><type>skin_js</type><name>' . $config[$pathKey] . '</name><params/></action>'
            : '<action method="addJs"><script>' . $config[$pathKey] . '</script></action>';
        return $layoutDom->importNode(dom_import_simplexml(simplexml_load_string($jsXml)), true);
    }

    protected function _getFileConfig() {
        if (is_null($this->_fileConfig)) {
            $this->_fileConfig = array();
            $jsConfig = Mage::getConfig()->getNode('mana_core/js');
            $section = Mage::app()->getStore()->isAdmin() ? 'js_admin' : 'js';
            if ($jsConfig) {
                $jsChildren = (array)$jsConfig->children();
                uasort($jsChildren, array($this, '_compareByModuleAndSortOrder'));
                foreach ($jsChildren as $name => $config) {
                    $options = Mage::getStoreConfig("mana/$section/$name");

                    /* @var $config Varien_Simplexml_Element */
                    if (empty($config->full_path) || !$options) {
                        continue;
                    }

                    $options = explode('_', $options);

                    $config = array_merge(array(
                        'type' => 'extension',
                        'skinnable' => false,
                        'full_path' => '',
                        'minified_path' => '',
                        'sort_order' => 1000000,
                    ), $config->asArray());

                    $this->_fileConfig[$name] = compact('config', 'options');
                }
            }
        }
        return $this->_fileConfig;
    }

    public function getSectionSeparator() {
        return "\n91b5970cd70e2353d866806f8003c1cd56646961\n";
    }

    protected function _compareByModuleAndSortOrder($a, $b) {
        if (($result = $this->_compareByModule($a, $b)) != 0) return $result;
        return $this->_compareBySortOrder($a, $b);
    }

    protected function _compareByModule($a, $b) {
        $aIndex = empty($a['module']) ? 1000000 : $this->_getModuleIndex((string)$a['module']);
        $bIndex = empty($b['module']) ? 1000000 : $this->_getModuleIndex((string)$b['module']);
        if ($aIndex < $bIndex) return -1;
        if ($aIndex > $bIndex) return 1;
        return 0;
    }

    protected function _compareBySortOrder($a, $b) {
        $aIndex = empty($a->sort_order) ? 1000000 : ((int)(string)$a->sort_order);
        $bIndex = empty($b->sort_order) ? 1000000 : ((int)(string)$b->sort_order);
        if ($aIndex < $bIndex) return -1;
        if ($aIndex > $bIndex) return 1;

        return 0;
    }

    protected function _getModuleIndex($moduleName) {
        if (!isset($this->_moduleIndex[$moduleName])) {
            $result = 0;
            foreach (Mage::getConfig()->getNode('modules')->children() as $name => $module) {
                if ($name = $moduleName) {
                    break;
                }
                $result++;
            }

            $this->_moduleIndex[$moduleName] = $result;
        }

        return $this->_moduleIndex[$moduleName];
    }
    #endregion
    #region Client side block markup
    public function wrapClientSideBlock($contentHtml, $params = false) {
        $info = $this->parseClientSideBlockInfo($params);

        if ($info['is_enabled'] && !$info['is_self_contained']) {
            return $info['opening_html'].$contentHtml.$info['closing_html'];
        }
        else {
            return $contentHtml;
        }

    }

    /**
     * @param string $blockName
     * @return string
     */
    public function getClientSideBlockName($blockName) {
        return str_replace('.', '-', str_replace('_', '-', $blockName));
    }

    /**
     * @param bool|array|Mage_Core_Block_Abstract $params
     * @return array
     */
    public function parseClientSideBlockInfo($params = false) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        if ($params instanceof Mage_Core_Block_Abstract) {
            $block = $params;
            $params = $block->getMClientSideBlock();
            if (is_array($params) && empty($params['id']) && !$block->getIsAnonymous()) {
                $params['id'] = $this->getClientSideBlockName($block->getNameInLayout());
            }
        }

        $is_enabled = false;
        $is_self_contained = false;
        $element = 'div';
        $class = '';
        $style = '';
        $attributes = array();
        $opening_html = $closing_html = $class_html = $style_html = $attribute_html = '';

        if (is_array($params)) {
            $is_enabled = true;
            if (!empty($params['self_contained'])) {
                $is_self_contained = true;
                unset($params['self_contained']);
            }

            // decide which element to use
            if (!empty($params['element'])) {
                $element = $params['element'];
                unset($params['element']);
            }

            // assign client side block a unique identifier if possible
            $id = '';
            if (!empty($params['id'])) {
                $id = $params['id'];
                unset($params['id']);
            }
            $class = $id ? 'mb-' . $id : 'm-block';
            $style = '';

            // set css properties
            if (isset($params['class'])) {
                $class .= " {$params['class']}";
                unset($params['class']);
            }
            foreach ($params as $key => $value) {
                if ($core->startsWith($key, 'style-')) {
                    $style .= "{$value};";
                    unset($params[$key]);
                }
            }

            // decide on client side block type
            if (empty($params['m-block'])) {
                $type = '';
                if (!empty($params['type'])) {
                    $type = $params['type'];
                    if (in_array($type, array('Mana/Core/Block', 'Mana/Core/NamedBlock'))) {
                        $type = '';
                    }
                    unset($params['type']);
                }
                if ($type) {
                    $params['m-block'] = $type;
                }
            }

            // render client side block element and its contents
            $opening_html = '<' . $element;
            if (isset($params['html'])) {
                foreach ($params['html'] as $key => $value) {
                    $attributes[$key] = $value;
                    if ($attribute_html) $attribute_html .= ' ';
                    $attribute_html .= "$key=\"$value\"";
                }
                unset($params['html']);
            }
            foreach ($params as $key => $value) {
                $attributeKey = 'data-' . str_replace('.', '-', str_replace('_', '-', $key));
                $attributeValue = str_replace('"', '&quot;', str_replace('<', '&lt;', str_replace('>', '&gt;', $value)));
                $attributes[$attributeKey] = $attributeValue;

                if ($attribute_html) $attribute_html .= ' ';
                $attribute_html .= "$attributeKey=\"$attributeValue\"";
            }
            if ($attribute_html) {
                $opening_html .= ' ' . $attribute_html;
            }

            if ($class) {
                $class_html = 'class="' . $class . '"';
                $opening_html .= ' '.$class_html;
            }
            if ($style) {
                $style_html = 'style="' . $style . '"';
                $opening_html .= ' ' . $style_html;
            }
            $opening_html .= '>';
            $closing_html = '</' . $element . '>';

        }

        return compact('is_enabled', 'is_self_contained', 'element', 'class', 'style', 'attributes',
            'opening_html', 'closing_html', 'class_html', 'style_html', 'attribute_html');
    }

    public function setConfig($key, $value) {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton(strtolower('Core/Layout'));
        /* @var $jsBlock Mana_Core_Block_Js */
        $jsBlock = $layout->getBlock('m_js');

        if ($jsBlock) {
            $jsBlock->setConfig($key, $value);
        }

        return $this;
    }

    public function getConfig() {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton(strtolower('Core/Layout'));
        /* @var $jsBlock Mana_Core_Block_Js */
        $jsBlock = $layout->getBlock('m_js');

        if ($jsBlock) {
            return $jsBlock->getConfig();
        }

        return false;
    }

    #endregion
    #region Deprecated API for $.options() and $.__() functions
    /**
	 * Makes translations of specified strings to be available in client-side scripts.
	 * @param array $translations
	 * @return Mana_Core_Helper_Js
	 */
	public function translations($translations) {
		/* @var $layout Mage_Core_Model_Layout */ $layout = Mage::getSingleton(strtolower('Core/Layout'));
		/* @var $jsBlock Mana_Core_Block_Js */ $jsBlock = $layout->getBlock('m_js');
		$jsBlock->translations($translations);
		return $this; 
	}
	/**
	 * Makes options (specified in $options key-value pair array) for HTML element (selected with $selector) 
	 * to be available in client-side scripts. 
	 * @param string $selector
	 * @param array $options
	 * @return Mana_Core_Helper_Js
	 */
	public function options($selector, $options) {
		/* @var $layout Mage_Core_Model_Layout */ $layout = Mage::getSingleton(strtolower('Core/Layout'));
		/* @var $jsBlock Mana_Core_Block_Js */ $jsBlock = $layout->getBlock('m_js');
		$jsBlock->options($selector, $options);
		return $this; 
	}
	public function optionsToHtml($selector, $options) {
		$options = json_encode(array($selector => $options));
		return <<<EOT
<script type="text/javascript"> 
//<![CDATA[
jQuery.options($options);
//]]>
</script> 
EOT;
	}
    #endregion
}