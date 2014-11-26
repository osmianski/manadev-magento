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
class Mana_Content_Block_Filter_Tags extends Mage_Tag_Block_Popular {
    protected function _construct() {
        $this->setTemplate('mana/content/filter/tags.phtml');
    }

    protected function _loadTags()
    {
        if (empty($this->_tags)) {
            $this->_tags = array();

            $tags = Mage::getResourceModel('mana_content/page_tag_collection')
                ->joinFields(Mage::app()->getStore()->getId())
                ->limit(20)
                ->load()
                ->getItems();

            if( count($tags) == 0 ) {
                return $this;
            }


            $this->_maxPopularity = reset($tags)->getPopularity();
            $this->_minPopularity = end($tags)->getPopularity();
            $range = $this->_maxPopularity - $this->_minPopularity;
            $range = ($range == 0) ? 1 : $range;
            foreach ($tags as $tag) {
                $tag->setRatio(($tag->getPopularity()-$this->_minPopularity)/$range);
                $this->_tags[$tag->getName()] = $tag;
            }
            ksort($this->_tags);
        }
        return $this;
    }

}