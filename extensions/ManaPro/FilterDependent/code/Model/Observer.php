<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterDependent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterDependent_Model_Observer {
    /**
     * Adds edited data received via HTTP to specified model (handles event "m_db_add_edited_data")
     * @param Varien_Event_Observer $observer
     */
    public function addEditedData($observer) {
        /* @var $object Mana_Db_Model_Object */ $object = $observer->getEvent()->getObject();
        /* @var $fields array */ $fields = $observer->getEvent()->getFields();

        switch ($object->getEntityName()) {
            case 'mana_filters/filter2':
                $object->setData('depends_on_filter_id', !empty($fields['depends_on_filter_id']) ? $fields['depends_on_filter_id'] : null);
                break;
        }
    }

    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_crud_form")
     * @param Varien_Event_Observer $observer
     */
    public function addFields($observer) {
        /* @var $formBlock Mana_Admin_Block_Crud_Card_Form */
        /** @noinspection PhpUndefinedMethodInspection */
        $formBlock = $observer->getEvent()->getForm();
        $form = $formBlock->getForm();
        switch ($formBlock->getEntityName()) {
            case 'mana_filters/filter2':
            case 'mana_filters/filter2_store':
                if ($form->getId() == 'mf_general') {

                /** @noinspection PhpParamsInspection */
                $fieldset = $form->addFieldset('mfs_dependency', array(
                    'title' => $this->dependentHelper()->__('Filter Dependency'),
                    'legend' => $this->dependentHelper()->__('Filter Dependency'),
                ));
                $fieldset->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_fieldset'));

                $fieldset->addField('depends_on_filter_id', 'select', array(
                    'label' => $this->dependentHelper()->__('Available if Filter is Applied'),
                    'name' => 'depends_on_filter_id',
                    'options' => Mage::getModel('manapro_filterdependent/source_filter')
                        ->setCurrentFilterId(Mage::app()->getRequest()->getParam('id'))
                        ->getOptionArray(),
                    'required' => false,
                    'disabled' => !$this->adminHelper()->isGlobal(),
                ))->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));
                }
                break;
        }
    }

    #region Dependencies

    /**
     * @return Mage_Core_Model_Layout
     */
    public function getLayout() {
        return Mage::getSingleton('core/layout');
    }

    /**
     * @return ManaPro_FilterDependent_Helper_Data
     */
    public function dependentHelper() {
        return Mage::helper('manapro_filterdependent');
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }
    #endregion
}