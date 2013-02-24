<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Entry points for cron and index processes
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Indexer extends Mage_Index_Model_Indexer_Abstract {
	// INDEXING ITSELF
	
    protected function _construct()
    {
        $this->_init('manapro_productfaces/inventory');
    }
    public function getName()
    {
        return Mage::helper('manapro_productfaces')->__('Representing Products');
    }
    public function getDescription()
    {
        return Mage::helper('manapro_productfaces')->__('Syncronizes Inventory of Representing Products');
    }
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
    }
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
    }
	public function reindexAll() {
		$this->_getResource()->updateAll();
	}
    
    // ERROR REPORTING IN CRON
    protected $_errors = array();
    protected function _sendErrorEmail()
    {
        if (!$this->_errors) {
            return $this;
        }
        if (!($recipient = Mage::getStoreConfig('manapro_productfaces/schedule/error_email'))) {
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
        
        $emailTemplate->setTemplateSubject(Mage::helper('manapro_productfaces')->__('Scheduled indexer %s failed at %s', $this->getName(), Mage::getBaseUrl()));
        $emailTemplate->setTemplateText(Mage::helper('manapro_productfaces')->__('Errors while running scheduled indexer %s:', $this->getName())."\n\n{{var warnings}}");
        
        $sender = Mage::getStoreConfig('manapro_productfaces/schedule/error_email_identity');
        if (!is_array($sender)) {
            $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_'.$sender.'/name', $storeId));
            $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_'.$sender.'/email', $storeId));
        } else {
            $emailTemplate->setSenderName($sender['name']);
            $emailTemplate->setSenderEmail($sender['email']);
        }

        $vars = array('warnings' => join("\n", $this->_errors));
        $vars['store'] = Mage::app()->getStore($storeId);

        $emailTemplate->setSentSuccess($emailTemplate->send($recipient, null, $vars));
        
        $translate->setTranslateInline(true);
        return $this;
    }
    public function runCronjob()
    {
        $this->_errors = array();

        try {
            $this->reindexAll();
        }
        catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTraceAsString();
        	$this->_sendErrorEmail();
        	throw $e;
        }

        return $this;
    }
}