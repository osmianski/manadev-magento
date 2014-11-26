<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Resource_Page_Tag_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct()
    {
        $this->_init('tag/tag');
    }

    /**
     * Replacing popularity by sum of popularity and base_popularity
     *
     * @param int $storeId
     * @return Mage_Tag_Model_Mysql4_Popular_Collection
     */
    public function joinFields($storeId = 0)
    {
        $this->getSelect()
            ->reset()
            ->from(
                array('tag_summary' => $this->getTable('mana_content/page_tagSummary')),
                array(
                    'page_tag_id',
                    'popularity'
                )
            )
            ->joinInner(
                array('tag' => $this->getTable('mana_content/page_tag')),
                'tag.id = tag_summary.page_tag_id'
            )
            ->where('tag_summary.store_id = ?', $storeId)
            ->order('popularity desc');
        return $this;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        parent::load($printQuery, $logQuery);
        return $this;
    }

    public function limit($limit)
    {
        $this->getSelect()->limit($limit);
        return $this;
    }
}