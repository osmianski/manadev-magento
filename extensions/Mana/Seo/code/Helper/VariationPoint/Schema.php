<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Helper_VariationPoint_Schema extends Mana_Seo_Helper_VariationPoint
{
    /**
     * @param Mana_Seo_Model_Context $context
     * @return Mana_Seo_Helper_VariationPoint_Schema
     */
    protected function _before(/** @noinspection PhpUnusedParameterInspection */$context) {
        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Schema $schema
     * @return Mana_Seo_Helper_VariationPoint_Schema
     */
    protected function _register($context, $schema) {
        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        $logger->beginSeo("Checking schema {$schema->getName()} ...");
        $context->setSchema($schema);

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Schema $schema
     * @return Mana_Seo_Helper_VariationPoint_Schema
     */
    protected function _unregister(/** @noinspection PhpUnusedParameterInspection */$context, $schema) {
        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        $context->unsetData('schema');
        $logger->endSeo();

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return Mana_Seo_Helper_VariationPoint_Schema
     */
    protected function _after(/** @noinspection PhpUnusedParameterInspection */$context) {
        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Schema[] $activeSchemas
     * @param Mana_Seo_Model_Schema[] $obsoleteSchemas
     * @return Mana_Seo_Helper_VariationPoint_Schema
     */
    protected function _getSchemas($context, &$activeSchemas, &$obsoleteSchemas) {
        $activeSchemas = array();
        $obsoleteSchemas = array();

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/schema/store_flat_collection');
        $collection
            ->setStoreFilter($context->getStoreId())
            ->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Schema::STATUS_ACTIVE,
                    Mana_Seo_Model_Schema::STATUS_OBSOLETE
                )
            ));

        foreach ($collection as $schema) {
            /* @var $schema Mana_Seo_Model_Schema */
            if ($schema->getStatus() == Mana_Seo_Model_Schema::STATUS_ACTIVE) {
                $activeSchemas[] = $schema;
            }
            else {
                $obsoleteSchemas[] = $schema;
            }
        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return bool
     */
    public function match($context) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $allObsoleteSchemas = array();
        $action = $context->getAction();

        $this->_before($context);
        $this->_getSchemas($context, $activeSchemas, $obsoleteSchemas);
        foreach ($activeSchemas as $schema) {
            $this->_register($context, $schema);

            if ($seo->getPageUrlVariationPoint()->match($context)) {
                return true;
            }

            $this->_unregister($context, $schema);
        }
        $allObsoleteSchemas = array_merge($allObsoleteSchemas, $obsoleteSchemas);

        $context->setAction(Mana_Seo_Model_Context::ACTION_REDIRECT);
        foreach ($allObsoleteSchemas as $schema) {
            $this->_register($context, $schema);

            if ($seo->getPageUrlVariationPoint()->match($context)) {
                return true;
            }

            $this->_unregister($context, $schema);
        }

        $context->setAction($action);
        $this->_after($context);

        return false;
    }
}