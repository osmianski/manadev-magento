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
    protected $_config = array();
    protected $defaultMessages = array(
        'required' => "Please fill in :field field.",
        'unique'   => "A record with `:value` :field already exists.",
        'numeric'  => "Field :field should only contain numbers.",
    );
    protected $defaultConfig = array(
        'singleErrorOnly' => false,
        'singleErrorPerField' => true,
        'skipUndefinedField' => true,
    );

    public function __construct($args) {
        list($rules, $data, $captions, $model, $config) = $args;
        $this->_data = $data;
        $this->_rules = $this->explodeRules($rules);
        $this->_captions = $captions;
        $this->_model = $model;
        $this->_config = array_merge($this->defaultConfig, $config);
    }

    public function passes() {
        foreach($this->_rules as $field => $rules) {
            foreach($rules as $rule) {
                $this->validate($field, $rule);
                // Stop validation if singleErrorOnly configuration is enabled
                if($this->isSingleErrorOnly() && count($this->_errors) > 0) {
                    return false;
                }
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

        $method = "validate". $this->adminHelper()->underscoreToCamelcase($rule);

        if(!($this->isSkipValidationOnUndefinedData() && !isset($this->_data[$field]))) {
            $data = isset($this->_data[$field]) ? $this->_data[$field] : "";
            if (method_exists($this, $method) && !$this->$method($field, $data)) {
                $this->addError($field, $rule);
            }
        }
    }

    protected function addError($field, $rule) {
        $message = $this->getMessage($field, $rule);
        // Do not add error if singleErrorPerField configuration is enabled and there is already an error in that field.
        if ($this->isSingleErrorPerField() && isset($this->_errors[$field])) {
            return;
        }
        $this->_errors[$field][$rule] = $message;
        $this->_allErrors[] = $message;
    }

    public function getMessage($field, $rule) {
        if(isset($this->defaultMessages[$rule])) {
            $message = $this->defaultMessages[$rule];
        } else {
            $message = "Rule `:rule` failed in field :field.";
        }
        $this->adminHelper()->__($message);

        $message = str_replace(":rule", $rule, $message);
        $message = str_replace(":field", $this->getFieldCaption($field), $message);
        $message = str_replace(":value", $this->_model->getData($field), $message);
        return $message;
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

    protected function validateNumeric($field, $value) {
        return is_numeric($value);
    }

    protected function getFieldCaption($field) {
        if(isset($this->_captions[$field])) {
            return $this->adminHelper()->__($this->_captions[$field]);
        } else {
            return $this->adminHelper()->underscoreToCapitalize($this->adminHelper()->__($field));
        }
    }

    public function ignoreRule($rule) {
        if(!isset($this->_ignoredRules[$rule])) {
            $this->_ignoredRules[] = $rule;
        }
    }

    public function getConfig($configKey) {
        if(isset($this->_config[$configKey])) {
            return $this->_config[$configKey];
        }
        throw new Exception("Undefined config key `$configKey`.");
    }

    private function isSingleErrorOnly() {
        return $this->getConfig('singleErrorOnly');
    }

    private function isSingleErrorPerField() {
        return $this->getConfig('singleErrorPerField');
    }

    private function isSkipValidationOnUndefinedData() {
        return $this->getConfig('skipUndefinedField');
    }

    #region Dependencies

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    #endregion
}