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
class Mana_Db_Test_Case extends Mana_Core_Test_Case {
    protected function assertFormulasSelect($targetEntity, $columns, $expectedFrom, $options = array()) {
        $options = array_merge(
            array(
                'provide_field_details_in_exceptions' => true,
                'process_all_fields' => false,
            ),
            $options
        );

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
        } catch (Exception $e) {
            foreach ($expectedColumns as $column) {
                if ($column instanceof Exception) {
                    $this->assertEquals(get_class($e), get_class($column));
                } else {
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
                    } else {
                        $this->assertFalse(
                            $expectedColumns[$column[2]] instanceof Exception,
                            sprintf(
                                "'%s' exception expected, but '%s' expression returned instead",
                                get_class($expectedColumns[$column[2]]),
                                $column[1]->__toString()
                            )
                        );
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
                } else {
                    $this->assertEquals($expected, $from[$a]['tableName']);
                    $this->assertNull($from[$a]['joinCondition']);
                }
            }
        }
    }
}