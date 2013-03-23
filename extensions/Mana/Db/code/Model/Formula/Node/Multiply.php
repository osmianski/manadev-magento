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
class Mana_Db_Model_Formula_Node_Multiply extends Mana_Db_Model_Formula_Node  {
    const MULTIPLY = Mana_Db_Helper_Formula_Parser::MULTIPLY;
    const DIVIDE = Mana_Db_Helper_Formula_Parser::DIVIDE;

    /**
     * @var int
     */
    public $operator;
    /**
     * @var Mana_Db_Model_Formula_Node
     */
    public $a;
    /**
     * @var Mana_Db_Model_Formula_Node
     */
    public $b;
}