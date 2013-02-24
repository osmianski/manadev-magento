<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Slider_Block_Slider extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    const DEFAULT_POSITION = 500;

    /**
     * @var ManaPro_Slider_Model_Product[]
     */
    protected $_products = array();

    /**
     * @var ManaPro_Slider_Model_Cmsblock[]
     */
    protected $_cmsBlocks = array();

    /**
     * @var ManaPro_Slider_Model_Htmlblock[]
     */
    protected $_htmlBlocks = array();

    /**
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected $_productCollection;

    /**
     * @var Mage_Cms_Model_Mysql4_Block_Collection
     */
    protected $_cmsBlockCollection;

    /**
     * @var string[]
     */
    protected $_contentBlockNames = array();

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('manapro/slider/slider.phtml');
    }

    protected function _prepareLayout() {
        Mage::helper('mana_core/layout')->delayPrepareLayout($this);
        return parent::_prepareLayout();
    }
    public function delayedPrepareLayout() {
//        foreach (Mage::helper('manapro_slider')->getDefaultSettings() as $field => $defaultValue) {
//            if (!$this->hasData($field)) {
//                $this->setData($field, $defaultValue);
//            }
//        }

        $children = array();
        $this
            ->_addManuallySelectedProducts()
            ->_addManuallySelectedCmsBlocks()
            ->_addHtmlBlocks()
            ->_addFeaturedProducts()
            ->_loadProductCollection()
            ->_loadCmsBlockCollection()
            ->_createProductBlocks($children)
            ->_createCmsBlocks($children)
            ->_createHtmlBlocks($children)
            ->_sortChildBlocks($children)
            ->_addChildBlocksToThisSlider($children)
            ->_addNavigationBlocks();
        return $this;
    }
    protected function _addManuallySelectedProducts() {
        if ($productJson = $this->getProductsJson()) {
            $productJson = htmlspecialchars_decode($productJson);
            if ($products = json_decode($productJson, true)) {
                foreach ($products as $productData) {
                    /* @var $product ManaPro_Slider_Model_Product */
                    $product = Mage::getModel('manapro_slider/product');
                    $product->setData($productData);
                    if (!isset($this->_products[$product->getProductId()])) {
                        $this->_products[$product->getProductId()] = $product;
                    }
                }
            }
        }
        return $this;
    }
    protected function _addManuallySelectedCmsBlocks() {
        if ($blockJson = $this->getCmsblocksJson()) {
            $blockJson = htmlspecialchars_decode($blockJson);
            if ($blocks = json_decode($blockJson, true)) {
                foreach ($blocks as $blockData) {
                    /* @var $block ManaPro_Slider_Model_Cmsblock */
                    $block = Mage::getModel('manapro_slider/cmsblock');
                    $block->setData($blockData);
                    if (!isset($this->_cmsBlocks[$block->getBlockId()])) {
                        $this->_cmsBlocks[$block->getBlockId()] = $block;
                    }
                }
            }
        }
        return $this;
    }
    protected function _addHtmlBlocks() {
        if ($blockJson = $this->getHtmlblocksJson()) {
            $blockJson = htmlspecialchars_decode($blockJson);
            if ($blocks = json_decode($blockJson, true)) {
                $index = 0;
                foreach ($blocks as $blockData) {
                    /* @var $block ManaPro_Slider_Model_Htmlblock */
                    $block = Mage::getModel('manapro_slider/htmlblock');
                    $block->setData($blockData)->setIndex($index);
                    $this->_htmlBlocks[] = $block;
                    $index++;
                }
            }
        }
        return $this;
    }
    protected function _addFeaturedProducts() {
        return $this;
    }
    protected function _loadProductCollection() {
        $this->_productCollection = Mage::getResourceModel('catalog/product_collection');
        $this->_productCollection
                ->setStoreId(Mage::app()->getStore()->getId())
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents();

        $ids = array();
        foreach ($this->_products as $product) {
            /* @var $product ManaPro_Slider_Model_Product */
            $ids[] = $product->getProductId();
        }
        $this->_productCollection->addIdFilter($ids);
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_productCollection);

        $this->_productCollection->load();
        Mage::getModel('review/review')->appendSummary($this->_productCollection);

        foreach ($this->_products as $product) {
            /* @var $product ManaPro_Slider_Model_Product */
            if ($catalogProduct = $this->_productCollection->getItemById($product->getProductId())) {
                $product->setProduct($catalogProduct);
            }
        }

        return $this;
    }
    protected function _loadCmsBlockCollection() {
        $this->_cmsBlockCollection = Mage::getResourceModel('cms/block_collection');
        $this->_cmsBlockCollection
            ->addStoreFilter(Mage::app()->getStore())
            ->addFieldToFilter('is_active', 1);


        $ids = array();
        foreach ($this->_cmsBlocks as $block) {
            /* @var $block ManaPro_Slider_Model_Cmsblock */
            $ids[] = $block->getBlockId();
        }
        $this->_cmsBlockCollection->addFieldToFilter('main_table.block_id', array('in' => $ids));

        $this->_cmsBlockCollection->load();
        foreach ($this->_cmsBlocks as $block) {
            /* @var $block ManaPro_Slider_Model_Cmsblock */
            if ($cmsBlock = $this->_cmsBlockCollection->getItemById($block->getBlockId())) {
                $block->setBlock($cmsBlock);
            }
        }

        return $this;
    }

    protected function _createProductBlocks(&$children) {
        foreach ($this->_products as $product) {
            /* @var $product ManaPro_Slider_Model_Product */
            $displayOptions = $product->getDisplayOptions();
            /* @var $block ManaPro_Slider_Block_Product */
            $block = $this->getLayout()->createBlock((string)$displayOptions->block, $product->getBlockName($this), array(
                'product' => $product,
                'display_options' => $displayOptions,
            ));
            $block->init();
            $children[] = $block;
        }
        return $this;
    }
    protected function _createCmsBlocks(&$children) {
        foreach ($this->_cmsBlocks as $cmsBlock) {
            /* @var $cmsBlock ManaPro_Slider_Model_Cmsblock */
            $displayOptions = $cmsBlock->getDisplayOptions();
            /* @var $block ManaPro_Slider_Block_Cmsblock */
            $block = $this->getLayout()->createBlock((string)$displayOptions->block, $cmsBlock->getBlockName($this), array(
                'block' => $cmsBlock,
                'display_options' => $displayOptions,
            ));
            $block->init();
            $children[] = $block;
        }
        return $this;
    }
    protected function _createHtmlBlocks(&$children) {
        foreach ($this->_htmlBlocks as $htmlBlock) {
            /* @var $htmlBlock ManaPro_Slider_Model_Htmlblock */
            $displayOptions = $htmlBlock->getDisplayOptions();
            /* @var $block ManaPro_Slider_Block_Htmlblock */
            $block = $this->getLayout()->createBlock((string)$displayOptions->block, $htmlBlock->getBlockName($this), array(
                'block' => $htmlBlock,
                'display_options' => $displayOptions,
            ));
            $block->init();
            $children[] = $block;
        }
        return $this;
    }
    protected function _sortChildBlocks(&$children) {
        usort($children, array($this, 'compareChildBlocks'));
        return $this;
    }
    /**
     * @param ManaPro_Slider_Block_Abstract $a
     * @param ManaPro_Slider_Block_Abstract $b
     * @return int
     */
    public function compareChildBlocks($a, $b) {
        if ($a->getPosition() < $b->getPosition()) return -1;
        if ($a->getPosition() > $b->getPosition()) return 1;
        return 0;
    }
    protected function _addChildBlocksToThisSlider($children) {
        foreach ($children as $childBlock) {
            /* @var $childBlock ManaPro_Slider_Block_Abstract */
            $this->_contentBlockNames[] = $childBlock->getChildName();
            $this->setChild($childBlock->getChildName(), $childBlock);
        }
        return $this;
    }
    protected function _addNavigationBlocks() {
        $slider = $this;
        if (($prevNext = $slider->getData('prev_next')) && ($xml = Mage::getConfig()->getNode('manapro_slider/navigation/'. $prevNext))) {
            $blockType = isset($xml->prev_block) ? (string)$xml->prev_block : 'core/template';
            if ($prevChild = $this->getLayout()->createBlock($blockType, $this->getNameInLayout() . '.prev')) {
                if (isset($xml->template_prev)) {
                    $prevChild->setTemplate((string)$xml->template_prev);
                    $this->append($prevChild, 'prev');
                    $prevChild->addToParentGroup('floating');
                }
            }
            $blockType = isset($xml->next_block) ? (string)$xml->next_block : 'core/template';
            if ($nextChild = $this->getLayout()->createBlock($blockType, $this->getNameInLayout() . '.next')) {
                if (isset($xml->template_next)) {
                    $nextChild->setTemplate((string)$xml->template_next);
                }
                $this->append($nextChild, 'next');
                $nextChild->addToParentGroup('floating');
            }

        }

        if (($switch = $slider->getData('fast_switch')) && ($xml = Mage::getConfig()->getNode('manapro_slider/switch/' . $switch))) {
            $blockType = isset($xml->block) ? (string)$xml->block : 'core/template';
            if ($child = $this->getLayout()->createBlock($blockType, $this->getNameInLayout() . '.switch')) {
                if (isset($xml->template)) {
                    $child->setTemplate((string)$xml->template);
                    $this->append($child, 'switch');
                    $child->addToParentGroup($slider->getData('fast_switch_position'));
                }
            }
        }

        return $this;
    }
    public function addProduct($id, $position = ManaPro_Slider_Block_Slider::DEFAULT_POSITION, $display = null) {
        if (!isset($this->_products[$id])) {
            /* @var $product ManaPro_Slider_Model_Product */
            $product = Mage::getModel('manapro_slider/product');
            $product->setProductId($id)->setPosition($position);
            if ($display) {
                $product->setDisplay($display);
            }
            $this->_products[$id] = $product;
        }
        return $this;
    }

    public function getContentBlocks() {
        $result = array();
        foreach ($this->_contentBlockNames as $contentBlockName) {
            $result[] = $this->getChild($contentBlockName);
        }
        return $result;
    }


    public function getSliderId() {
        if (!($result = parent::getSliderId())) {
            $result = sprintf('m-%s', $this->getNameInLayout());
        }
        return $result;
    }

    public function getConfigJson() {
        $result = array();
        $slider = $this;
        $result['rotationInterval'] = $slider->getData('effect_rotation_interval');

        foreach (array('hide', 'show') as $op) {
            $effect = $slider->getData(sprintf('effect_%s', $op));
            switch ($effect) {
                case 'none':
                    $result[$op . 'Effect'] = false;
                    break;
                case 'random':
                    $result[$op . 'Effect'] = false;
                    $result[$op . 'RandomEffect'] = true;
                    $result[$op . 'RandomEffects'] = explode(',', $slider->getData(sprintf('effect_%s_random', $op)));
                    break;
                default:
                    $xml = Mage::getConfig()->getNode('manapro_slider/effect/' . $effect);
                    if (isset($xml->model)) {
                        $model = Mage::getModel((string)$xml->model);
                        $result[$op . 'Effect'] = $model->getEffect($effect);
                        $result[$op . 'EffectOptions'] = $model->getEffectOptions($effect, $slider, $op);
                    } else {
                        $result[$op . 'Effect'] = $effect;
                    }
                    break;
            }
            $result[$op . 'EffectTimer'] = $slider->getData(sprintf('effect_%s_interval', $op));
        }

        if (($switch = $slider->getData('fast_switch')) && ($xml = Mage::getConfig()->getNode('manapro_slider/switch/' . $switch))) {
            $result['helper'] = "#{$this->getSliderId()}.m-slider-banner .m-switch ol";
            $result['helperInteraction'] = $slider->getData('fast_switch_event');

        }
        if (($prevNext = $slider->getData('prev_next')) && ($xml = Mage::getConfig()->getNode('manapro_slider/navigation/' . $prevNext))) {
            $result['previousItemElement'] = "#{$this->getSliderId()}.m-slider-banner .m-navigation-prev";
            $result['nextItemElement'] = "#{$this->getSliderId()}.m-slider-banner .m-navigation-next";
        }
        $result['autoStart'] = $slider->getData('auto_start') ? true : false;
        $result['randomStart'] = $slider->getData('random_start') ? true : false;
        $result['shuffle'] = $slider->getData('shuffle') ? true : false;

        return json_encode($result);
    }
    public function getItemCount() {
        $result = 0;
        foreach ($this->getContentBlocks() as $contentBlock) {
            if ($contentBlock->isVisible()) {
                $result++;
            }
        }
        return $result;
    }
}