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
    implements Mana_Seo_Interface_VariationSource
{
    /**
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @return Mana_Seo_Interface_VariationSource[]
     */
    public function getVariationSources($variationPoint) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');
        return array($this);
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @param Mana_Seo_Model_Schema $variation
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function registerVariation($context, $variationPoint, $variation) {
        $context->setSchema($variation);

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @param Mana_Seo_Model_Schema $variation
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function unregisterVariation($context, $variationPoint, $variation) {
        $context->unsetData('schema');

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param object[] $activeVariations
     * @param object[] $obsoleteVariations
     * @return Mana_Seo_Interface_VariationSource
     */
    public function getVariations($context, &$activeVariations, &$obsoleteVariations) {
        $activeVariations = array();
        $obsoleteVariations = array();

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
                $activeVariations[] = $schema;
            }
            else {
                $obsoleteVariations[] = $schema;
            }
        }
    }
}