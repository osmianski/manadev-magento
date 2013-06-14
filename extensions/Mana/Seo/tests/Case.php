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
class Mana_Seo_Test_Case extends Mana_Core_Test_Case {
    public function assertParsedUrl($path, $expected) {
        /* @var $parser Mana_Seo_Helper_UrlParser */
        $parser = Mage::helper('mana_seo/urlParser');
        $result = $parser->parse($path);

        if (empty($expected)) {
            $this->assertFalse($result, sprintf("Failed asserting that URL '%s' would result in page is not found", $path));
        }
        else {
            $this->assertNotEmpty($result, sprintf("Failed asserting that URL '%s' would be parsed", $path));
            if ($result) {
                foreach ($expected as $key => $expectedValue) {
                    if ($key != 'params') {
                        $this->assertEquals($expectedValue, $result->getData($key));
                    }
                }
                if (isset($expected['params'])) {
                    foreach ($expected['params'] as $key => $value) {
                        $this->assertArrayHasKey($key, $result->getParameters());
                        $this->assertEquals($value, implode('_', $result->getParameter($key)));
                    }
                }
            }
        }
        return $result;
    }

    protected function assertGeneratedCategoryUrl($expected, $query = '') {
        $params = array(
            '__route' => 'catalog/category/view',
            '_direct' => 'apparel.html',
        );
        $this->assertGeneratedUrl($expected, $params, $query);
    }

    protected function assertGeneratedUrl($expected, $route, $params) {
        $url = Mage::getUrl($route, array_merge(array(
                '_m_escape' => '',
                '_use_rewrite' => true,
                '_nosid' => true,
            )
            , $params));
        $relativeUrl = substr($url, strlen(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)));
        $this->assertEquals($expected, $relativeUrl);
    }

    protected function _setAttributeUrlFilterDisplay($attributeCode, $display) {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Seo_Resource_Url_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/url_collection');
        $collection
            ->addTypeFilter(Mana_Seo_Model_ParsedUrl::PARAMETER_ATTRIBUTE)
            ->addFieldToFilter('internal_name', $attributeCode)
            ->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Url::STATUS_ACTIVE,
                    Mana_Seo_Model_Url::STATUS_OBSOLETE
                )
            ));
        foreach ($collection as $url) {
            /* @var $url Mana_Seo_Model_Url */
            $url->setFilterDisplay($display)->save();
        }

        /* @var $parser Mana_Seo_Helper_UrlParser */
        $parser = Mage::helper('mana_seo/urlParser');
        $parser->clearParameterUrlCache();

        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');
        $seo->clearParameterUrlCache();
    }
}