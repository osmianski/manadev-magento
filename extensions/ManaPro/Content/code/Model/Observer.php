<?php
/** 
 * @category    Mana
 * @package     ManaPro_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_Content_Model_Observer {

    /**
	 * Add client side data to mana content's TabContainer block (handles event "core_block_abstract_prepare_layout_after")
	 * @param Varien_Event_Observer $observer
	 */
    public function addDataToTabContainer($observer) {
        $block = $observer->getBlock();
        if(get_class($block) == "Mana_Content_Block_Adminhtml_Book_TabContainer") {
            $data = $block->getData('m_client_side_block');
            $url = Mage::getModel('adminhtml/url')->getUrl('adminhtml/manapro_content_book/relatedProductGridSelection');
            $arr = array(
                'related_product_grid_selection_url' => $url,
            );
            $block->setData('m_client_side_block', array_merge($data, $arr));
        }
    }

    /**
     * Add client side data to mana content's TabContainer block (handles event "m_load_related_products")
     * @param Varien_Event_Observer $observer
     */
    public function processRelatedProductIds($observer) {
        $ids = $observer->getRelatedProducts();
        $ids = ($ids) ? $ids : array();
        $this->contentProHelper()->processRelatedProductIds($ids);
    }

    /**
     * Save related products (handles event "m_saved")
     * @param Varien_Event_Observer $observer
     */
    public function saveRelatedProducts($observer) {
        $related_products = $observer->getRelatedProducts();
        /** @var Mana_Content_Model_Page_GlobalCustomSettings $model */
        $model = $observer->getObject();
        $delete_id = array();
        foreach ($related_products as $key => $id) {
            if (substr($id, 0, 1) == "-") {
                unset($related_products[$key]);
                array_push($delete_id, substr($id, 1, strlen($id) - 1));
            }
        }
        $global_id = $model->getGlobalId($model->getId());
        $collection = Mage::getResourceModel("manapro_content/page_relatedProduct_collection");
        $collection->unlinkProducts($global_id, $delete_id);
        $collection->linkProducts($global_id, $related_products);
    }

    public function validateTags($observer) {
        $fields = $observer->getFields();
        if(isset($fields['tags'])) {
            Mage::getModel('manapro_content/page_tag')->validateTag($fields['tags']['value']);
        }
    }

    public function enableCopyAndReference($observer) {
        $treeOptions = $observer->getOptions();
        $newOptions = array(
            'dnd' => array(
                'copy' => true,
                'reference' => true,
            )
        );
        $treeOptions->addData($newOptions);
    }

    /**
     * Add field `canonical_book_page` to Mana_Seo_Block_Adminhtml_Schema_CanonicalForm (handles event "m_crud_form")
     * @param Varien_Event_Observer $observer
     */
    public function addSettingIncludeFiltersOnCanonicalLinks($observer) {
        if(get_class($observer->getForm()) == "Mana_Seo_Block_Adminhtml_Schema_CanonicalForm") {
            /** @var Mana_Seo_Block_Adminhtml_Schema_CanonicalForm $canonicalForm */
            $canonicalForm = $observer->getForm();
            $form = $canonicalForm->getForm();
            foreach($form->getElements() as $fieldset) {
                if($fieldset->getId() == "mfs_canonical") {
                    $canonicalForm->addField($fieldset, 'canonical_book_page', 'select', array(
                        'options' => $canonicalForm->getYesNoSourceModel()->getOptionArray(),
                        'label' => $canonicalForm->__('Canonical URL does not Contain Filters in CMS Book Page'),
                        'name' => 'canonical_book_page',
                        'required' => true,
                    ));
                    break;
                }
            }
        }
    }

    #region Dependencies
    /**
     * @return Mana_Content_Helper_Data
     */
    public function contentHelper() {
        return Mage::helper('mana_content');
    }

    /**
     * @return ManaPro_Content_Helper_Data
     */
    public function contentProHelper() {
        return Mage::helper('manapro_content');
    }
    #endregion
}