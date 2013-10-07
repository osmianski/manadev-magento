<?php

class Local_Manadev_Block_Order_Address_Form extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm() {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->addField('entity_id', 'hidden', array('name'  => 'entity_id'));
        $form->addField('redirect_to', 'hidden', array('name'  => 'redirect_to'));
        
        $fieldset = $form->addFieldset('person_fieldset', array('legend'=>'Person'));

        $fieldset->addField('firstname', 'text',
            array(
                'name'  => 'firstname',
                'label' => 'Name',
                'title' => 'Name',
            )
        );
        
        $fieldset = $form->addFieldset('company_fieldset', array('legend'=>'Company'));
        
        $fieldset->addField('company', 'text',
            array(
                'name'  => 'company',
                'label' => 'Company Name',
                'title' => 'Company Name',
            )
        );
        
        $fieldset->addField('m_company_code', 'text',
            array(
                'name'  => 'm_company_code',
                'label' => 'Company Registration No',
                'title' => 'Company Registration No',
            )
        );
        
        $fieldset->addField('m_vat_number', 'text',
            array(
                'name'  => 'm_vat_number',
                'label' => 'VAT Registration No',
                'title' => 'VAT Registration No',
            )
        );
        
        $fieldset = $form->addFieldset('location_fieldset', array('legend'=>'Location'));
        
        $fieldset->addField('street', 'text',
            array(
                'name'  => 'street',
                'label' => 'Street',
                'title' => 'Street',
            )
        );
        
        $fieldset->addField('city', 'text',
            array(
                'name'  => 'city',
                'label' => 'City',
                'title' => 'City',
            )
        );
        
        $fieldset->addField('region', 'text',
            array(
                'name'  => 'region',
                'label' => 'Region',
                'title' => 'Region',
            )
        );
        
        $fieldset->addField('postcode', 'text',
            array(
                'name'  => 'postcode',
                'label' => 'Postcode',
                'title' => 'Postcode',
            )
        );
        
        $fieldset->addField('country_id', 'select',
            array(
                'name'  => 'country_id',
                'label' => 'Country',
                'title' => 'Country',
            	'values' => Mage::getResourceModel('directory/country_collection')
                        ->loadByStore(Mage::registry('order')->getStoreId())->toOptionArray()
            )
        );
        
        $form->setUseContainer(true)->setId('edit_form')->setAction($this->getUrl('*/*/save'));
        $this->setForm($form);
        return $this;
	}
    protected function _initFormValues()
    {
    	$this->getForm()->addValues(Mage::registry('address')->getData());
    	$this->getForm()->addValues(array('redirect_to' => Mage::registry('redirect_to')));
        return $this;
    }
}