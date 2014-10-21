<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This grid shows products representing product being edited
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Set grid params
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('m_representing_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * Retirve currently edited product model
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }

    protected $_collectionType;
    protected function _getCollectionType() {
    	if (!$this->_collectionType) {
	    	if ($this->getProductsMRepresenting()) {
	    		// we display this grid as a response to ajax action and we have got currently edited data from
	    		// browser in POST variable
	    		$this->_collectionType = 'manapro_productfaces/collection_edited';
	    	}
	    	elseif (Mage::getResourceModel('manapro_productfaces/link')->isRepresentedProduct($this->_getProduct()->getId())) {
	    		// there is representing products in database sharing this product's qty 
	    		$this->_collectionType = 'manapro_productfaces/collection';
	    	}
	    	else {
	    		// there are no representing products in database - this product currently behaves as standard Magento 
	    		// product
	    		$this->_collectionType = 'manapro_productfaces/collection_empty';
	    	}
    	}
    	return $this->_collectionType;
    } 
    protected function _createCollection() {
    	$collection = Mage::getResourceModel($this->_getCollectionType());
    	$product = $this->_getProduct();
    	
    	if ($clientData = $this->getProductsMRepresenting()) {
    		$collection->setClientData($clientData);
    	}
    	$collection->setProduct($product);
    	if ($this->_getCollectionType() == 'manapro_productfaces/collection') {
	        $collection
	        	->setLinkModel($product->getLinkInstance()->setLinkTypeId($collection->getRepresentingLinkTypeId()))
	        	->addAttributeToSelect('name')
	        	->setIsStrongMode()
	        	->getSelect()->distinct(true);
    	}
	    for ($i = 0; $i < 10; $i++) {
	    	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $product->getStoreId())) {
	        	$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
	        	$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
	        	foreach ($attributes as $attribute) {
					switch ($attribute->getFrontend()->getInputType()) {
				    	case 'price': 
				        case 'text': 
				        case 'select':
				        	$collection->addAttributeToSelect($attributeCode);
				        	break;
				    }
        		}
        	}
        }
    	return $collection;
    }
    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_createCollection());
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection() {
    	foreach ($this->getCollection() as $item) {
    		if ($item->getData('entity_id') == $this->_getProduct()->getId()) {
        		$item->setData('entity_id', 'this');
        	}
    		$item->setData('qty', $item->getData('qty')*1);
    		$item->setData('m_parts', (string)($item->getData('m_parts')*1));
		    for ($i = 0; $i < 10; $i++) {
		    	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $this->_getProduct()->getStoreId())) {
		        	$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
		        	$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
	        		foreach ($attributes as $attribute) {
				        switch ($attribute->getFrontend()->getInputType()) {
				        	case 'price': 
    							$item->setData($attributeCode, sprintf("%1.2f", $item->getData($attributeCode)));
				        		break;
				        }
	        		}
	        	}
		    }
    	}
    }
    
    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return false;
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        if (!$this->isReadonly()) {
            $this->addColumn('in_products', array(
                'header_css_class'  => 'a-center',
                'type'              => 'checkbox',
                'name'              => 'massaction',
                'align'             => 'center',
        		'column_css_class'	=> 'mc-massaction',
            	'filter' => false,
            	'value' => true,
            	'index' => 'entity_id',
			));
        }

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id',
        	'column_css_class'	=> 'mc-entity_id',
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku',
        	'format'	=> '<a href="#" onclick="return m_warnAndNavigate(\''.$this->getUrl('adminhtml/catalog_product/edit', 
        		array('_current' => true, 'id' => '$entity_id')).'\')">$sku</a>',
        	'column_css_class'	=> 'mc-sku',
        	'renderer'			=> 'manapro_productfaces/column_link',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name',
        	'format'	=> '<a href="#" onclick="return m_warnAndNavigate(\''.$this->getUrl('adminhtml/catalog_product/edit', 
        		array('_current' => true, 'id' => '$entity_id')).'\')">$name</a>',
        	'column_css_class'	=> 'mc-name',
        	'renderer'			=> 'manapro_productfaces/column_link',
        ));

        $this->addColumn('m_selling_qty', array(
            'header'            => Mage::helper('manapro_productfaces')->__('Selling Qty'),
            'name'              => 'm_selling_qty',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'm_selling_qty',
            'width'             => 60,
            'editable'          => true,
            'edit_only'         => true,
            'align'             => 'center',
        	'renderer'			=> 'adminhtml/widget_grid_column_renderer_input',
        	'column_css_class'	=> 'mc-m_selling_qty',
        ));

        $this->addColumn('m_parts', array(
            'header'            => Mage::helper('manapro_productfaces')->__('Parts'),
            'name'              => 'm_parts',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'm_parts',
            'width'             => 60,
            'editable'          => true,
            'edit_only'         => true,
            'align'             => 'center',
        	'renderer'			=> 'adminhtml/widget_grid_column_renderer_input',
        	'column_css_class'	=> 'mc-m_parts',
        ));

        $this->addColumn('m_unit', array(
            'header'            => Mage::helper('manapro_productfaces')->__('Unit of Measure'),
            'name'              => 'm_unit',
            'type'              => 'options',
            'index'             => 'm_unit',
            'width'             => 100,
        	'options'			=> Mage::getModel('manapro_productfaces/source_unit')->getOptionArray(),
            'editable'          => true,
            'edit_only'         => true,
            'align'             => 'center',
        	'renderer'			=> 'manapro_productfaces/column_unit',
        	'column_css_class'	=> 'mc-unit',
        ));
        
        $this->addColumn('position', array(
            'header'            => Mage::helper('manapro_productfaces')->__('Position'),
            'name'              => 'position',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'position',
            'width'             => 60,
            'editable'          => true,
            'edit_only'         => true,
            'align'             => 'center',
        	'renderer'			=> 'adminhtml/widget_grid_column_renderer_input',
        	'column_css_class'	=> 'mc-position',
        ));

        $this->addColumn('qty', array(
            'header'    => Mage::helper('catalog')->__('Estimated Qty'),
            'width'     => 40,
        	'index'     => 'qty',
        	'format'    => '<span class="m_representing_product_qty" id="m_representing_product_qty_$entity_id">$qty</span>',
        	'column_css_class'	=> 'mc-qty',
        ));

        for ($i = 0; $i < 10; $i++) {
        	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $this->_getProduct()->getStoreId())) {
        		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        		$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
        		foreach ($attributes as $attribute) {
        			$options = array(
			            'header'        => Mage::helper('catalog')->__($attribute->getFrontend()->getLabel()),
			            'name'              => $attributeCode,
			            'index'         => $attributeCode,
			            'editable'          => true,
			            'width'             => 60,
			            'edit_only'         => true,
			            'align'             => 'center',
        				'column_css_class'	=> 'mc-'.$attributeCode,
			        );
			        switch ($attribute->getFrontend()->getInputType()) {
			        	case 'price': 
			        		$options['type'] = 'number'; 
			        		$options['renderer'] = 'adminhtml/widget_grid_column_renderer_input'; 
			        		$options['validate_class'] = 'validate-number'; 
			        		break;
			        	case 'text': 
			        		$options['renderer'] = 'adminhtml/widget_grid_column_renderer_input'; 
			        		break;
			        	case 'select':
            				$options['type'] = 'options';
            				$options['renderer'] = 'adminhtml/widget_grid_column_renderer_select';
            				$options['options'] = Mage::getModel('manapro_productfaces/source_adapter')->setSource(
            					$attribute->getSource())->getOptionArray();
			        		break;
            			default:
			        		$options = null;
			        		break;
			        }
			        if ($options) {
				        $this->addColumn($attributeCode, $options);
			        }
        		}
        	}
        }
        return parent::_prepareColumns();
    }

    public function _getSkuColumnValue($row) {
    	if ($row['entity_id'] == 'this') {
    		return $row['sku'];
    	}
    	else {
    		return '<a href="#" onclick="return m_warnAndNavigate(\''.$this->getUrl('adminhtml/catalog_product/edit', 
        		array('_current' => true, 'id' => $row['entity_id'])).'\')">'.$row['sku'].'</a>';
    	}
    }
    public function _getNameColumnValue($row) {
    	if ($row['entity_id'] == 'this') {
    		return $row['name'];
    	}
    	else {
    		return '<a href="#" onclick="return m_warnAndNavigate(\''.$this->getUrl('adminhtml/catalog_product/edit', 
        		array('_current' => true, 'id' => $row['entity_id'])).'\')">'.$row['name'].'</a>';
    	}
    }
    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('adminhtml/representing_products/grid', array('_current' => true));
    }

    /**
     * Retrieve selected related products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getProductsMRepresenting();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedMRepresentingProducts());
        }
        if ($newProduct = Mage::registry('m_product_copy')) {
        	$products[] = $newProduct->getId();
        }
        return $products;
    }

    /**
     * Retrieve related products
     *
     * @return array
     */
    public function getSelectedMRepresentingProducts()
    {
    	/* @var $helper ManaPro_ProductFaces_Helper_Data */ $helper = Mage::helper(strtolower('ManaPro_ProductFaces'));
    	$products = array();
    	$attributeCodes = array('entity_id', 'm_unit', 'm_parts', 'position', 'sku', 'name', 'm_selling_qty');
	    for ($i = 0; $i < 10; $i++) {
	    	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $this->_getProduct()->getStoreId())) {
	        	$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
	        	$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
        		foreach ($attributes as $attribute) {
			        switch ($attribute->getFrontend()->getInputType()) {
			        	case 'price': 
			        	case 'text': 
			        	case 'select':
			        		$attributeCodes[] = $attributeCode; 
			        		break;
			        }
        		}
        	}
	    }
    	
	    $collection = $this->_createCollection(); 
        foreach ($collection as $product) {
        	$data = array();
        	foreach ($attributeCodes as $attribute) {
        		$data[$attribute] = $product->getData($attribute);
        	}
        	if ($data['entity_id'] == $this->_getProduct()->getId()) {
        		$data['entity_id'] = 'this';
        	}
    		$data['m_parts'] = (string)($data['m_parts']*1);
		    for ($i = 0; $i < 10; $i++) {
		    	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $this->_getProduct()->getStoreId())) {
		        	$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
		        	$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
	        		foreach ($attributes as $attribute) {
				        switch ($attribute->getFrontend()->getInputType()) {
				        	case 'price': 
    							$data[$attributeCode] = sprintf("%1.2f", $data[$attributeCode]*1);
				        		break;
				        }
	        		}
	        	}
		    }
        	$products[$data['entity_id']] = $data;
        }
        return $products;
    }

    public function getMainButtonsHtml()
    {
        $html = '';
        $html.= $this->getChildHtml('add_copy_button');
        $html.= $this->getChildHtml('add_existing_button');
        $html.= $this->getChildHtml('remove_button');
        $html .= parent::getMainButtonsHtml();
        return $html;
    }
    protected function _prepareLayout()
    {
        $this->setChild('add_copy_button', $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            	'label'     => Mage::helper('adminhtml')->__('Add Copy'), 
                'class'   => 'add m-add-copy',
            ))
        );
        $this->setChild('add_existing_button', $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            	'label'     => Mage::helper('adminhtml')->__('Add Existing'), 
                'class'   => 'add m-add-existing',
            ))
        );
        $this->setChild('remove_button', $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            	'label'     => Mage::helper('adminhtml')->__('Remove'), 
                'class'   => 'delete m-remove',
            ))
        );
        return parent::_prepareLayout();
    }
    protected $_varNameAddCopy   = 'addCopy';
    
}