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
class Mana_Db_Test_Formula_BasicTest extends Mana_Db_Test_Case {
    public function testForeignField() {
        $this->assertFormulasSelect('mana_attributepage/page/store_flat',
            // formulas and SQL column expressions
            array(
                'primary_global_id' => array('{{= global.primary.id }}', "`p2`.`id`"),
                'primary_id' => array('{{= primary.id }}', "`p`.`id`"),
            ),
            // SQL joined tables
            array(
                'primary' => array('m_attribute_page_store', "`p`.`global_id` = `g`.`id` AND `p`.`store_id` = `s`.`store_id`"), // p
                'global' => 'm_attribute_page_flat', // g
                'global.primary' => array('m_attribute_page', "`p2`.`id` = `g`.`primary_id`"), // p2
            )
        );
    }

    public function testThisField() {
        $this->assertFormulasSelect('mana_attributepage/page/store_flat',
            // formulas and SQL column expressions
            array(
                'primary_global_id' => array('{{= primary_id }}', "`p`.`id`"),
                'global_id' => array('{{= this.primary_id }}', "`p`.`id`"),
                'primary_id' => array('{{= this.primary.id }}', "`p`.`id`"),
            ),
            // SQL joined tables
            array(
                'primary' => array('m_attribute_page_store', "`p`.`global_id` = `g`.`id` AND `p`.`store_id` = `s`.`store_id`"), // p
            )
        );
    }

    public function testOverriddenValue() {
        $this->assertFormulasSelect('mana_attributepage/page/store_flat',
            // formulas and SQL column expressions
            array(
                'title' => array('{{= global.title }}', "IF (`p`.`default_mask0` & 2 = 2, `p`.`title`, `g`.`title`)"),
            ),
            // SQL joined tables
            array(
                'primary' => array('m_attribute_page_store', "`p`.`global_id` = `g`.`id` AND `p`.`store_id` = `s`.`store_id`"), // p
                'global' => 'm_attribute_page_flat', // g
            )
        );
    }

    public function testAggregateField() {
        $this->assertFormulasSelect('mana_attributepage/page/store_flat',
            // formulas and SQL column expressions
            array(
                'title' => array('{{= global.primary.attribute.frontend_label }}', new Mana_Db_Exception_Formula())
            ),
            // SQL joined tables
            array(
                'primary' => array('m_attribute_page_store', "`p`.`global_id` = `g`.`id` AND `p`.`store_id` = `s`.`store_id`"), // p
                'global' => 'm_attribute_page_flat', // g
            )
        );
    }

    public function testAggregateCount() {
        $this->assertFormulasSelect('mana_attributepage/page/store_flat',
            // formulas and SQL column expressions
            array(
                'title' => array('{{= COUNT(global.attribute.frontend_label) }}',
                    "IF (`p`.`default_mask0` & 2 = 2, `p`.`title`,".
                        " CONCAT(IF (`a`.`frontend_label` IS NULL, 0, 1) +".
                        " IF (`a2`.`frontend_label` IS NULL, 0, 1) +".
                        " IF (`a3`.`frontend_label` IS NULL, 0, 1) +".
                        " IF (`a4`.`frontend_label` IS NULL, 0, 1) +".
                        " IF (`a5`.`frontend_label` IS NULL, 0, 1)))")
            ),
            // SQL joined tables
            array(
                'primary' => array('m_attribute_page_store', "`p`.`global_id` = `g`.`id` AND `p`.`store_id` = `s`.`store_id`"), // p
                'global' => 'm_attribute_page_flat', // g
                'global.attribute0' => array('eav_attribute', "`a`.`attribute_id` = `g`.`attribute_id_0`"), // a
                'global.attribute1' => array('eav_attribute', "`a2`.`attribute_id` = `g`.`attribute_id_1`"), // a2
                'global.attribute2' => array('eav_attribute', "`a3`.`attribute_id` = `g`.`attribute_id_2`"), // a3
                'global.attribute3' => array('eav_attribute', "`a4`.`attribute_id` = `g`.`attribute_id_3`"), // a4
                'global.attribute4' => array('eav_attribute', "`a5`.`attribute_id` = `g`.`attribute_id_4`"), // a5
            )
        );
//        $this->assertFormulasSelect('mana_attributepage/page/flat',
//            // formulas and SQL column expressions
//            array(
//                'title' => array(
//                    '{{= COUNT(attribute.frontend_label) }}',
//                    "IF (`p`.`default_mask0` & 2 = 2, `p`.`title`,".
//                        " CONCAT((SELECT COUNT(`pa`.`frontend_label`)".
//                        " FROM `eav_attribute` AS `pa`\n".
//                        " INNER JOIN `m_attribute_page_attribute` AS `pp` ON `pa`.`attribute_id` = `pp`.`attribute_id`".
//                        " WHERE (`p`.`id` = `pp`.`page_id`)".
//                        " ORDER BY `pp`.`position` ASC)))"
//                )
//            ),
//            // SQL joined tables
//            array(
//                'primary' => 'm_attribute_page', // p
//            )
//        );
    }

    public function testFrontendField() {
        $this->assertFormulasSelect('mana_attributepage/page/store_flat',
            // formulas and SQL column expressions
            array(
                'title' => array('{{= category.name }}', "IF (`p`.`default_mask0` & 2 = 2, `p`.`title`, '{{= category.name }}')")
            ),
            // SQL joined tables
            array(
                'primary' => array('m_attribute_page_store', "`p`.`global_id` = `g`.`id` AND `p`.`store_id` = `s`.`store_id`"), // p
            )
        );
    }


}