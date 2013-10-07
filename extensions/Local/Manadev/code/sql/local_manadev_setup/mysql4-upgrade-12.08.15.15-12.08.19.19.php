<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$attributes = array(
    'm_company_code' => array(
        'input' => 'text',
        'type' => 'varchar',
        'label' => 'Company Code',
        'sort_order' => 31,
        'required' => false,
    ),
    'm_vat_number' => array(
        'input' => 'text',
        'type' => 'varchar',
        'label' => 'VAT Number',
        'sort_order' => 32,
        'required' => false,
    )
);
$customerAddress = (int)$installer->getEntityTypeId('customer_address');
$attributeIds = array();
$select = $this->getConnection()->select()
        ->from(
    array('ea' => $this->getTable('eav/attribute')),
    array('entity_type_id', 'attribute_code', 'attribute_id'))
        ->where('ea.entity_type_id IN(?)', array($customerAddress));
foreach ($this->getConnection()->fetchAll($select) as $row) {
    $attributeIds[$row['entity_type_id']][$row['attribute_code']] = $row['attribute_id'];
}
$data = array();

foreach ($attributes as $attributeCode => $attribute) {
    $attributeId = $attributeIds[$customerAddress][$attributeCode];
    $attribute['system'] = isset($attribute['system']) ? $attribute['system'] : true;
    $attribute['visible'] = isset($attribute['visible']) ? $attribute['visible'] : true;
    if (false === ($attribute['system'] == true && $attribute['visible'] == false)) {
        $usedInForms = array(
            'adminhtml_customer_address',
            'customer_address_edit',
            'customer_register_address'
        );
        foreach ($usedInForms as $formCode) {
            $data[] = array(
                'form_code' => $formCode,
                'attribute_id' => $attributeId
            );
        }
    }
}

if ($data) {
    $this->getConnection()->insertMultiple($this->getTable('customer/form_attribute'), $data);
}


$installer->endSetup();