<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAttributes
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

if (defined('COMPILER_INCLUDE_PATH')) {
    throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $this Mage_Core_Model_Resource_Setup */
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
if (method_exists($this->getConnection(), 'allowDdlCache')) {
    $this->getConnection()->allowDdlCache();
}
$connection = $installer->getConnection();

$installer->startSetup();

Mage::register('m_prevent_indexing_on_save', true, true);

/* @var $ratingObserver ManaPro_FilterAttributes_Resource_Rating */
$ratingObserver = Mage::getResourceModel('manapro_filterattributes/rating');
$ratingAttributeCode = $ratingObserver->getRatingAttributeCode();
$attributeOptions = array(
    0 => $ratingObserver->getOptionName(4),
    1 => $ratingObserver->getOptionName(3),
    2 => $ratingObserver->getOptionName(2),
    3 => $ratingObserver->getOptionName(1),
    4 => $ratingObserver->getOptionName(0));

$installer->addAttribute('catalog_product', $ratingAttributeCode, array(
    'group'         => 'General',
    'input'         => 'multiselect',
	'source'        => 'eav/entity_attribute_source_table',
    'type'          => 'varchar',
    'backend'       => 'eav/entity_attribute_backend_array',
    'label'         => 'Rating',
    'user_defined'  => true,
	'required'      => false,
    'is_configurable'  => 0,
    'searchable'    => false,
    'filterable'    => true,
    'filterable_in_search' => true,
    'comparable'    => true,
    'html_allowed_on_front' => true,
    'visible_in_advanced_search' => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
));

$model = Mage::getModel('eav/entity_attribute')
     ->load($installer->getAttributeId('catalog_product', $ratingAttributeCode));
// add options
$tableOptions = $installer->getTable('eav_attribute_option');
$tableOptionValues = $installer->getTable('eav_attribute_option_value');
$attributeId = $installer->getAttributeId('catalog_product', $ratingAttributeCode);

foreach ($attributeOptions as $sortOrder => $label) {
    $data = array(
        'attribute_id' => $attributeId,
        'sort_order' => $sortOrder,
    );
    $installer->getConnection()->insert($tableOptions, $data);
    $optionId = (int)$installer->getConnection()->lastInsertId($tableOptions, 'option_id');
    $data = array(
        'option_id' => $optionId,
        'store_id' => 0,
        'value' => $label,
    );
    $installer->getConnection()->insert($tableOptionValues, $data);
}

$model
     ->setDefaultValue($model->getSource()->getOptionId($ratingObserver->getOptionName(1)))->save();

/*$installer->addAttributeOption($options)*/;

/* @var $core Mana_Core_Helper_Data */
$core = Mage::helper('mana_core');

if ($core->isManadevLayeredNavigationInstalled()) {

    /* @var $dbHelper Mana_Db_Helper_Data */
    $dbHelper = Mage::helper('mana_db');

    /* @filter Mana_Filters_Model_Filter2 */
    $filter = Mage::getModel('mana_filters/filter2');


    if ($core->isManadevLayeredNavigationColorInstalled() ) {
        $dbHelper->updateDefaultableField($filter, 'display', Mana_Filters_Resource_Filter2::DM_DISPLAY, array(
            'display' => 'colors_label_one'), false);
        $dbHelper->updateDefaultableField($filter, 'image_width', Mana_Filters_Resource_Filter2::DM_IMAGE_WIDTH, array(
            'image_width' => 69), false);
        $dbHelper->updateDefaultableField($filter, 'image_height', Mana_Filters_Resource_Filter2::DM_IMAGE_HEIGHT, array(
            'image_height' => 14), false);
        $dbHelper->updateDefaultableField($filter, 'image_border_radius', Mana_Filters_Resource_Filter2::DM_IMAGE_BORDER_RADIUS, array(
            'image_border_radius' => 0), false);
        $dbHelper->updateDefaultableField($filter, 'state_width', Mana_Filters_Resource_Filter2::DM_STATE_WIDTH, array(
            'state_width' => 69), false);
        $dbHelper->updateDefaultableField($filter, 'state_height', Mana_Filters_Resource_Filter2::DM_STATE_HEIGHT, array(
            'state_height' => 14), false);
        $dbHelper->updateDefaultableField($filter, 'state_border_radius', Mana_Filters_Resource_Filter2::DM_STATE_BORDER_RADIUS, array(
            'state_border_radius' => 0), false);
        $filter
            ->setData('code', $ratingAttributeCode)
            ->setData('type', 'attribute');
        $filter->save();

        $model = Mage::getModel('eav/entity_attribute')
             ->load($installer->getAttributeId('catalog_product', $ratingAttributeCode));

        /* @var $files Mana_Core_Helper_Files */
        $files = Mage::helper(strtolower('Mana_Core/Files'));
        $skinBaseDir = Mage::getDesign()->getSkinBaseDir(array('_package' => 'base'));
        foreach ($attributeOptions as $i=>$attributeOption) {
            $imageFile = 'filterattributes/i_rating-' . (4 - $i) . 'star.gif';
            $sourcePath = $skinBaseDir . "/images/manapro_" . $imageFile;
            $targetPath = $files->getFilename($imageFile, 'image', true);
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
            copy($sourcePath, $targetPath);

            /* @var $filterValue Mana_Filters_Model_Filter2_Value */
            $filterValue = Mage::getModel('mana_filters/filter2_value');
            $filterValue->loadByFilterPosition($filter->getId(), $i);
            $dbHelper->updateDefaultableField($filterValue, 'normal_image', Mana_Filters_Resource_Filter2_Value::DM_NORMAL_IMAGE, array(
                'normal_image' => $imageFile), false);
            $dbHelper->updateDefaultableField($filterValue, 'selected_image', Mana_Filters_Resource_Filter2_Value::DM_SELECTED_IMAGE, array(
                'selected_image' => $imageFile), false);
            $dbHelper->updateDefaultableField($filterValue, 'normal_hovered_image', Mana_Filters_Resource_Filter2_Value::DM_NORMAL_HOVERED_IMAGE, array(
                'normal_hovered_image' => $imageFile), false);
            $dbHelper->updateDefaultableField($filterValue, 'selected_hovered_image', Mana_Filters_Resource_Filter2_Value::DM_SELECTED_HOVERED_IMAGE, array(
                'selected_hovered_image' => $imageFile), false);
            $dbHelper->updateDefaultableField($filterValue, 'state_image', Mana_Filters_Resource_Filter2_Value::DM_STATE_IMAGE, array(
                'state_image' => $imageFile), false);


            $optionId = $model->getSource()->getOptionId($attributeOption);
            $valueId = $connection->fetchOne($connection->select()
                ->from($installer->getTable('eav/attribute_option_value', 'value'))
                ->where('option_id = ? AND store_id = 0', $optionId));
            $filterValue
                ->setData('filter_id', $filter->getId())
                ->setData('option_id', $optionId)
                ->setData('value_id', $valueId);
            $filterValue->save();
        }
    }

    if (!Mage::registry('m_run_db_replication')) {
        if ($savedObjects = Mage::registry('m_objects_to_be_saved')) {
            Mage::unregister('m_objects_to_be_saved');
        }
        else {
            $savedObjects = array();
        }
        $savedObjects[] = $filter;
        Mage::register('m_objects_to_be_saved', $savedObjects);
        Mage::register('m_run_db_replication', true);
    }
}

Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_attribute')
    ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);

Mage::unregister('m_prevent_indexing_on_save');

if (method_exists($installer->getConnection(), 'disallowDdlCache')) {
    $installer->getConnection()->disallowDdlCache();
}
$installer->endSetup();
