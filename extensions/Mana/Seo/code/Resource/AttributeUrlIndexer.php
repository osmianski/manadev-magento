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
abstract class Mana_Seo_Resource_AttributeUrlIndexer extends Mana_Seo_Resource_UrlIndexer {
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        'mana_filters/filter2_store' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
    );

    /**
     * @param Mana_Seo_Model_UrlIndexer $indexer
     * @param Mage_Index_Model_Event $event
     */
    public function register(
        /** @noinspection PhpUnusedParameterInspection */
        $indexer,
        $event
    ) {
        $db = $this->_getReadAdapter();

        if ($event->getEntity() == Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY) {
            $event->addNewData('attribute_id', $event->getData('data_object')->getId());
        }
        elseif ($event->getEntity() == 'mana_filters/filter2') {
            if ($attributeId = $this->getFilterResource()->getAttributeId($event->getData('data_object'))) {
                $event->addNewData('attribute_id', $attributeId);
            }
        }
        elseif ($event->getEntity() == 'mana_filters/filter2_store') {
            if ($attributeId = $this->getFilterStoreResource()->getAttributeId($event->getData('data_object'))) {
                $event->addNewData('attribute_id', $attributeId);
            }
        }
    }

    #region Dependencies

    /**
     * @return Mana_Filters_Resource_Filter2
     */
    public function getFilterResource() {
        return Mage::getResourceSingleton('mana_filters/filter2');
    }

    /**
     * @return Mana_Filters_Resource_Filter2_Store
     */
    public function getFilterStoreResource() {
        return Mage::getResourceSingleton('mana_filters/filter2_store');
    }
    #endregion
}