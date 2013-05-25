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
class Mana_Seo_Test_Url_OptionPageBasedOn_CategoryPageTest extends Mana_Seo_Test_UrlGenerator {
    public function testWithoutParams() {
        $this->assertOptionPageBasedOnCategoryUrl('apparel/red.html');
    }

    public function test3rdPartyParameter() {
        $this->assertCategoryUrl('apparel/red.html?qq=qqq', 'qq=qqq');
    }

    public function testToolbarParameter() {
        $this->assertCategoryUrl('apparel/red/page/2.html', 'p=2');
    }

    public function testPriceFilter() {
        $this->assertCategoryUrl('apparel/red/price/100-200.html', 'price=2,100');
    }

    public function testAttributeFilter() {
        $this->assertCategoryUrl('apparel/red/high-heels.html', 'shoe_type=51');
    }

    public function testAttributeFilterWithMultipleValues() {
        $this->assertCategoryUrl('apparel/red/high-heels-sandal.html', 'shoe_type=51_97');
    }

    public function testAttributeFilterWithUnderlyingOptionPage() {
        $this->assertCategoryUrl('apparel/blue-red.html', 'color=25');
    }

}