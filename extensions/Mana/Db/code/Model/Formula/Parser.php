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
    const FIELD = 6;
    const FUNCTION_CALL = 7;
    const MULTIPLY = 8;
    const ADD = 9;

    const TOKEN_OPEN_PAR = 1;
    const TOKEN_CLOSE_PAR = 2;
    const TOKEN_OPEN_FORMULA = 3;
    const TOKEN_CLOSE_FORMULA = 4;
    const TOKEN_IDENTIFIER = 5;
    const TOKEN_DOT = 6;
    const TOKEN_NUMBER = 7;
    const TOKEN_STRING = 8;
    const TOKEN_COMMA = 9;
    const TOKEN_MULTIPLY = 10;
    const TOKEN_DIVIDE = 11;
    const TOKEN_ADD = 12;
    const TOKEN_SUBTRACT = 13;
    const TOKEN_EOF = 14;
    const TOKEN_WHITESPACE = 15;

    static $_tokens = array(
        self::TOKEN_OPEN_PAR => '(',
        self::TOKEN_CLOSE_PAR => ')',
        self::TOKEN_OPEN_FORMULA => '{{=',
        self::TOKEN_CLOSE_FORMULA => '}}',
        self::TOKEN_DOT => '.',
        self::TOKEN_COMMA => ',',
        self::TOKEN_MULTIPLY => '*',
        self::TOKEN_DIVIDE => '/',
        self::TOKEN_ADD => '+',
        self::TOKEN_SUBTRACT => '-',
    );
    static $_multiplicationOperators = array(self::TOKEN_MULTIPLY, self::TOKEN_DIVIDE);
    static $_additionOperators = array(self::TOKEN_ADD, self::TOKEN_SUBTRACT);

    static $_numeric = '0123456789';
    static $_firstIdentifierSymbol = '_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    static $_nextIdentifierSymbol = '_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
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
        for ($pos = strpos($formula, self::$_tokens[self::TOKEN_OPEN_FORMULA]); $pos !== false;
            $pos = strpos($formula, self::$_tokens[self::TOKEN_OPEN_FORMULA], $fromPos + 2))
        {
            if ($fromPos < $pos) {
                $parts[] = $this->_parseString(substr($formula, $fromPos, $pos - $fromPos));
            }
            if (($fromPos = strpos($formula, self::$_tokens[self::TOKEN_CLOSE_FORMULA], $pos + 3)) !== false) {
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
            return (object)array('type' => self::FORMULA_EXPR, 'parts' => $parts);
        }
        else {
            return $parts[0];
        }
    }

    protected function _parseString($value) {
        return (object)array('type' => self::STRING_CONSTANT, 'value' => $value);
    }

    public function parseFormula($formula) {
        $this->_formula = $formula;
        $this->_pos = 0;
        $this->_scan();

        return $this->_parseExpr();
    }

    protected function _parseNull() {
        return (object)array('type' => self::NULL_VALUE);
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

            return (object)array('type' => self::ADD, 'operator' => $operator, 'a' => $a, 'b' => $b);
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

            return (object)array('type' => self::MULTIPLY, 'operator' => $operator, 'a' => $a, 'b' => $b);
        }
        else {
            return $a;
        }
    }

    protected function _parsePrimaryExpr() {
        if ($this->_token->kind == self::TOKEN_OPEN_PAR) {
            $this->_scan();
            $result = $this->_parseExpr();
            $this->_expect(self::TOKEN_CLOSE_PAR);
            return $result;
        }
        elseif ($this->_token->kind == self::TOKEN_STRING) {
            return (object)array('type' => self::STRING_CONSTANT, 'value' => $this->_token->text);
        }
        elseif ($this->_token->kind == self::TOKEN_NUMBER) {
            return (object)array('type' => self::NUMBER_CONSTANT, 'value' => $this->_token->text);
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
                return (object)array('type' => self::IDENTIFIER, 'identifier' => $identifier);
            }
        }
        else {
            $this->_error(Mage::helper('mana_db')->__('Invalid expression'));
        }
    }

    protected function _parseField($identifier) {
        $identifiers = array($identifier);

        while ($this->_token->kind == self::TOKEN_DOT) {
            $this->_expect(self::TOKEN_IDENTIFIER);
            $identifiers[] = $this->_token->text;
            $this->_scan();
        }

        return (object)array('type' => self::FIELD, 'identifiers' => $identifiers);
    }

    protected function _parseFunction($name) {
        $args = array();

        $this->_expect(self::TOKEN_OPEN_PAR);

        if ($this->_token->kind != self::TOKEN_CLOSE_PAR) {
            while ($this->_token->kind == self::TOKEN_COMMA) {
                $this->_scan();
                $args[] = $this->_parseExpr();
            }
        }

        $this->_expect(self::TOKEN_CLOSE_PAR);

        return (object)array('type' => self::FUNCTION_CALL, 'args' => $args);
    }

    protected function _expect($tokenKind) {
        $this->_scan();
        if ($this->_token->kind != $tokenKind) {
            $this->_error($tokenKind);
        }
    }

    protected function _error($errNo) {
        if (is_string($errNo)) {
            $err = $errNo;
        }
        else {
            $t = Mage::helper('mana_db');
            switch ($errNo) {
                case self::TOKEN_IDENTIFIER: $err = $t->__("Identifier expected"); break;
                case self::TOKEN_NUMBER: $err = $t->__("Number constant expected"); break;
                case self::TOKEN_STRING: $err = $t->__("String constant expected"); break;
                case self::TOKEN_EOF: $err = $t->__("End of formula expected"); break;
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
        $token = $this->_internalScan();
        while ($token->kind == self::TOKEN_WHITESPACE) {
            $token = $this->_internalScan();
        }
        $this->_token = $this->_peek(1);
        $this->_pos = $token->pos + $token->length;
    }

    protected function _peek($count = 1) {
        $token = null;
        for ($pos = $this->_pos, $i = 0; $i < $count; $i++) {
            $token = $this->_internalScan($pos);
            $pos = $token->pos + $token->length;
            while ($token->kind == self::TOKEN_WHITESPACE) {
                $token = $this->_internalScan();
                $pos = $token->pos + $token->length;
            }
        }
        return $token;
    }

    protected function _internalScan($pos = false) {
        $kind = 0;
        $text = '';
        if ($pos === false) {
            $pos = $this->_pos;
        }
        $newPos = $this->_pos;
        $formulaLength = strlen($this->_formula);
        $state = 0;
        $unexpectedEof = false;
        $found = false;

        while (!$found) {
            if ($newPos < $formulaLength) {
                $ch = substr($this->_formula, $newPos++, 1);
            }
            elseif ($unexpectedEof) {
                throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__('Unexpected end of formula'));
            }
            else {
                if ($kind === 0) {
                    $kind = self::TOKEN_EOF;
                }
                break;
            }

            switch ($state) {
                case 0: // analyzing first symbol
                    if ($ch == '\'') {
                        $kind = self::TOKEN_STRING;
                        $state = 1;
                        $unexpectedEof = true;
                    }
                    elseif (strpos(self::$_firstIdentifierSymbol, $ch) !== false) {
                        $kind = self::TOKEN_IDENTIFIER;
                        $state = 3;
                    }
                    elseif (strpos(self::$_numeric, $ch) !== false) {
                        $kind = self::TOKEN_NUMBER;
                        $state = 4;
                    }
                    else {
                        foreach (self::$_tokens as $k => $v) {
                            if (substr($this->_formula, $v, $pos) === $pos) {
                                $kind = $k;
                                $text = $v;
                                $found = true;
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

        $length = $newPos - $pos;

        if (!$text && $length) {
            $text = substr($this->_formula, $pos, $length);
        }
        return (object)compact('kind', 'text', 'pos', 'length');
    }

}