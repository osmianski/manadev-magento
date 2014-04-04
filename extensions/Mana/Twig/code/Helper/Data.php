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
    protected $_twig;
    protected $_stringTwig;

    public function renderFromStoreConfig($path, $data) {
        $template = Mage::getStoreConfig($path);
        $cachedFilename = 'config/' . $path . '.twig';

        return $this->renderStringCached($template, $data, $cachedFilename);
    }

    public function renderStringCached($template, $data, $cachedFilename) {
        return $this->getStringTwig()->render($template, $data);
    }

    /**
     * @return Twig_Environment
     */
    public function getTwig() {
        if (!$this->_twig) {
            $this->_twig = $twig = new Twig_Environment(new Twig_Loader_Filesystem(BP), array(
                'debug' => true,
                'autoescape' => false,
            ));
            $twig->addExtension(new Twig_Extension_Debug());
        }
        return $this->_twig;
    }

    /**
     * @return Twig_Environment
     */
    public function getStringTwig() {
        if (!$this->_stringTwig) {
            $this->_stringTwig = $twig = new Twig_Environment(new Twig_Loader_String(), array(
                'debug' => true,
                'autoescape' => false,
            ));
            $twig->addExtension(new Twig_Extension_Debug());
        }

        return $this->_stringTwig;
    }
}