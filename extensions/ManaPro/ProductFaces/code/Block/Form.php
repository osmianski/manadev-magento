<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This form shows and allows to edit backlink info in representing product link
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Block_Form extends Mage_Adminhtml_Block_Widget_Form {
	protected $_linkRenderer;
	
	protected function _prepareForm() {
        /* @var $product Mage_Catalog_Model_Product */ $product = Mage::registry('product');
		/* @var $representedProduct Mage_Catalog_Model_Product */ $representedProduct = $this->getProductMRepresented();
		
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('product');
        
        $fieldset = $form->addFieldset('represented', array(
        	'legend'=>Mage::helper('manapro_productfaces')->__('Represented Product')
        ));
        
        $fieldset->addField('m_represented_id', 'hidden', array(
                'name'=>'m_represented_id',
                'value'=>$representedProduct->getId()
        ));
        
        $field = $fieldset->addField('m_represented_sku', 'label', array(
                'label'=> Mage::helper('catalog')->__('SKU'),
                'title'=> Mage::helper('catalog')->__('SKU'),
                'name'=>'m_represented_sku',
                'bold'=>true,
                'value'=>$representedProduct->getSku(),
        		'represented_entity_id' => $representedProduct->getId(), 
	        	'format'	=> '<a href="#" onclick="return m_warnAndNavigate(\''.$this->getUrl('adminhtml/catalog_product/edit', 
	        		array('_current' => true, 'id' => '$represented_entity_id')).'\')">$value</a>',
        ));
        $field->setRenderer($this->_linkRenderer);
        
        $field = $fieldset->addField('m_represented_name', 'label', array(
                'label'=> Mage::helper('catalog')->__('Name'),
                'title'=> Mage::helper('catalog')->__('Name'),
                'name'=>'m_represented_name',
                'bold'=>true,
                'value'=>$representedProduct->getName(),
        		'represented_entity_id' => $representedProduct->getId(), 
	        	'format'	=> '<a href="#" onclick="return m_warnAndNavigate(\''.$this->getUrl('adminhtml/catalog_product/edit', 
	        		array('_current' => true, 'id' => '$represented_entity_id')).'\')">$value</a>',
        ));
        $field->setRenderer($this->_linkRenderer);
        
        $fieldset->addField('m_represented_price', 'label', array(
                'label'=> Mage::helper('catalog')->__('Price'),
                'title'=> Mage::helper('catalog')->__('Price'),
                'name'=>'m_represented_price',
                'bold'=>true,
                'value'=>Mage::app()->getStore()->formatPrice($representedProduct->getPrice(), false)
        ));
        
        $fieldset = $form->addFieldset('link', array(
        	'legend'=>Mage::helper('manapro_productfaces')->__('Inventory Sharing Options')
        ));
        
        $fieldset->addField('m_represented_parts', 'text', array(
                'label'=> Mage::helper('manapro_productfaces')->__('Parts'),
                'title'=> Mage::helper('manapro_productfaces')->__('Parts'),
                'name'=>'m_represented_parts',
                'value'=>(string)($representedProduct->getMParts()*1)
        ));
        
        $fieldset->addField('m_represented_unit', 'select', array(
                'label'=> Mage::helper('manapro_productfaces')->__('Unit of Measure'),
                'title'=> Mage::helper('manapro_productfaces')->__('Unit of Measure'),
                'name'=>'m_represented_unit',
                'value'=>$representedProduct->getMUnit(),
        		'options' => Mage::getModel('manapro_productfaces/source_unit')->getOptionArray(),
        ));
        /*
        $fieldset = $form->addFieldset('other', array(
        	'legend'=>Mage::helper('manapro_productfaces')->__('Other')
        ));
        
        $fieldset->addField('m_represented_external_id', 'text', array(
                'label'=> Mage::helper('manapro_productfaces')->__('External ID'),
                'title'=> Mage::helper('manapro_productfaces')->__('External ID'),
                'name'=>'m_represented_external_id',
                'value'=>$representedProduct->getMExternalId(),
        ));
        */
        $this->setForm($form);
        
        return $this;
	}
	protected function _prepareLayout() {
		parent::_prepareLayout();
		$this->_linkRenderer = $this->getLayout()->createBlock('manapro_productfaces/form_link');
		return $this;
	}
}