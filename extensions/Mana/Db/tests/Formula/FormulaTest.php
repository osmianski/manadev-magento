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
class Mana_Db_Test_Formula_FormulaTest extends PHPUnit_Framework_TestCase {
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
                'title' => array('{{= global.title }}', "IF (`p`.`default_mask0` & 2 = 2, `p`.`title`, `g`.`title`)")
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
                'title' => array('{{= COUNT(global.primary.attribute.frontend_label) }}',
                    "IF (`p`.`default_mask0` & 2 = 2, `p`.`title`, ".
                        "CONCAT((SELECT COUNT(`p2a`.`frontend_label`)".
                        " FROM `eav_attribute` AS `p2a`\n".
                        " INNER JOIN `m_attribute_page_attribute` AS `p2p` ON `p2a`.`attribute_id` = `p2p`.`attribute_id`".
                        " WHERE (`p2`.`id` = `p2p`.`page_id`)".
                        " ORDER BY `p2p`.`position` ASC)))")
            ),
            // SQL joined tables
            array(
                'primary' => array('m_attribute_page_store', "`p`.`global_id` = `g`.`id` AND `p`.`store_id` = `s`.`store_id`"), // p
                'global' => 'm_attribute_page_flat', // g
            )
        );
        $this->assertFormulasSelect('mana_attributepage/page/flat',
            // formulas and SQL column expressions
            array(
                'title' => array(
                    '{{= COUNT(attribute.frontend_label) }}',
                    "IF (`p`.`default_mask0` & 2 = 2, `p`.`title`,".
                        " CONCAT((SELECT COUNT(`pa`.`frontend_label`)".
                        " FROM `eav_attribute` AS `pa`\n".
                        " INNER JOIN `m_attribute_page_attribute` AS `pp` ON `pa`.`attribute_id` = `pp`.`attribute_id`".
                        " WHERE (`p`.`id` = `pp`.`page_id`)".
                        " ORDER BY `pp`.`position` ASC)))"
                )
            ),
            // SQL joined tables
            array(
                'primary' => 'm_attribute_page', // p
            )
        );
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


    protected function assertFormulasSelect($targetEntity, $columns, $expectedFrom, $options = array()) {
        $options = array_merge(array(
            'provide_field_details_in_exceptions' => true,
            'process_all_fields' => false,
        ), $options);

        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $formulas = array();
        $expectedColumns = array();
        foreach ($columns as $k => $v) {
            $formulas[$k] = $v[0];
            $expectedColumns[$k] = $v[1];
        }

        try {
            $context = $formulaHelper->select($targetEntity, $formulas, $options);
        }
        catch (Exception $e) {
            foreach ($expectedColumns as $column) {
                if ($column instanceof Exception) {
                    $this->assertEquals(get_class($e), get_class($column));
                }
                else {
                    throw $e;
                }
            }
            return;
        }
        if ($expectedColumns) {
            foreach ($context->getSelect()->getPart(Varien_Db_Select::COLUMNS) as $column) {
                if (isset($expectedColumns[$column[2]])) {
                    if (is_string($expectedColumns[$column[2]])) {
                        $this->assertEquals($expectedColumns[$column[2]], $column[1]->__toString());
                    }
                    else {
                        $this->assertFalse($expectedColumns[$column[2]] instanceof Exception,
                            sprintf("'%s' exception expected, but '%s' expression returned instead",
                            get_class($expectedColumns[$column[2]]), $column[1]->__toString()));
                    }
                    unset($expectedColumns[$column[2]]);
                }
            }
            $this->assertEmpty($expectedColumns, 'Some column expressions not prepared.');
        }
        if ($expectedFrom) {
            $from = $context->getSelect()->getPart(Varien_Db_Select::FROM);
            foreach ($expectedFrom as $alias => $expected) {
                $a = $context->registerAlias($alias);
                $this->assertArrayHasKey($a, $from);
                if (is_array($expected)) {
                    $this->assertEquals($expected[0], $from[$a]['tableName']);
                    $this->assertEquals($expected[1], $from[$a]['joinCondition']);
                }
                else {
                    $this->assertEquals($expected, $from[$a]['tableName']);
                    $this->assertNull($from[$a]['joinCondition']);
                }
            }
        }
    }
}