<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Db_Test_Formula_RegressionTest extends Mana_Db_Test_Case {
    public function testThatOptionPageOnStoreLevelGetsCorrectFilterConditionWhenAttributePageIsSaved() {
        $this->assertFormulasSelect('mana_attributepage/option_page/store_flat',
            // formulas and SQL column expressions
            array(
                'primary_id' => array('{{= global.attribute_page.primary.id}}', "`p2`.`id`"),
            ),
            // SQL joined tables
            null
        );
    }
}