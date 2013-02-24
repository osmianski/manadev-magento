<?php
/**
 * @category    Mana
 * @package     ManaPro_Featured
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class ManaPro_Featured_Block_List extends Mage_Catalog_Block_Product_Abstract {

    /**
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     */
    protected $_collection;
    /**
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getCollection() {
        return $this->_collection;
    }

    protected $_mainChild = null;
    protected function _prepareLayout() {
        $this->_prepareCollection();

        $template = Mage::getStoreConfig($this->getConfigSource().'/template');
        if ($template == 'custom') {
            $this->setTemplate(Mage::getStoreConfig($this->getConfigSource() . '/custom'));
        }
        elseif ($template == 'none') {
            // do nothing
        }
        else {
            $xml = Mage::getConfig()->getNode($this->getConfigSource() . '/'. $template);
            if (isset($xml->block)) {
                $child = $this->getLayout()->createBlock((string)$xml->block, $this->getNameInLayout() . '.content', array(
                    'collection' => $this->getCollection(),
                    'config_source' => $this->getConfigSource(),
                ));
                if (isset($xml->template)) {
                    $child->setTemplate((string)$xml->template);
                }
                $this->append($child, $this->getNameInLayout().'.content');
                $this->_mainChild = $child;
            }
            else {
                $this->setTemplate((string)$xml->template);
            }
        }
        return parent::_prepareLayout();
    }
    public function addCategoryFilter() {
        /* @var $db Mage_Core_Model_Resource */
        $db = Mage::getSingleton('core/resource');
        $condition = Mage::getStoreConfigFlag($this->getConfigSource() . '/subcategory')
            ? "mfc.path LIKE '{$this->getCurrentCategory()->getPath()}%'"
            : "mfc.path = '{$this->getCurrentCategory()->getPath()}'";
        $this->_collection->getSelect()->where("e.entity_id IN (
            SELECT mfcp.product_id
            FROM {$db->getTableName('catalog/category_product')} AS mfcp
            INNER JOIN {$db->getTableName('catalog/category')} AS mfc ON mfc.entity_id = mfcp.category_id
            WHERE $condition
        )");

        return $this;
    }
    public function addFeaturedFilter() {
        $todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $this->_collection
            ->addAttributeToFilter('m_featured_from_date', array('date' => true, 'to' => $todayDate))
            ->addAttributeToFilter('m_featured_to_date', array('or' => array(
                0 => array('date' => true, 'from' => $todayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left');

        return $this;
    }
    public function getCurrentCategory() {
        if (!$this->getData('current_category')) {
            if ($this->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                $this->setData('current_category', $category);
            }
            elseif ($category = Mage::registry('current_category')) {
                $this->setData('current_category', $category);
            }
            else {
                $category = Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId());
                $this->setData('current_category', $category);
            }
        }
        return $this->getData('current_category');
    }
    protected function _toHtml() {
        if ($this->getTemplate()) {
            return parent::_toHtml();
        }
        else {
            // like core/text_list
            $html = '';
            foreach ($this->getSortedChildren() as $name) {
                $block = $this->getLayout()->getBlock($name);
                if (!$block) {
                    Mage::throwException(Mage::helper('core')->__('Invalid block: %s', $name));
                }
                $html .= $block->toHtml();
            }
            return $html;
        }
    }
    protected function _prepareCollection() {
        $this->_collection = Mage::getResourceModel('catalog/product_collection');
        $this->_collection
                ->setStoreId(Mage::app()->getStore()->getId())
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents();

        if (($sort = Mage::getStoreConfig($this->getConfigSource() . '/sort')) != 'random') {
            list($sortField, $direction) = explode('_', $sort);
            if ($sortField == 'position') {
                /* @var $db Mage_Core_Model_Resource */
                $db = Mage::getSingleton('core/resource');
                $condition = Mage::getStoreConfigFlag($this->getConfigSource() . '/subcategory')
                        ? "pmfc.path LIKE '{$this->getCurrentCategory()->getPath()}%'"
                        : "pmfc.path = '{$this->getCurrentCategory()->getPath()}'";
                $expr = new Zend_Db_Expr("(SELECT MIN(pmfcp.position)
                    FROM {$db->getTableName('catalog/category_product')} AS pmfcp
                    INNER JOIN {$db->getTableName('catalog/category')} AS pmfc ON pmfc.entity_id = pmfcp.category_id
                    WHERE $condition AND pmfcp.product_id = e.entity_id)");
                $this->_collection->getSelect()->columns(array('cat_index_position' => $expr));
            }
            $this->_collection->setOrder($sortField, $direction);
            if (Mage::getStoreConfig($this->getConfigSource() . '/show') == 'specified') {
                $this->_collection->getSelect()->limit(Mage::getStoreConfig($this->getConfigSource() . '/count'));
            }
        }
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_collection);

        return $this;
    }
    protected function _beforeToHtml() {
        $this->getCollection()->load();
        Mage::getModel('review/review')->appendSummary($this->getCollection());
        $items = $this->getCollection()->getItems();
        if (Mage::getStoreConfig($this->getConfigSource() . '/sort') == 'random') {
            shuffle($items);
            if (Mage::getStoreConfig($this->getConfigSource() . '/show') == 'specified') {
                $items = array_slice($items, 0, Mage::getStoreConfig($this->getConfigSource() . '/count'));
            }
        }
        if ($this->_mainChild) {
            $this->_mainChild->setItems($items);
        }
        else {
            $this->setItems($items);
        }

        return parent::_beforeToHtml();
    }

    abstract public function getConfigSource();
    public function getConfigJson() {
        return '{}';
    }
}