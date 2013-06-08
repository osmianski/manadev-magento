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
}