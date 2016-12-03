<?php
/** 
 * @category    Mana
 * @package     Mana_Twig
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Twig module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Twig_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_contentRuleTwig;
    protected $_verbatimMagentoDirectives;
    protected $_verbatimRegex;

    public function renderFromStoreConfig($path, $data) {
        $template = Mage::getStoreConfig($path);
        $cachedFilename = 'config/' . $path . '.twig';

        return $this->renderContentRule($template, $data, $cachedFilename);
    }

    protected function _getVerbatimMagentoDirectives() {
        if (!$this->_verbatimMagentoDirectives) {
            $result = array();
            if ($directiveXml = Mage::getConfig()->getNode('mana_twig/verbatim_magento_directives')) {
                foreach ($directiveXml->children() as $key => $xml) {
                    $result[] = $key;
                }
            }
            $this->_verbatimMagentoDirectives = $result;
        }
        return $this->_verbatimMagentoDirectives;
    }

    public function renderContentRule($template, $data) {
        if (!$this->_verbatimRegex) {
            $patterns = array(
                '{{depend\s*(.*?)}}(.*?){{\\/depend\s*}}',
                '{{if\s*(.*?)}}(.*?)({{else}}(.*?))?{{\\/if\s*}}'
            );
            if ($directives = implode('|', $this->_getVerbatimMagentoDirectives())) {
                $patterns[] = '{{(' . $directives . ')(.*?)}}';
            }

            $this->_verbatimRegex = '/'. implode('|', $patterns) . '/si';
        }

        return $this->getContentRuleTwig()->render(
            preg_replace($this->_verbatimRegex, '{% verbatim %}$0{% endverbatim %}', $template), $data);
    }

    /**
     * @return Twig_Environment
     */
    public function getContentRuleTwig() {
        if (!$this->_contentRuleTwig) {
            $loaders = array();
            if (Mage::getStoreConfigFlag('mana/twig/allow_loading_from_files')) {
                $loaders[] = new Twig_Loader_Filesystem(BP);
            }
            $loaders[] = new Twig_Loader_String();

            $this->_contentRuleTwig = $twig = new Twig_Environment(new Twig_Loader_Chain($loaders), array(
                'debug' => true,
                'autoescape' => false,
            ));
            $twig->addExtension(new Twig_Extension_Debug());
            $twig->addFunction('remove', new Twig_Function_Function(array(Mage::helper('mana_twig/functions'), 'remove')));
        }

        return $this->_contentRuleTwig;
    }
}