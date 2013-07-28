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
abstract class Mana_Seo_Resource_CategoryUrlIndexer extends Mana_Seo_Resource_UrlIndexer {
    protected $_childCategoryIds = array();

    protected $_matchedEntities = array(
        Mage_Catalog_Model_Category::ENTITY => array(
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
        if ($event->getEntity() == Mage_Catalog_Model_Category::ENTITY) {
            $event
                ->addNewData('category_id', $event->getData('data_object')->getId())
                ->addNewData('category_path', $event->getData('data_object')->getPath());
        }
    }

    protected function _getChildCategoryIds($id, $path) {
        if (!isset($this->_childCategoryIds[$id])) {
            $db = $this->_getReadAdapter();

            $select = $db->select()
                ->from(array('e' => $this->getTable('catalog/category')), 'entity_id')
                ->where('`e`.`path` LIKE ?', $path . '/%');
            $childrenIds = $db->fetchCol($select);
            $this->_childCategoryIds[$id] = array_merge(array($id), $childrenIds);
        }

        return $this->_childCategoryIds[$id];
    }
}