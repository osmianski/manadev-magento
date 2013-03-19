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
class Mana_Db_Model_Formula_Node_FunctionCall extends Mana_Db_Model_Formula_Node  {
    /**
     * @var string
     */
    public $name;
    /**
     * @var Mana_Db_Model_Formula_Node[]
     */
    public $args;
}