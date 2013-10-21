<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class Mana_Core_Model_Observer {
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Add layout handles from config.xml files (handles event "controller_action_layout_load_before")
	 * @param Varien_Event_Observer $observer
	 */
	public function addLayoutHandles($observer) {
		/* @var $action Mage_Core_Controller_Varien_Action */ $action = $observer->getEvent()->getAction();
		/* @var $layout Mage_Core_Model_Layout */ $layout = $observer->getEvent()->getLayout();

		if (Mage::getConfig()->getNode('m_layout')) {
			foreach (Mage::getConfig()->getNode('m_layout')->children() as $name => $config) {
				if (in_array($name, $layout->getUpdate()->getHandles())) {
					foreach ($config->children() as $action => $actionConfig) {
						if (isset($actionConfig['if'])) {
							$method = (string) $actionConfig['if'];
							$args = array();
							foreach ($actionConfig->children() as $arg) {
								$args[] = (string) $arg;
							}
							$visible = call_user_func_array(array($this, $method), $args);
						}
						else {
							$visible = true;
						}
						if ($visible) {
							$method = (string) $actionConfig['action'];
							$this->$method($layout, $name, $actionConfig);
						}
					}
				}
			}
		}

	}
	/**
	 * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_generate_xml_before")
	 * @param Varien_Event_Observer $observer
	 */
	public function loadBlockLayouts($observer) {
        /* @var $action Mage_Core_Controller_Varien_Action */
        $action = $observer->getEvent()->getAction();
        /* @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getLayout();

        // load javascript and css files if certain blocks are present
        if ($node = Mage::getConfig()->getNode('m_block_layout_handle')) {
            foreach ($node->children() as $ruleName => $ruleConfig) {
                $if = $ruleConfig->if;
                $handleName = (string)$ruleConfig->load_handle;
                if ($type = (string)$if['type']) {
                    if ($this->_hasBlockInXml($type, $layout)) {
                        $layout->getUpdate()->fetchPackageLayoutUpdates((string)$handleName);
                    }
                }
            }
        }
    }

    /**
     * @param string $blockType
     * @param Mage_Core_Model_Layout $layout
     * @return bool
     */
    protected function _hasBlockInXml($blockType, $layout) {
        /* @var $xml Mage_Core_Model_Layout_Element */
        $xml = $layout->getUpdate()->asSimplexml();
        return $blocks = $xml->xpath("//block[@type='{$blockType}']");
    }

	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * After blocks are generated change their properties (handles event "controller_action_layout_generate_blocks_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function postProcessBlocks($observer) {
		/* @var $action Mage_Core_Controller_Varien_Action */ $action = $observer->getEvent()->getAction();
		/* @var $layout Mage_Core_Model_Layout */ $layout = $observer->getEvent()->getLayout();

        // additional layout changes initiated by blocks when all their properties and children are already loaded
        Mage::helper('mana_core/layout')->prepareDelayedLayoutBlocks();

        #region Obsolete, currently does nothing as there is no more m_blocks section in config.xml
		if (Mage::getConfig()->getNode('m_blocks')) {
			foreach (Mage::getConfig()->getNode('m_blocks')->children() as $name => $config) {
				if (in_array($name, $layout->getUpdate()->getHandles())) {
					foreach ($config->children() as $action => $actionConfig) {
						if (isset($actionConfig['if'])) {
							$method = (string) $actionConfig['if'];
							$args = array();
							foreach ($actionConfig->children() as $arg) {
								$args[] = (string) $arg;
							}
							$visible = call_user_func_array(array($this, $method), $args);
						}
						else {
							$visible = true;
						}
						if ($visible) {
							foreach ($this->_findBlocks($layout, $actionConfig) as $block) {
								$method = (string) $actionConfig['action'];
								$this->$method($block, $actionConfig);
							}
						}
					}
				}
			}
		}
		#endregion
	}
	
	
	protected function _findBlocks($layout, $actionConfig) {
		$result = array();
		
		if (isset($actionConfig['type'])) {
			$value = $block = Mage::getConfig()->getBlockClassName((string) $actionConfig['type']);
			foreach ($layout->getAllBlocks() as $block) {
				if ($block instanceof $value) {
					$result[] = $block;
				}
			}				
		}
		else {
			throw new Exception('Not implemented');
		}

		return $result;
	}
	
	// CONDITION METHODS
	
	public function flagSet($param) {
		return Mage::getStoreConfigFlag($param);
	} 
	public function flagNotSet($param) {
		return ! Mage::getStoreConfigFlag($param);
	} 
	public function valueEquals($param, $value) {
		return (Mage::getStoreConfig($param) == $value);
	} 
	public function valueNotEquals($param, $value) {
		return (Mage::getStoreConfig($param) != $value);
	} 
	
	// LAYOUT HANDLE METHODS
	
	public function addAfter($layout, $name, $actionConfig) {
		if ($handle = (string)$actionConfig['handle']) {
			$handles = $layout->getUpdate()->getHandles();
			$index = array_search($name, $handles);
			$layout->getUpdate()->resetHandles()->addHandle(array_merge(
				array_slice($handles, 0, $index + 1),
				array($handle),
				$index + 1 < count($handles) ? array_slice($handles, $index + 1) : array()
			));
		}
	}
	
	// BLOCK ACTION METHODS
	
	public function setTemplate($block, $actionConfig) {
		$block->setTemplate((string)$actionConfig['template']);
	}
	/**
	 * Adds css files to header (handles event "core_block_abstract_to_html_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function adhocCss($observer) {
	    /* @var $block Mage_Core_Block_Abstract */ $block = $observer->getEvent()->getBlock();
	    /* @var $transport Varien_Object */ $transport = $observer->getEvent()->getTransport();

	    if ($block->getNameInLayout() == 'head' && ($css = $block->getMCss())) {
	        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
	        $html = '';
	        foreach ($css as $relativeUrl) {
	            if ($files->getFilename($relativeUrl, 'css')) {
	                $html .= '<link rel="stylesheet" type="text/css" href="'.$files->getUrl($relativeUrl, 'css').'" />'."\n";
	            }
	        }
	        if ($html) {
	            $transport->setHtml($transport->getHtml().$html);
	        }
	    }
	}

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "cms_page_render")
     * @param Varien_Event_Observer $observer
     */
    public function handleUpdateInstructions($observer) {
        /* @var $page Mage_Cms_Model_Page */ $page = $observer->getEvent()->getPage();
        /* @var $controllerAction Mage_Core_Controller_Varien_Action */ $controllerAction = $observer->getEvent()->getControllerAction();

        $inRange = Mage::app()->getLocale()->isStoreDateInInterval(null, $page->getCustomThemeFrom(), $page->getCustomThemeTo());
        if ($layoutUpdate = ($page->getCustomLayoutUpdateXml() && $inRange) ? $page->getCustomLayoutUpdateXml() : $page->getLayoutUpdateXml()) {
            $layoutUpdate = '<' . '?xml version="1.0"?' . '><layout>' . $layoutUpdate . '</layout>';
            if ($xml = simplexml_load_string($layoutUpdate, Mage::getConfig()->getModelClassName('core/layout_element'))) {
                foreach ($xml->children() as $child) {
                    if (strtolower($child->getName()) == 'update' && isset($child['handle'])) {
                        $controllerAction->getLayout()->getUpdate()->addHandle((string)$child['handle']);
                    }
                }
            }
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_render_before")
     * @param Varien_Event_Observer $observer
     */
    public function registerThatPageIsBeingRendered($observer) {
        if (!Mage::registry('m_page_is_being_rendered')) {
            Mage::register('m_page_is_being_rendered', true);
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_generate_blocks_before")
     * @param Varien_Event_Observer $observer
     */
    public function processJsCssFiles($observer) {
        /* @var $action Mage_Core_Controller_Varien_Action */
        $action = $observer->getEvent()->getAction();
        /* @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getLayout();

        /* @var $helper Mana_Core_Helper_Js */
        $helper = Mage::helper('mana_core/js');

        $helper->processFiles($action, $layout);
    }

    /**
     * wrap all blocks marked as client side blocks into container elements
     * (handles event "core_block_abstract_to_html_after")
     * @param Varien_Event_Observer $observer
     */
    public function addClientSideBlockMarkup($observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();
        /* @var $transport Varien_Object */
        $transport = $observer->getEvent()->getTransport();

        if ($block->getNameInLayout() == 'head' && ($css = $block->getMCss())) {
            /* @var $files Mana_Core_Helper_Files */
            $files = Mage::helper(strtolower('Mana_Core/Files'));
            $html = '';
            foreach ($css as $relativeUrl) {
                if ($files->getFilename($relativeUrl, 'css')) {
                    $html .= '<link rel="stylesheet" type="text/css" href="' . $files->getUrl($relativeUrl, 'css') . '" />' . "\n";
                }
            }
            if ($html) {
                $transport->setHtml($transport->getHtml() . $html);
            }
        }

        /* @var $js Mana_Core_Helper_Js */
        $js = Mage::helper('mana_core/js');
        $transport->setHtml($js->wrapClientSideBlock($transport->getHtml(), $block));
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "core_block_abstract_to_html_after")
     * @param Varien_Event_Observer $observer
     */
    public function renderRequireSettings($observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();
        /* @var $transport Varien_Object */
        $transport = $observer->getEvent()->getTransport();

        /* @var $js Mana_Core_Helper_Js */
        $js = Mage::helper('mana_core/js');

        if ($block->getNameInLayout() == 'head' && $js->pageContains('require')) {
            $require = $block->getLayout()->createBlock('mana_core/require');
            $html = $transport->getHtml();
            $html = $require->toHtml(). $html;

            // replace original content with wrapped one
            $transport->setHtml($html);
        }
    }

    /**
     * If relevant global flag is raised, renders AJAX content into JSON response instead of typical full-page
     * HTML response (handles event "controller_front_send_response_before")
     * @param Varien_Event_Observer $observer
     */
    public function renderCustomResponse($observer) {
        if ($callback = Mage::registry('m_response_callback')) {
            call_user_func($callback);
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_front_init_before")
     * @param Varien_Event_Observer $observer
     */
    public function registerEarlyRouters($observer) {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getData('front');

        foreach ($this->coreHelper()->getSortedXmlChildren(Mage::getConfig()->getNode('mana_core'), 'routers') as $key => $routerXml) {
            $class = (string)$routerXml->class;
            $front->addRouter($key, new $class);
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_front_send_response_before")
     * @param Varien_Event_Observer $observer
     */
    public function helpSavingInFullPageCache($observer) {
        if ($queryParameters = Mage::registry('m_temporary_query_parameters')) {
            foreach (array_keys($queryParameters) as $queryParameter) {
                if (isset($_GET[$queryParameter])) {
                    unset($_GET[$queryParameter]);
                }
            }
        }
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    #endregion
}

