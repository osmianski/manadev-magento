<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class ManaPro_ProductFaces_Model_ChangeLog extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('manapro_productfaces/changeLog');
    }

    /**
     * @return ManaPro_ProductFaces_Resource_ChangeLog
     */
    protected function _getResource() {
        return parent::_getResource();
    }

    public function runCronJob() {
        try {
            $this->_run('cron');
        }
        catch (Exception $e) {
            $this->_sendErrorEmail($e);
        }
    }

    public function runManually() {
        return $this->_run('manual');
    }

    public function createDropTriggerAsConfigured() {
        if ($this->isEnabled()) {
            $this->_getResource()->createTriggerIfNotExists();
        }
        else {
            $this->_getResource()->dropTriggerIfExists();
        }
    }

    public function isEnabled() {
        return Mage::getStoreConfigFlag('manapro_productfaces/inventory_change_log/is_enabled');
    }

    public function getPendingProductCount() {
        if ($this->isEnabled()) {
            return $this->_getResource()->getPendingProductCount();
        }
        else {
            return 0;
        }
    }

    protected function _run($environment) {
        if (!$this->isEnabled()) {
            return 0;
        }

        return $this->_getResource()->run($environment);
    }

    protected function _sendErrorEmail(Exception $e) {
        if (!($recipient = Mage::getStoreConfig('manapro_productfaces/inventory_change_log/error_email'))) {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $emailTemplate = Mage::getModel('core/email_template');
        /* @var $emailTemplate Mage_Core_Model_Email_Template */
        $emailTemplate->setDesignConfig(array('area' => 'backend'));

        $emailTemplate->setSentSuccess(false);
        $storeId = null;
        if (($storeId === null) && $emailTemplate->getDesignConfig() && $emailTemplate->getDesignConfig()->getStore()) {
            $storeId = $emailTemplate->getDesignConfig()->getStore();
        }

        $emailTemplate->setTemplateSubject(Mage::helper('manapro_productfaces')->__('Inventory change tracker failed at %s', Mage::getBaseUrl()));
        $emailTemplate->setTemplateText("{$e->getMessage()}\n{$e->getTraceAsString()}");

        $sender = Mage::getStoreConfig('manapro_productfaces/inventory_change_log/error_email_identity');
        if (!is_array($sender)) {
            $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_'.$sender.'/name', $storeId));
            $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_'.$sender.'/email', $storeId));
        } else {
            $emailTemplate->setSenderName($sender['name']);
            $emailTemplate->setSenderEmail($sender['email']);
        }

        $vars = array();
        $vars['store'] = Mage::app()->getStore($storeId);

        $emailTemplate->setSentSuccess($emailTemplate->send($recipient, null, $vars));

        $translate->setTranslateInline(true);
        return $this;
    }
}