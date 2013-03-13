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
 * Language symbols:
 *
 * STRING ::= { ANY - '{{=' }
 * Language syntax:
 *
 * Text ::= { Formula | String } // no spaces
 * String ::= { ANY } until '{{=' | EOF )
 * Formula ::= '{{=' Expr '}}' // spaces separate tokens
 * Expr ::= AddExpr
 * AddExpr ::= MulExpr ('+' | '-') Expr
 * MulExpr ::= PrimaryExpr ('*' | '/') Expr
 * PrimaryExpr ::= Function | Field | String | Number | '(' Expr ')'
 * Function ::= identifier '(' { Expr ',' } ')'
 * Field ::= [ identifier '.' ] identifier
 */
class Mana_Db_Model_Formula_Parser  {
    const FORMULA_EXPR = 1;
    const NULL_VALUE = 2;
    const STRING_CONSTANT = 3;
    const IDENTIFIER = 4;
    const NUMBER_CONSTANT = 5;

    const TOKEN_OPEN_PAR = 1;
    const TOKEN_CLOSE_PAR = 2;
    const TOKEN_OPEN_FORMULA = 3;
    const TOKEN_CLOSE_FORMULA = 4;
    const TOKEN_IDENTIFIER = 5;
    const TOKEN_DOT = 6;
    const TOKEN_NUMBER = 7;
    const TOKEN_STRING = 8;

    protected $_formula;
    protected $_pos;
    protected $_token;

    /**
     * @param string $formula
     * @return stdClass
     */
    public function parseText($formula) {
        $parts = array();
        $fromPos = 0;
        for ($pos = strpos($formula, '{{='); $pos !== false; $pos = strpos($formula, '{{=', $fromPos + 2)) {
            if ($fromPos < $pos) {
                $parts[] = $this->_parseString(substr($formula, $fromPos, $pos - $fromPos));
            }
            if (($fromPos = strpos($formula, $pos + 3)) !== false) {
                $parts[] = $this->parseFormula(substr($formula, $pos + 3, $fromPos - ($pos + 3)));
            }
            else {
                $this->_error(self::TOKEN_CLOSE_FORMULA);
            }
        }
        if ($fromPos < strlen($formula)) {
            $parts[] = $this->_parseString(substr($formula, $fromPos));
        }
        if (!count($parts)) {
            $parts[] = $this->_parseNull();
        }

        if (count($parts) > 1) {
            $result = new stdClass();
            $result->type = self::FORMULA_EXPR;
            $result->parts = $parts;
            return $result;
        }
        else {
            return $parts[0];
        }
    }

    protected function _parseString($value) {
        $result = new stdClass();
        $result->type = self::STRING_CONSTANT;
        $result->value = $value;

        return $result;
    }

    public function parseFormula($formula) {
        $this->_formula = $formula;
        $this->_pos = 0;
        $this->_scan();

        return $this->_parseExpr();
    }

    protected function _parseNull() {
        $result = new stdClass();
        $result->type = self::NULL_VALUE;

        return $result;
    }

    protected function _parseExpr() {
        return $this->_parseAddExpr();
    }

    protected function _parseAddExpr() {
        $firstOperand = $this->_parseMulExpr();

    }

    protected function _parseMulExpr() {
        $firstOperand = $this->_parsePrimaryExpr();
    }

    protected function _parsePrimaryExpr() {
        if ($this->_token->kind == self::TOKEN_OPEN_PAR) {
            $this->_scan();
            $result = $this->_parseExpr();
            $this->_expect(self::TOKEN_CLOSE_PAR);
            return $result;
        }
        elseif ($this->_token->kind == self::TOKEN_STRING) {
            $result = new stdClass();
            $result->type = self::STRING_CONSTANT;
            $result->value = substr($this->_token->text, 1, strlen($this->_token->text) - 2);

            return $result;
        }
        elseif ($this->_token->kind == self::TOKEN_NUMBER) {
            $result = new stdClass();
            $result->type = self::NUMBER_CONSTANT;
            $result->value = $this->_token->text;

            return $result;
        }
        elseif ($this->_token->kind == self::TOKEN_IDENTIFIER) {
            $identifier = $this->_token->text;
            $this->_scan();
            if ($this->_token->kind == self::TOKEN_DOT) {
                $this->_scan();

                return $this->_parseField($identifier);
            }
            elseif ($this->_token->kind == self::TOKEN_OPEN_PAR) {
                $this->_scan();

                return $this->_parseFunction($identifier);
            }
            else {
                $result = new stdClass();
                $result->type = self::IDENTIFIER;
                $result->value = $identifier;

                return $result;
            }
        }
        else {
            $this->_error(Mage::helper('mana_db')->__('Invalid expression'));
        }
    }

    protected function _parseField($identifier) {
        $this->_expect(self::TOKEN_IDENTIFIER);

    }

    protected function _parseFunction($identifier) {
    }

    protected function _scan() {
    }

    protected function _expect($tokenKind) {
        $this->_scan();
        if ($this->_token->kind != $tokenKind) {
            $this->_error($tokenKind);
        }
    }

    protected function _error($errNo) {
    }

}