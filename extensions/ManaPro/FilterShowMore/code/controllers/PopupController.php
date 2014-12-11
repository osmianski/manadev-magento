<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterShowMore
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterShowMore_PopupController extends Mage_Core_Controller_Front_Action {
    public function viewAction() {
        $request = $this->getRequest();
        $layout = $this->getLayout();
        $id = $request->getParam('m-show-more-popup');
        $filterOptions = Mage::getModel('mana_filters/filter2_store');
        /* @var $filterOptions Mana_Filters_Model_Filter2_Store */
        $filterOptions->load($id);
        $displayOptions = $filterOptions->getDisplayOptions();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        /* @var $filterHelper Mana_Filters_Helper_Data */
        $filterHelper = Mage::helper(strtolower('Mana_Filters'));

        switch (Mage::registry('m_original_route_path')) {
            case 'catalogsearch/result/index':
                $viewBlock = $layout->addBlock('mana_filters/search', 'layer');
                $viewBlock->setMode('search');
                $filterHelper->setMode('search');
                break;
            case 'mana/optionPage/view':
                $category = Mage::getModel('catalog/category')->load($request->getParam('m-show-more-cat'));
                if ($category->getId()) {
                        Mage::helper('mana_filters')->getLayer()->setCurrentCategory($category);
                }
                $viewBlock = $layout->addBlock('mana_filters/view', 'layer');
                $viewBlock->setMode('category');
                $filterHelper->setMode('category');
                if (Mage::helper('mana_attributepage/optionPage')->initOptionPage((int)$this->getRequest()->getParam('id', false))) {
                    Mage::getSingleton('mana_attributepage/layer')->apply();
                }
                break;
            default:
                $category = Mage::getModel('catalog/category')->load($request->getParam('m-show-more-cat'));
                if ($category->getId()) {
                        Mage::helper('mana_filters')->getLayer()->setCurrentCategory($category);
                }
                $viewBlock = $layout->addBlock('mana_filters/view', 'layer');
                $viewBlock->setMode('category');
                $filterHelper->setMode('category');
                break;
        }

        Mage::dispatchEvent(
            'controller_action_layout_generate_blocks_after',
            array('action' => $this, 'layout' => $this->getLayout())
        );

        $block = $viewBlock->getChild($filterOptions->getCode() . '_filter');
        $template = $block->getTemplate();
        $block->setTemplate(str_replace('.phtml', '_popup.phtml', $template));
        Mage::register('m_showing_filter_popup', true);

        $this->getResponse()->setBody($block->toHtml());
    }
}