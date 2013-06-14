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
class Mana_Seo_Test_UrlGenerator_PageTypeTest extends Mana_Seo_Test_Case {
    public function testCategoryPage() {
        $this->assertGeneratedUrl('apparel.html', 'catalog/category/view', array(
            'id' => 18,
        ));
    }
}