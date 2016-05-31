<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This block shows total inventory to be distributed among rep;resenting products
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Block_Header extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm() {
        /* @var $product Mage_Catalog_Model_Product */ $product = Mage::registry('product');
		
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('product');
        
        $fieldset = $form->addFieldset('inventory', array(
        	'legend'=>Mage::helper('manapro_productfaces')->__('Inventory')
        ));
        
        $productId = Mage::registry('current_product')->getId();
        $representingEnabled = $productId ? Mage::getResourceModel('manapro_productfaces/link')->isRepresentedProduct($productId) : 0;
        $fieldset->addField('m_representing_enabled', 'select', array(
            'label'=> Mage::helper('manapro_productfaces')->__('Enable Multiple Representations of This Product'),
            'title'=> Mage::helper('manapro_productfaces')->__('Enable Multiple Representations of This Product'),
        	'note'=> Mage::helper('manapro_productfaces')->__('While not enabled, standard Magento inventory management handles stock of this product; once enabled, you can ditribute stock qty of this product between several products including this one.'),
        	'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        	'name'=>'m_representing_enabled',
            'value'=> $representingEnabled,
        ));
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$res = Mage::getSingleton('core/resource');
        $attributeId = $db->fetchOne("SELECT attribute_id FROM {$res->getTableName('eav_attribute')} 
			WHERE entity_type_id = {$product->getEntityTypeId()} 
			AND attribute_code = 'm_productfaces_clone_override'");
        $override = $db->fetchOne("SELECT value FROM {$res->getTableName('catalog_product_entity_int')}
        	WHERE attribute_id = $attributeId && entity_id = ".Mage::registry('current_product')->getId());
        if ($override === false) 
        {
        	$override = Mage::getStoreConfigFlag('manapro_productfaces/cloning/override') ? '3' : '2';
        }
        $fieldset->addField('m_productfaces_clone_override', 'select', array(
            'label'=> Mage::helper('manapro_productfaces')->__('Override Existing Product Attributes'),
            'title'=> Mage::helper('manapro_productfaces')->__('Override Existing Product Attributes'),
        	'note'=> Mage::helper('manapro_productfaces')->__('If set, attributes of an existing product added as a representing product will be overridden (if confirmed).'),
        	'values' => Mage::getSingleton('manapro_productfaces/source_yesnoglobal')->toOptionArray(),
        	'name'=>'m_productfaces_clone_override',
            'value'=> $override,
        ));
        $fieldset->addField('m_productfaces_cloning_override_decision', 'hidden', array(
        	'name'=>'m_productfaces_cloning_override_decision',
            'value'=> '',
        ));
        $fieldset->addField('m_productfaces_cloning_override_ids', 'hidden', array(
        	'name'=>'m_productfaces_cloning_override_ids',
            'value'=> '',
        ));
        
        $qty = $product->getStockItem() ? $product->getStockItem()->getQty() : 0;
        $fieldset->addField('qty', 'label', array(
            'label'=> Mage::helper('manapro_productfaces')->__('Total Represented Quantity'),
            'title'=> Mage::helper('manapro_productfaces')->__('Total Represented Quantity'),
        	'note'=> Mage::helper('manapro_productfaces')->__('Equals quantity in stock'),
        	'name'=>'qty',
            'bold'=>true,
            'value'=>$qty,
        	'value_class' => 'm_representing_header_qty',
        ));
        
        $this->setForm($form);
        return $this;
	}
}