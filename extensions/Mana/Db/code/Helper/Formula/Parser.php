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
class Mana_Db_Helper_Formula_Parser extends Mage_Core_Helper_Abstract {
    const OPEN_PAR = 1;
    const CLOSE_PAR = 2;
    const OPEN_FORMULA = 3;
    const CLOSE_FORMULA = 4;
    const IDENTIFIER = 5;
    const DOT = 6;
    const NUMBER = 7;
    const STRING = 8;
    const COMMA = 9;
    const MULTIPLY = 10;
    const DIVIDE = 11;
    const ADD = 12;
    const SUBTRACT = 13;
    const EOF = 14;

    static $_tokens = array(
        self::OPEN_PAR => '(',
        self::CLOSE_PAR => ')',
        self::OPEN_FORMULA => '{{=',
        self::CLOSE_FORMULA => '}}',
        self::DOT => '.',
        self::COMMA => ',',
        self::MULTIPLY => '*',
        self::DIVIDE => '/',
        self::ADD => '+',
        self::SUBTRACT => '-',
    );
    static $_multiplicationOperators = array(self::MULTIPLY, self::DIVIDE);
    static $_additionOperators = array(self::ADD, self::SUBTRACT);

    static $_numeric = '0123456789';
    static $_firstIdentifierSymbol = '_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    static $_nextIdentifierSymbol = '_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    static $_whitespace = " \t\r\n";
    protected $_formula;
    protected $_pos;
    protected $_token;

    /**
     * @param string $formula
     * @return Mana_Db_Model_Formula_Node
     */
    public function parse($formula) {
        $parts = array();
        $fromPos = 0;
        for ($pos = strpos($formula, self::$_tokens[self::OPEN_FORMULA]); $pos !== false;
            $pos = strpos($formula, self::$_tokens[self::OPEN_FORMULA], $fromPos))
        {
            if ($fromPos < $pos) {
                $parts[] = $this->_parseString(substr($formula, $fromPos, $pos - $fromPos));
            }
            if (($fromPos = strpos($formula, self::$_tokens[self::CLOSE_FORMULA], $pos + strlen(self::$_tokens[self::OPEN_FORMULA]))) !== false) {
                $parts[] = $this->parseFormula(substr($formula, $pos + strlen(self::$_tokens[self::OPEN_FORMULA]), $fromPos - ($pos + 3)));
                $fromPos += strlen(self::$_tokens[self::CLOSE_FORMULA]);
            }
            else {
                $this->_error(self::CLOSE_FORMULA);
            }
        }
        if ($fromPos < strlen($formula)) {
            $parts[] = $this->_parseString(substr($formula, $fromPos));
        }
        if (!count($parts)) {
            $parts[] = $this->_parseNull();
        }

        if (count($parts) > 1) {
            return new Mana_Db_Model_Formula_Node_FormulaExpr(compact('parts'));
        }
        else {
            return $parts[0];
        }
    }

    protected function _parseString($value) {
        return new Mana_Db_Model_Formula_Node_StringConstant(compact('value'));
    }

    public function parseFormula($formula) {
        $this->_formula = $formula;
        $this->_pos = 0;
        $this->_scan();

        return $this->_parseExpr();
    }

    protected function _parseNull() {
        return new Mana_Db_Model_Formula_Node_NullValue();
    }

    protected function _parseExpr() {
        return $this->_parseAddExpr();
    }

    protected function _parseAddExpr() {
        $a = $this->_parseMulExpr();
        $operator = $this->_token->kind;

        if (in_array($operator, self::$_additionOperators)) {
            $this->_scan();
            $b = $this->_parseExpr();
            return new Mana_Db_Model_Formula_Node_Add(compact('operator', 'a', 'b'));
        }
        else {
            return $a;
        }

    }

    protected function _parseMulExpr() {
        $a = $this->_parsePrimaryExpr();
        $operator = $this->_token->kind;

        if (in_array($operator, self::$_multiplicationOperators)) {
            $this->_scan();
            $b = $this->_parseExpr();
            return new Mana_Db_Model_Formula_Node_Multiply(compact('operator', 'a', 'b'));
        }
        else {
            return $a;
        }
    }

    protected function _parsePrimaryExpr() {
        if ($this->_token->kind == self::OPEN_PAR) {
            $this->_scan();
            $result = $this->_parseExpr();
            $this->_expect(self::CLOSE_PAR);
            return $result;
        }
        elseif ($this->_token->kind == self::STRING) {
            $result = new Mana_Db_Model_Formula_Node_StringConstant(array('value' => $this->_token->text));
            $this->_scan();
            return $result;
        }
        elseif ($this->_token->kind == self::NUMBER) {
            $result = new Mana_Db_Model_Formula_Node_NumberConstant(array('value' => $this->_token->text));
            $this->_scan();
            return $result;
        }
        elseif ($this->_token->kind == self::IDENTIFIER) {
            $identifier = $this->_token->text;
            $this->_scan();
            if ($this->_token->kind == self::DOT) {
                return $this->_parseField($identifier);
            }
            elseif ($this->_token->kind == self::OPEN_PAR) {
                return $this->_parseFunction($identifier);
            }
            else {
                return new Mana_Db_Model_Formula_Node_Identifier(compact('identifier'));
            }
        }
        else {
            $this->_error(Mage::helper('mana_db')->__('Invalid expression'));
            return false;
        }
    }

    protected function _parseField($identifier) {
        $identifiers = array($identifier);

        while ($this->_token->kind == self::DOT) {
            $this->_scan();
            $identifiers[] = $this->_token->text;
            $this->_expect(self::IDENTIFIER);
        }

        return new Mana_Db_Model_Formula_Node_Field(compact('identifiers'));
    }

    protected function _parseFunction($name) {
        $args = array();

        $this->_expect(self::OPEN_PAR);

        if ($this->_token->kind != self::CLOSE_PAR) {
            $args[] = $this->_parseExpr();
            while ($this->_token->kind == self::COMMA) {
                $this->_scan();
                $args[] = $this->_parseExpr();
            }
        }

        $this->_expect(self::CLOSE_PAR);

        return new Mana_Db_Model_Formula_Node_FunctionCall(compact('name', 'args'));
    }

    protected function _expect($tokenKind) {
        if ($this->_token->kind != $tokenKind) {
            $this->_error($tokenKind);
        }
        $this->_scan();
    }

    /**
     * @param $errNo
     * @throws Mana_Db_Exception_Formula
     */
    protected function _error($errNo) {
        if (is_string($errNo)) {
            $err = $errNo;
        }
        else {
            $t = Mage::helper('mana_db');
            switch ($errNo) {
                case self::IDENTIFIER: $err = $t->__("Identifier expected"); break;
                case self::NUMBER: $err = $t->__("Number constant expected"); break;
                case self::STRING: $err = $t->__("String constant expected"); break;
                case self::EOF: $err = $t->__("End of formula expected"); break;
                default:
                    if (isset(self::$_tokens[$errNo])) {
                        $err = $t->__("'%s' expected", self::$_tokens[$errNo]);
                    }
                    else {
                        $err = $t->__("Syntax error");
                    }
                    break;
            }
        }

        throw new Mana_Db_Exception_Formula($err);
    }

    protected function _scan() {
        $this->_token = $this->_peek(1);
        $this->_pos = $this->_token->pos + $this->_token->length;
    }

    protected function _peek($count = 1) {
        $token = null;
        for ($pos = $this->_pos, $i = 0; $i < $count; $i++) {
            $token = $this->_internalScan($pos);
            $pos = $token->pos + $token->length;
        }
        return $token;
    }

    protected function _internalScan($pos = false) {
        $kind = 0;
        $text = '';
        $formulaLength = strlen($this->_formula);
        $state = 0;
        $unexpectedEof = false;
        $found = false;

        if ($pos === false) {
            $pos = $this->_pos;
        }
        $whitespacePos = $pos;
        while ($pos < $formulaLength) {
            $ch = substr($this->_formula, $whitespacePos++, 1);
            if (strpos(self::$_whitespace, $ch) !== false) {
                $pos++;
            }
            else {
                break;
            }
        }
        $newPos = $pos;

        while (!$found) {
            if ($newPos < $formulaLength) {
                $ch = substr($this->_formula, $newPos++, 1);
            }
            elseif ($unexpectedEof) {
                throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__('Unexpected end of formula'));
            }
            else {
                $newPos++;
                if ($kind === 0) {
                    $kind = self::EOF;
                }
                break;
            }

            switch ($state) {
                case 0: // analyzing first symbol
                    if ($ch == '\'') {
                        $kind = self::STRING;
                        $state = 1;
                        $unexpectedEof = true;
                    }
                    elseif (strpos(self::$_firstIdentifierSymbol, $ch) !== false) {
                        $kind = self::IDENTIFIER;
                        $state = 3;
                    }
                    elseif (strpos(self::$_numeric, $ch) !== false) {
                        $kind = self::NUMBER;
                        $state = 4;
                    }
                    else {
                        foreach (self::$_tokens as $k => $v) {
                            if ($k != self::OPEN_FORMULA && $k != self::CLOSE_FORMULA && strpos($this->_formula, $v, $pos) === $pos) {
                                $kind = $k;
                                $text = $v;
                                $found = true;
                                $newPos++;
                                break;
                            }
                        }
                        if (!$found) {
                            throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__("Unexpected character '%s'", $ch));
                        }
                    }
                    break;
                case 1: // inside string
                    if ($ch == '\\') {
                        $state = 2;
                    }
                    elseif ($ch == '\'') {
                        $newPos++;
                        $found = true;
                    }
                    else {
                        $text .= $ch;
                    }
                    break;
                case 2: // processing string escape symbol
                    switch ($ch) {
                        case '\'':
                        case '\\':
                            $text .= $ch;
                            break;
                        default:
                            $text .= '\\';
                            break;
                    }
                    $state = 1;
                    break;
                case 3: // reading identifier
                    if (strpos(self::$_nextIdentifierSymbol, $ch) === false) {
                        $found = true;
                    }
                    break;
                case 4: // reading number
                    if (strpos(self::$_numeric, $ch) === false) {
                        $found = true;
                    }
                    break;
            }
        }

        $length = $newPos - $pos - 1;

        if (!$text && $length) {
            $text = substr($this->_formula, $pos, $length);
        }
        return (object)compact('kind', 'text', 'pos', 'length');
    }

}