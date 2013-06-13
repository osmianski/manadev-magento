<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for ManaPro_FilterSeoLinks module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterSeoLinks_Helper_Data extends Mage_Core_Helper_Abstract {
	protected $_urlVars;
	protected $_rewriteVars;
	public function getUrlVars() {
		if (!$this->_urlVars) {
			$this->_urlVars = array(
				'p' => 'p',
				'order' => 'order',
				'dir' => 'dir',
				'mode' => 'mode',
				'limit' => 'limit',
				//'___from_store' => 'from-store',
				//'___store' => 'store',
			);
		}
		return $this->_urlVars;
	}
	public function getRewriteVars() {
		if (!$this->_rewriteVars) {
			$this->_rewriteVars = array(
				'p' => 'p',
				'order' => 'order',
				'dir' => 'dir',
				'mode' => 'mode',
				'limit' => 'limit',
				//'from-store' => '___from_store',
				//'store' => '___store',
			);
		}
		return $this->_rewriteVars;
	}
	protected $_categoryName;
    public function getCategoryName() {
        if (!$this->_categoryName) {
            $categoryFilter = Mage::helper('mana_core')->collectionFind(
                Mage::helper('mana_filters')->getFilterOptionsCollection(true),
                'code', 'category');
            $this->_categoryName = Mage::getStoreConfigFlag('mana_filters/seo/use_label')
                ? Mage::helper('mana_core')->labelToUrl($categoryFilter->getLowerCaseName())
                : 'category';
        }
        return $this->_categoryName;
    }

    public function getCategoryUrlSuffix() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        /* @var $helper Mage_Catalog_Helper_Category */
        $helper = Mage::helper('catalog/category');
        return $core->addDotToSuffix($helper->getCategoryUrlSuffix());
    }
}