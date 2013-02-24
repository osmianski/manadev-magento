<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_Video_Model_Observer {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "core_block_abstract_prepare_layout_after")
     * @param Varien_Event_Observer $observer
     */
    public function addVideoTab($observer) {
        /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        /* @var $block Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs */
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs) {
            $product = $block->getProduct();
            if (!($setId = $product->getAttributeSetId())) {
                $setId = $block->getRequest()->getParam('set', null);
            }
            if ($setId) {
                $tabBefore = 'inventory';
                $groupCollection = Mage::getResourceModel('eav/entity_attribute_group_collection')
                        ->setAttributeSetFilter($setId)
                        ->load();
                foreach ($groupCollection as $group) {
                    if (Mage::helper('catalog')->__($group->getAttributeGroupName()) == Mage::helper('catalog')->__('Images')) {
                        $tabBefore = 'group_' . $group->getId();
                        break;
                    }
                }
                $block->addTab('m_video', array_merge(array(
                    'label' => Mage::helper('manapro_video')->__('Videos'),
                    'url' => $block->getUrl('adminhtml/product_video/tab', array('_current' => true)),
                    'class' => 'ajax',
                ), $tabBefore ? array(
                    'after' => $tabBefore,
                ) : array()));
                $block->setMContainsVideoTab(true);
            }
        }
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "core_block_abstract_to_html_before")
     * @param Varien_Event_Observer $observer
     */
    public function beginEditingSession($observer) {
        /* @var $block Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs */
        $block = $observer->getEvent()->getBlock();

        if ($block->getMContainsVideoTab()) {
            if (!Mage::helper('mana_db')->getInEditing()) {
                Mage::helper('mana_db')->setInEditing();
                Mage::helper('mana_core/js')->options('edit-form', array('editSessionId' => Mage::helper('mana_db')->beginEditing()));
            }
        }
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "catalog_product_prepare_save")
     * @param Varien_Event_Observer $observer
     */
    public function addEditedVideos($observer) {
        /* @var $product Mage_Catalog_Model_Product */
        $object = $observer->getEvent()->getProduct();

        $this->_unserializeVideos($object);
    }
    protected function _unserializeVideos($object) {
        $request = Mage::app()->getRequest();
        if (($edit = $request->getParam('mVideoGrid_table')) && !$object->getMVideoData()) {
            $edit = json_decode(base64_decode($edit), true);
            Mage::helper('mana_admin')->processPendingEdits('manapro_video/video', $edit);
            $object->setMVideoData($edit);
        }
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "catalog_product_validate_after", "catalog_product_save_before")
     * @param Varien_Event_Observer $observer
     */
    public function validateVideos($observer) {
        /* @var $product Mage_Catalog_Model_Product */
        $object = $observer->getEvent()->getProduct();
        $request = Mage::app()->getRequest();

        // here is the hack for dumb validate action which does not provide a way to inject POST parsing code through
        // dedicated event
        if ($request && $request->getControllerName() == 'catalog_product' &&
                $request->getActionName() == 'validate' && $request->getModuleName() == 'admin')
        {
            $this->_unserializeVideos($object);
        }

        try {
            Mage::helper('mana_admin')->validateEditedData($object, 'm_video_data', 'manapro_video/video');
        }
        catch (Mana_Db_Exception_Validation $e) {
            throw new Mage_Core_Exception(implode('<br />', $e->getErrors()));
        }

        $baseVideo = Mage::helper('manapro_video')->getBaseVideo(Mage::helper('manapro_video')->getBackendVideos(
            $object, $object->getMVideoData()), true);

        if (is_array($baseVideo)) {
            throw new Mage_Core_Exception(Mage::helper('manapro_video')->__('You can assign only one base video per product.'));
        }
        elseif ($baseVideo && $object->getImage() && $object->getImage() != 'no_selection') {
            throw new Mage_Core_Exception(Mage::helper('manapro_video')->__('You can assign either base image or base video, but not both.'));
        }
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "catalog_product_save_after")
     * @param Varien_Event_Observer $observer
     */
    public function saveVideos($observer) {
        /* @var $product Mage_Catalog_Model_Product */
        $object = $observer->getEvent()->getProduct();

        Mage::helper('mana_admin')->saveEditedData($object, 'm_video_data', 'manapro_video/video',
            array($this, '_beforeSaveVideo'));
    }
    public function _beforeSaveVideo($object, $model, $editModel) {
        $model->setProductId($object->getId());
    }
}