<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Indexer extends Mana_Core_Model_Indexer
{
    protected $_code = "local_manadev_license_status";
    protected $_matchedEntities = array(
        Local_Manadev_Model_Downloadable_Item::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );
    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName() {
        return Mage::helper('local_manadev')->__('License Status');
    }

    protected function _construct() {
        $this->_init('local_manadev/indexer');
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event) {
        if ($event->getEntity() == Local_Manadev_Model_Downloadable_Item::ENTITY) {
            $event->addNewData('item_id', $event->getDataObject()->getId());
        }
    }

    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event) {
        $this->_getResource()->process($event->getNewData());
    }

    public function getDescription() {
        return Mage::helper('local_manadev')->__('Sets license status to expired if it has exceeded its expire date.');
    }

    public function runCronJob() {
        try {
            $this->reindexAll();
        }
        catch (Exception $e) {
            $subject = 'License status error: ' . $e->getMessage();
            $body = $e->getTraceAsString();

            $emailTemplate = Mage::getModel('core/email_template');
            /* @var $emailTemplate Mage_Core_Model_Email_Template */
            $emailTemplate->setDesignConfig(array('area' => 'backend'));

            $emailTemplate->setSentSuccess(false);

            $emailTemplate->setTemplateSubject($subject);
            $emailTemplate->setTemplateText($body);

            $emailTemplate->setSenderName('team@manadev.com');
            $emailTemplate->setSenderEmail('team@manadev.com');

            $emailTemplate->setSentSuccess($emailTemplate->send('vo@manadev.com'));
        }
    }
}