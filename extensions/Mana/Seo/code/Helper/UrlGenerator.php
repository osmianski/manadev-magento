<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Helper_UrlGenerator extends Mage_Core_Helper_Abstract {
    /**
     * @param string $route
     * @param array $params
     * @param Mana_Seo_Rewrite_Url $url
     * @return string
     */
    public function generateAndValidateUrl($route, $params, $url) {
        $magentoUrl = $url->getMagentoUrl($route, $params);
        $result = $this->generateUrl($route, $magentoUrl);
        $result = preg_replace('#\/[-_\w\d]+\/\.#', '.', $result);
        if (strpos($result, '/.html') !== false) {
            $currentUrl = $url->getMagentoUrl('*/*/*', array('_current' => true, '_use_rewrite' => true, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()));
            Mage::log("Wrong URL {$result} on page {$currentUrl}", Zend_Log::DEBUG, 'seo_errors.log');
            try {
                throw new Exception();
            } catch (Exception $e) {
                Mage::log("{$e->getMessage()}\n{$e->getTraceAsString()}", Zend_Log::DEBUG, 'seo_errors.log');
            }
        }

        return $result;

    }

    public function generateUrl($route, $magentoUrl) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $route = $this->_populateRoute($route);


        return $magentoUrl;
    }

    protected function _populateRoute($route) {
        $request = Mage::app()->getRequest();
        $route = explode('/', $route);
        if (isset($route[0]) && $route[0] == '*') $route[0] = $request->getRouteName();
        if (isset($route[1]) && $route[1] == '*') $route[1] = $request->getControllerName();
        if (isset($route[2]) && $route[2] == '*') $route[2] = $request->getActionName();
        return $route[0] . (isset($route[1]) ? '/' . $route[1] : '') . (isset($route[2]) ? '/' . $route[2] : '');
    }
}