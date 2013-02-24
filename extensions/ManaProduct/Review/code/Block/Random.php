<?php
/**
 * @category    Mana
 * @package     ManaProduct_Review
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaProduct_Review_Block_Random extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {
    protected $_reviewInitialized = false;
    protected $_reviews;
    protected $_products = array();

    /**
     * @return Mage_Review_Model_Mysql4_Review_Collection
     */
    public function getReviewsCollection()
    {
        return Mage::getModel('review/review')->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->setDateOrder();
    }

    public function getReviews() {
        if (!$this->_reviewInitialized) {
            $collection = $this->getReviewsCollection();
            $this->_reviews = array();
            if ($ids = $collection->getAllIds()) {
                $expectedReviewCount = $this->getReviewCount() ? $this->getReviewCount() : 1;
                $maxReviewCount = count($ids);
                if ($maxReviewCount > $expectedReviewCount) {
                    $maxReviewCount = $expectedReviewCount;
                }
                for ($i = 0; $i < $maxReviewCount;) {
                    $id = $ids[rand(0, count($ids) - 1)];
                    if (!isset($this->_reviews[$id])) {
                        $this->_reviews[$id] = Mage::getModel('review/review')->load($id);
                        $i++;
                    }
                }

            }
            $this->_reviewInitialized = true;
        }
        return $this->_reviews;
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct($review) {
        if (!isset($this->_products[$review->getEntityPkValue()])) {
            $this->_products[$review->getEntityPkValue()] = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($review->getEntityPkValue());
        }
        return $this->_products[$review->getEntityPkValue()];
    }

    public function getReadMoreUrl($review) {
        return $this->getProduct($review)->getProductUrl().'#review-list';
    }
}