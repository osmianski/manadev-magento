<?php
/** 
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Model_Validator {
    protected $_rules = array();
    protected $_data = array();
    protected $_model;
    protected $_errors = array();
    protected $_allErrors = array();
    protected $_ignoredRules = array();
    protected $defaultMessages = array(
        'required' => "Please fill in :field field.",
        'unique'   => "The value of :field already exists.",
    );

    public function __construct($args) {
        list($rules, $data, $captions, $model) = $args;
        $this->_data = $data;
        $this->_rules = $this->explodeRules($rules);
        $this->_captions = $captions;
        $this->_model = $model;
    }

    public function passes() {
        foreach($this->_rules as $field => $rules) {
            foreach($rules as $rule) {
                $this->validate($field, $rule);
            }
        }
        return count($this->_errors) === 0;
    }

    protected  function explodeRules($rules) {
        foreach($rules as $field => $rulesAsString) {
            $rules[$field] = explode("|", $rulesAsString);
        }
        return $rules;
    }

    protected function validate($field, $rule) {
        if (trim($rule) == "" || in_array($rule, $this->_ignoredRules)) return;

        $method = "validate". $this->contentHelper()->underscoreToCamelcase($rule);

        if(method_exists($this, $method) && !$this->$method($field, $this->_data[$field])) {
            $this->addError($field, $rule);
        }
    }

    protected function addError($field, $rule) {
        $message = $this->getMessage($field, $rule);
        $this->_errors[$field][$rule] = $message;
        $this->_allErrors[] = $message;
    }

    public function getMessage($field, $rule) {
        $message = $this->defaultMessages[$rule];
        if(trim($message) == "") {
            $message = "Rule `{$rule}` failed in field :field";
        }
        return str_replace(":field", $this->getFieldCaption($field), $message);
    }

    public function getMessages() {
        return $this->_allErrors;
    }

    protected function validateRequired($field, $value) {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_array($value) && count($value) < 1) {
            return false;
        }
        return true;
    }

    protected function validateUnique($field, $value) {
        if(!isset($this->_model)) {
            throw new Exception("Could not validate if field is unique because model is not declared in validator.");
        }
        $resource = Mage::getModel($this->_model->getResourceName());
        $resource->load($value, $field);
        if($resource->getId() && $resource->getId() != $this->_model->getId()) {
            return false;
        }
        return true;
    }

    protected function getFieldCaption($field) {
        if($this->_captions[$field]) {
            return $this->contentHelper()->__($this->_captions[$field]);
        } else {
            return $this->contentHelper()->underscoreToCapitalize($this->contentHelper()->__($field));
        }
    }

    public function ignoreRule($rule) {
        if(!isset($this->_ignoredRules[$rule])) {
            $this->_ignoredRules[] = $rule;
        }
    }

    #region Dependencies

    /**
     * @return Mana_Content_Helper_Data
     */
    public function contentHelper() {
        return Mage::helper('mana_content');
    }

    #endregion
}