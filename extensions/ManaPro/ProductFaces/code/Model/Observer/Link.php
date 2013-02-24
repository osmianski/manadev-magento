<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Observer_Link {
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Adds new tab to product editing page (handles event "core_block_abstract_prepare_layout_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function addRepresentingProductsTab($observer) {
		/* @var $block Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs */ $block = $observer->getEvent()->getBlock();
		if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs) {
			if (!$block->getProduct()->getId() ||
			    !in_array($block->getProduct()->getTypeId(), array(
			        Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
			    )))
			{
			    return;
			}
			
	        $block->addTab('m_representing', array(
	        	'label'     => Mage::helper('manapro_productfaces')->__('Representing Products'),
	        	'url'       => $block->getUrl('adminhtml/representing_products/tab', array('_current' => true)),
	        	'class'     => 'ajax',
		        'after'		=> 'categories',
	        ));
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Adds represented product column to product grid (handles event "core_block_abstract_to_html_before")
	 * @param Varien_Event_Observer $observer
	 */
	public function addProductGridColumn($observer) {
		/* @var $block Mage_Adminhtml_Block_Catalog_Product_Grid */ $block = $observer->getEvent()->getBlock();
		if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid || $block instanceof TBT_Enhancedgrid_Block_Catalog_Product_Grid) {
			if (Mage::getStoreConfigFlag('manapro_productfaces/general/show_sku_in_grid')) {
				Mage::register('m_add_represented_sku_to_product_collection', true);
		        $block->addColumnAfter('m_represented_sku',
		            array(
		                'header'=> Mage::helper('manapro_productfaces')->__('Represented Product'),
		                'index' => 'm_represented_sku',
		            	'filter_condition_callback' => array($this, 'addProductCollectionFilter'),
		        ), 'name');
		        $block->addColumnAfter('m_represented_qty',
		            array(
		                'header'=> Mage::helper('manapro_productfaces')->__('Represented Qty'),
		                'index' => 'm_represented_qty',
                		'type'  => 'number',
		            	'width' => '60px',
		            	'filter_condition_callback' => array($this, 'addProductCollectionQtyFilter'),
		            ), 'm_represented_sku');
			}
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Adds represented product column to product collection (handles event "catalog_product_collection_load_before")
	 * @param Varien_Event_Observer $observer
	 */
	public function addProductCollectionColumn($observer) {
		/* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */ $collection = $observer->getEvent()->getCollection();
		if (Mage::registry('m_add_represented_sku_to_product_collection')) {
			if (Mage::getStoreConfigFlag('manapro_productfaces/general/show_sku_in_grid')) {
				$this->_addProductCollectionColumn($collection);
			}
		}
	}
	
	protected function _addProductCollectionColumn($collection) {
		$linkTypeId = Mage::getResourceModel('manapro_productfaces/collection')->getRepresentingLinkTypeId();
		$collection->getSelect()
			->joinLeft(array('m_back_link' => Mage::getSingleton('core/resource')->getTableName('catalog/product_link')), 
				'(m_back_link.linked_product_id=e.entity_id) and (m_back_link.link_type_id = '.$linkTypeId.')', null)
			->joinLeft(array('m_back_linked_product' => Mage::getSingleton('core/resource')->getTableName('catalog/product')), 
				'm_back_linked_product.entity_id=m_back_link.product_id', null)
			->joinLeft(array('m_link' => Mage::getSingleton('core/resource')->getTableName('catalog/product_link')), 
				'(m_link.product_id=e.entity_id) and (m_link.link_type_id = '.$linkTypeId.')', null);
		$collection->getSelect()
			->distinct()
			->columns(array(
				'm_represented_sku' => new Zend_Db_Expr("IF(m_link.product_id IS NOT NULL,e.sku, m_back_linked_product.sku)"),
			));
		$collection->joinField('m_represented_qty',
                'cataloginventory/stock_item',
                'm_represented_qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
		
	}
	public function addProductCollectionFilter($collection, $column) {
		//$this->_addProductCollectionColumn($collection);
		$cond = $column->getFilter()->getCondition();
        if (in_array((string)$cond['like'], array('%real%', "'%real%'"))) {
			$collection->getSelect()->where('(m_back_link.link_id IS NULL OR m_link.link_id IS NOT NULL)');
		}
		else {
			$collection->getSelect()->where('IF(m_link.product_id IS NOT NULL,e.sku,m_back_linked_product.sku) like ?', $cond['like']);
		}
	}
	public function addProductCollectionQtyFilter($collection, $column) {
		//$this->_addProductCollectionColumn($collection);
		$cond = $column->getFilter()->getCondition();
		if (isset($cond['from'])) {
			$collection->getSelect()->where('e.m_represented_qty >= ?', $cond['from']);
		}
		if (isset($cond['to'])) {
			$collection->getSelect()->where('e.m_represented_qty <= ?', $cond['to']);
		}
		
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Similar to other links, deserializes representing products grid data (handles event "catalog_product_prepare_save")
	 * @param Varien_Event_Observer $observer
	 */
	public function deserializeRepresentingProducts($observer) {
		/* @var $product Mage_Catalog_Model_Product */ $product = $observer->getEvent()->getProduct();
		/* @var $request Mage_Core_Controller_Request_Http */ $request = $observer->getEvent()->getRequest();
		
        $links = $request->getPost('links');
        $productData = $request->getPost('product');
	    if (isset($links['m_representing']) && !$product->getMRepresentingReadonly()) {
        	$product->setMRepresentingLinkData(Mage::helper('manapro_productfaces')->decodeGridSerializedInput($links['m_representing']));
        }
	}
	protected function _extractRepresentedFields($product) {
		return array(
			'm_parts' => $product->getMRepresentedParts(),
			'm_unit' => $product->getMRepresentedUnit(),
			'm_external_id' => $product->getMRepresentedExternalId(),
		);
	}
	static protected $_validating = false;
	protected function _validateRepresentingProduct(&$errors, $product, $linkedId, $fields) {
		/* @var $helper ManaPro_ProductFaces_Helper_Data */ $helper = Mage::helper(strtolower('ManaPro_ProductFaces'));
		if (self::$_validating) return;
		self::$_validating = true;
		try {
			$errorSuffix = '';
			if ($linkedId) {
				if (is_numeric($linkedId)) {
					$linkedProduct = Mage::getModel('catalog/product')->load($linkedId);
				}
				else {
					$linkedProduct = clone $product;
				}
				$errorSuffix = $helper->__(' See product %s (%s).', $linkedProduct->getSku(), $linkedProduct->getName());
				if (is_numeric($linkedId) && ($backProduct = $helper->getRepresentedProduct($linkedProduct)) && $product->getId() != $backProduct->getId()) {
					$errors[] = $helper->__('Product can represent only a single other product.').$errorSuffix;
				}
				try {
			        for ($i = 0; $i < 10; $i++) {
			        	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $product->getStoreId())) {
			        		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
			        		$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
			        		foreach ($attributes as $attribute) {
						        switch ($attribute->getFrontend()->getInputType()) {
						        	case 'price': 
						        	case 'text': 
						        	case 'select':
								        $linkedProduct->setData($attributeCode, $fields[$attributeCode]);
						        		break;
						        }
			        		}
			        	}
			        }
					$linkedProduct->validate();		
				}
				catch (Exception $e) {
					$errors[] = $helper->__('Error while saving representing product %s: %s', $linkedProduct->getSku(), $e->getMessage());
				}
			}
			if (isset($fields['m_parts']) && (!$fields['m_parts'] || !is_numeric($fields['m_parts']) || $fields['m_parts'] <= 0)) {
				$errors[] = $helper->__('Parts should be positive number, but %s is not.', $fields['m_parts']).$errorSuffix;
			}
			if (isset($fields['position']) && !is_numeric($fields['position'])) {
				$errors[] = $helper->__('Position should be a number, but %s is not.', $fields['position']).$errorSuffix;
			}
			if (isset($fields['m_unit']) && !in_array($fields['m_unit'], array_keys(Mage::getModel('manapro_productfaces/source_unit')->getOptionArray()))) {
				$errors[] = $helper->__('One of valid units of measure expected, but %s is not.', $fields['m_unit']).$errorSuffix;
			}
		}
		catch (Exception $e) {
			self::$_validating = false;
			throw $e;
		}
		self::$_validating = false;
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * REPLACE THIS WITH DESCRIPTION (handles event "catalog_product_save_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function validateRepresentingProducts($observer) {
		if (!($product = Mage::registry('current_product')) || $product->getId() != $observer->getEvent()->getProduct()->getId()) {
			// do not handle nonUI (or recursive) changes
			return;
		}
		
		/* @var $product Mage_Catalog_Model_Product */ $product = $observer->getEvent()->getProduct();
        /* @var $linkModel Mage_Catalog_Model_Product_Link */ $linkModel = $product->getLinkInstance();
		/* @var $collection ManaPro_ProductFaces_Resource_Collection */ $collection = Mage::getResourceModel('manapro_productfaces/collection');
		/* @var $helper ManaPro_ProductFaces_Helper_Data */ $helper = Mage::helper(strtolower('ManaPro_ProductFaces'));
		$errors = array();
		
		// validate backlink info
		if ($representedProductId = $product->getMRepresentedId()) {
			$representedProduct = $helper->getRepresentedProduct($product);
			if ($representedProduct && $representedProduct->getId() == $representedProductId) {
				$this->_validateRepresentingProduct($errors, null, null, $this->_extractRepresentedFields($product));
			}
		}
		
		// here is the hack for dumb validate action which does not provide a way to inject POST parsing code through 
		// dedicated event
		if (Mage::app()->getRequest() && Mage::app()->getRequest()->getControllerName() == 'catalog_product' && 
			Mage::app()->getRequest()->getActionName() == 'validate' && Mage::app()->getRequest()->getModuleName() == 'admin')
		{
	        $links = Mage::app()->getRequest()->getPost('links');
	        if (isset($links['m_representing'])) {
	            $product->setMRepresentingLinkData(Mage::helper('manapro_productfaces')->decodeGridSerializedInput($links['m_representing']));
	        }
		}
		
		// validate link collection
		if ($product->getMRepresentingEnabled()) {
	        $data = $product->getMRepresentingLinkData();
	        if (!is_null($data)) {
	        	foreach ($data as $id => $fields) {
					$this->_validateRepresentingProduct($errors, $product, $id, $fields);
	        	}
	        }
		}
        
        if (count($errors) > 0) {
        	throw new Mage_Core_Exception(implode('<br />', $errors));
        }
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Similar to other links, updates representing products in database (handles event "catalog_product_save_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function saveRepresentingProducts($observer) {
		if (!($product = Mage::registry('current_product')) || $product->getId() != $observer->getEvent()->getProduct()->getId()) {
			// do not handle nonUI (or recursive) changes
			return;
		}
		static $reentranceFlag = false;
		if ($reentranceFlag) return; else $reentranceFlag = true;
		
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		/* @var $product Mage_Catalog_Model_Product */ $product = $observer->getEvent()->getProduct();
        /* @var $linkModel Mage_Catalog_Model_Product_Link */ $linkModel = $product->getLinkInstance();
		/* @var $collection ManaPro_ProductFaces_Resource_Collection */ $collection = Mage::getResourceModel('manapro_productfaces/collection');
		/* @var $inventory ManaPro_ProductFaces_Resource_Inventory */ $inventory = Mage::getResourceModel('manapro_productfaces/inventory');
		
		// update backlink
		if ($representedProductId = $product->getMRepresentedId()) {
			Mage::getResourceModel('manapro_productfaces/link')->updateProductLink($representedProductId, $product->getId(), 
				$this->_extractRepresentedFields($product), $collection->getRepresentingLinkTypeId());
		}
		elseif ($product->hasMRepresentingEnabled()) {
			$inventory->clearRepresentingProducts($product->getId());
			$data = $product->getMRepresentingLinkData();
			if ($product->getMRepresentingEnabled()) {
		        if (!is_null($data)) {
		        	$processed = array();
		        	$existingIds = $product->getData('m_productfaces_cloning_override_ids');
		        	$existingIds = $existingIds ? json_decode($existingIds, true) : array();
		        	if (!in_array($product->getData('m_productfaces_clone_override'), array(1, 3)) ||
		        		!$product->getData('m_productfaces_cloning_override_decision')) 
	        		{
	        			$existingIds = array();
	        		}
	        		
		        	foreach ($data as $id => $fields) {
		        		if ($id == 'this') {
		        			// update this product from quick edit
		        			$quickEdited = false;
							foreach (Mage::helper('manapro_productfaces')->getAttributesForQuickEdit($product->getStoreId()) as $attributeCode) {
				        		if ($product->getData($attributeCode) != $fields[$attributeCode]) {
						        	$product->setData($attributeCode, $fields[$attributeCode]);
						        	$quickEdited = true;
				        		}
					        }
		        				        			
					        if ($quickEdited) {
								$product->save();
					        }
					        
					        $fields['entity_id'] = $product->getId();
		        			$processed[$product->getId()] = $fields; 
		        		}
		        		elseif ($core->startsWith($id, 'copy-')) {
		        			// save new product
							/* @var $newProduct Mage_Catalog_Model_Product */ $newProduct = $product->duplicate();
		        			$newProduct = Mage::getModel('catalog/product')->setStoreId(0)->load($newProduct->getId());
					        $newProduct
					        	->setImage($product->getImage())
					        	->setSmallImage($product->getSmallImage())
					        	->setThumbnail($product->getThumbnail())
                                ->setImageLabel($product->getImageLabel())
                                ->setSmallImageLabel($product->getSmallImageLabel())
                                ->setThumbnailLabel($product->getThumbnailLabel());
					        
					        $i = 0;
					        $newSku = '';
					        $suffix = Mage::getStoreConfig('manapro_productfaces/cloning/sku_suffix');
					        /* @var $productModel Mage_Catalog_Product_Model */ $productModel = Mage::getModel('catalog/product');
					        while (!$newSku) {
					        	$sku = $product->getSku().$suffix.(++$i);
					        	if (!$productModel->getIdBySku($sku)) {
					        		$newSku = $sku;
					        	}
					        }
					        $newProduct->setSku($newSku);
					        $newProduct->setStatus(Mage::getStoreConfig('manapro_productfaces/cloning/status'));
					
					        for ($i = 0; $i < 10; $i++) {
					        	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/not_cloned/attribute'.$i)) {
					        		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
					        		$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
					        		foreach ($attributes as $attribute) {
								        $newProduct->setData($attributeCode, $attribute->getDefaultValue());
					        		}
					        	}
					        }
							foreach (Mage::helper('manapro_productfaces')->getAttributesForQuickEdit($newProduct->getStoreId()) as $attributeCode) {
				        		if ($newProduct->getData($attributeCode) != $fields[$attributeCode]) {
						        	$newProduct->setData($attributeCode, $fields[$attributeCode]);
				        		}
					        }
					        $newProduct->save();
					        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
					        $db = Mage::getSingleton('core/resource')->getConnection('core_write');
					        $res = Mage::getSingleton('core/resource');

                            // to this point all media_gallery entries are copied as needed. We need to assign correct file names to
                            // main images ('image', 'small_image', 'thumbnail') which are displayed on product page, product list
                            // and in sidebars. So go through each of these 3 attributes
							foreach ($db->fetchPairs("SELECT attribute_code, attribute_id FROM {$res->getTableName('eav_attribute')}
                                WHERE entity_type_id = {$product->getEntityTypeId()}
									AND attribute_code IN ('image', 'small_image', 'thumbnail')") as $attributeCode => $attributeId)
							{
							    $oldValue = $db->fetchOne("SELECT value FROM {$res->getTableName('catalog_product_entity_varchar')}
							        WHERE (entity_id = {$product->getId()})
							            AND (attribute_id = {$attributeId})
							            AND store_id = 0");
                                $value = null;

                                if (($pos = strrpos($oldValue, '.')) === false) {
                                    continue;
                                }
                                $name = substr($oldValue, 0, $pos);
                                $ext = substr($oldValue, $pos);

                                $pattern = "/".$this->_escapeRegExpr($name)."_\\d+". $this->_escapeRegExpr($ext)."/";
                                foreach ($db->fetchCol("SELECT mg.value FROM {$res->getTableName('catalog_product_entity_media_gallery')} mg,
                                    {$res->getTableName('eav_attribute')} ea
                                    WHERE mg.attribute_id = ea.attribute_id
                                    and mg.entity_id ={$newProduct->getId()} and ea.attribute_code = 'media_gallery'") as $newValue)
                                {
                                    if (preg_match($pattern, $newValue)) {
                                        $value = $newValue;
                                        break;
                                    }
                                }

                                if ($value) {
                                    $db->query("INSERT INTO {$res->getTableName('catalog_product_entity_varchar')}
										(entity_type_id, attribute_id, store_id, entity_id, value) VALUES
										({$newProduct->getEntityTypeId()}, $attributeId, 0, {$newProduct->getId()}, '$value')
										ON DUPLICATE KEY UPDATE value = '$value'");


                                    if (Mage::getStoreConfigFlag('catalog/frontend/flat_catalog_product') && $attributeCode != 'image') {
                                        foreach (Mage::app()->getStores(false) as $store) {
                                            $db->query("UPDATE {$res->getTableName('catalog_product_flat_'.$store->getId())} SET $attributeCode = '$value'
												WHERE entity_id = {$newProduct->getId()}");
                                        }
                                    }
                                }
							}
		        			$fields['entity_id'] = $newProduct->getId();
		        			$processed[$newProduct->getId()] = $fields; 
		        		}
		        		else {
		        			// update existing products (quick edit info)
		        			$quickEdited = false;
		        			$representingProduct = Mage::getModel('catalog/product')
								->setStore($product->getStoreId())
								->load($id);
							
							if (isset($existingIds[$id])) {
								$this->_overrideProductAttributes($representingProduct, $product);
							}
							
							foreach (Mage::helper('manapro_productfaces')->getAttributesForQuickEdit($product->getStoreId()) as $attributeCode) {
				        		if ($representingProduct->getData($attributeCode) != $fields[$attributeCode]) {
						        	$representingProduct->setData($attributeCode, $fields[$attributeCode]);
						        	$quickEdited = true;
				        		}
					        }
					        
					        if ($quickEdited) {
								$representingProduct->save();
					        }
					        $processed[$id] = $fields;
		        		}	
		        	}
	
					$db = Mage::getSingleton('core/resource')->getConnection('core_write');
					$res = Mage::getSingleton('core/resource');
		        	$attributeId = $db->fetchOne("SELECT attribute_id FROM {$res->getTableName('eav_attribute')} 
						WHERE entity_type_id = {$product->getEntityTypeId()} 
						AND attribute_code = 'm_productfaces_clone_override'");
	        		$value = $product->getData('m_productfaces_clone_override');
					$db->query("INSERT INTO {$res->getTableName('catalog_product_entity_int')}
						(entity_type_id, attribute_id, store_id, entity_id, value) VALUES 
						({$product->getEntityTypeId()}, $attributeId, 0, {$product->getId()}, $value)
						ON DUPLICATE KEY UPDATE value = $value");

					// update links (parts, unit, position)
		        	$linkModel->getResource()->saveProductLinks($product, $processed, $collection->getRepresentingLinkTypeId());
		        }
			}
			else {
		    	$linkModel->getResource()->saveProductLinks($product, array(), $collection->getRepresentingLinkTypeId());
			}
		}

		if ($representedProduct = Mage::helper('manapro_productfaces')->getRepresentedProduct($product)) {
            $inventory->updateRepresentingProducts($product->getId());
        }
		else {
            $inventory->updateStockProductMReprepresendedQty($product->getId());
        }
	}
	public function beforeDeleteRepresentingProducts($observer) {
		/* @var $product Mage_Catalog_Model_Product */ $product = $observer->getEvent()->getProduct();
		/* @var $inventory ManaPro_ProductFaces_Resource_Inventory */ $inventory = Mage::getResourceModel('manapro_productfaces/inventory');
		
		// update backlink
		if ($representedProductId = Mage::getResourceModel('manapro_productfaces/link')->getRepresentedProductId($product->getId())) {
			// update inventory
			// TD: handle deletion of multiple products
			Mage::register('updateRepresentedProductAfterDelete', $representedProductId);
		}
	}
	public function deleteRepresentingProducts($observer) {
		/* @var $product Mage_Catalog_Model_Product */ $product = $observer->getEvent()->getProduct();
		/* @var $inventory ManaPro_ProductFaces_Resource_Inventory */ $inventory = Mage::getResourceModel('manapro_productfaces/inventory');
		
		// update backlink
		if ($representedProductId = Mage::registry('updateRepresentedProductAfterDelete')) {
			// update inventory
			$inventory->updateRepresentingProducts($representedProductId, true,
				array('potentiallyObsoleteIds' => array($product->getId())));
		}
	}
	/* BASED ON SNIPPET: Models/Event handler */
	/**
	 * Adds columns to product flat tables (handles event "catalog_product_flat_prepare_columns")
	 * @param Varien_Event_Observer $observer
	 */
	public function addFlatColumns($observer) {
		/* @var $columnsObject Varien_Object */ $columnsObject = $observer->getEvent()->getColumns();
		
		$columns = $columnsObject->getColumns();
		$columns['m_represented_qty'] = array(
            'type'      => 'decimal(12,4)',
			'unsigned'  => false,
            'is_null'   => false,
            'default'   => '0.0000',
            'extra'     => null
        );
		$columns['m_represents'] = array(
            'type'      => 'tinyint(1)',
			'unsigned'  => false,
			'is_null'   => false,
            'default'   => 0,
            'extra'     => null
        );
        $columnsObject->setColumns($columns);
	}
	/**
	 * Catches moment after database upgrade to rerun data replication actions (handles event "controller_action_predispatch")
	 * @param Varien_Event_Observer $observer
	 */
	public function afterUpgrade($observer) {
		if (Mage::registry('m_product_faces_reindex_flat')) {
			Mage::unregister('m_product_faces_reindex_flat');
			foreach (array('catalog_product_flat', 'manapro_productfaces_update_all') as $process) {
				Mage::getModel('index/process')->load($process, 'indexer_code')
					->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX)->reindexAll();
			}
		}
	}
	/**
	 * Enter description here ...
	 * @param Mage_Catalog_Model_Product $target
	 * @param Mage_Catalog_Model_Product $source
	 * @return Ambiguous
	 */
	protected function _overrideProductAttributes($target, $source) {
		$preservedAttributes = array('entity_id', 'sku', 'status', 'created_at', 'updated_at');
	    for ($i = 0; $i < 10; $i++) {
        	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/not_overridden/attribute'.$i)) {
        		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        		$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
        		foreach ($attributes as $attribute) {
			        $preservedAttributes[] = $attributeCode;
        		}
        	}
        }
		
        $source->getWebsiteIds();
        $source->getCategoryIds();

        $target->setStoreId(Mage::app()->getStore()->getId());
        foreach ($source->getAttributes() as $attribute) {
        	if (!in_array($attribute->getAttributeCode(), $preservedAttributes)) {
        		$target->setData($attribute->getAttributeCode(), $source->getData($attribute->getAttributeCode()));
        	}
        }
        //Mage::dispatchEvent('catalog_model_product_duplicate', array('current_product'=>$source, 'new_product'=>$target));

        /* Prepare Related*/
        $data = array();
        $source->getLinkInstance()->useRelatedLinks();
        $attributes = array();
        foreach ($source->getLinkInstance()->getAttributes() as $_attribute) {
            if (isset($_attribute['code'])) {
                $attributes[]=$_attribute['code'];
            }
        }
        foreach ($source->getRelatedLinkCollection() as $_link) {
            $data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
        }
        $target->setRelatedLinkData($data);

        /* Prepare UpSell*/
        $data = array();
        $source->getLinkInstance()->useUpSellLinks();
        $attributes = array();
        foreach ($source->getLinkInstance()->getAttributes() as $_attribute) {
            if (isset($_attribute['code'])) {
                $attributes[]=$_attribute['code'];
            }
        }
        foreach ($source->getUpSellLinkCollection() as $_link) {
            $data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
        }
        $target->setUpSellLinkData($data);

        /* Prepare Cross Sell */
        $data = array();
        $source->getLinkInstance()->useCrossSellLinks();
        $attributes = array();
        foreach ($source->getLinkInstance()->getAttributes() as $_attribute) {
            if (isset($_attribute['code'])) {
                $attributes[]=$_attribute['code'];
            }
        }
        foreach ($source->getCrossSellLinkCollection() as $_link) {
            $data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
        }
        $target->setCrossSellLinkData($data);

        /* Prepare Grouped */
        $data = array();
        $source->getLinkInstance()->useGroupedLinks();
        $attributes = array();
        foreach ($source->getLinkInstance()->getAttributes() as $_attribute) {
            if (isset($_attribute['code'])) {
                $attributes[]=$_attribute['code'];
            }
        }
        foreach ($source->getGroupedLinkCollection() as $_link) {
            $data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
        }
        $target->setGroupedLinkData($data);

        $target->save();

        $source->getOptionInstance()->duplicate($source->getId(), $target->getId());
        $source->getResource()->duplicate($source->getId(), $target->getId());
        /*
        $target
        	->setImage($source->getImage())
        	->setSmallImage($source->getSmallImage())
        	->setThumbnail($source->getThumbnail())
            ->setImageLabel($source->getImageLabel())
            ->setSmallImageLabel($source->getSmallImageLabel())
            ->setThumbnailLabel($source->getThumbnailLabel());
        */
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $res = Mage::getSingleton('core/resource');
		foreach ($db->fetchPairs("SELECT attribute_code, attribute_id FROM {$res->getTableName('eav_attribute')} 
			WHERE entity_type_id = {$source->getEntityTypeId()} 
			AND attribute_code IN ('image', 'small_image', 'thumbnail')") as $attributeCode => $attributeId) 
		{
			$value = $db->fetchOne("SELECT value FROM {$res->getTableName('catalog_product_entity_varchar')} 
				WHERE store_id = 0 AND entity_id = {$source->getId()} AND attribute_id = $attributeId");
			$db->query("INSERT INTO {$res->getTableName('catalog_product_entity_varchar')}
				(entity_type_id, attribute_id, store_id, entity_id, value) VALUES 
				({$target->getEntityTypeId()}, $attributeId, 0, {$target->getId()}, '$value')
				ON DUPLICATE KEY UPDATE value = '$value'");
			if (Mage::getStoreConfigFlag('catalog/frontend/flat_catalog_product') && $attributeCode != 'image') {
				foreach (Mage::app()->getStores(false) as $store) {
					$db->query("UPDATE {$res->getTableName('catalog_product_flat_'.$store->getId())} SET $attributeCode = '$value' 
						WHERE entity_id = {$target->getId()}");
				}
			}
		}



        return $target;
	}

    static protected $_regExprCharacters = '[\^$.|?*+()/';
    protected function _escapeRegExpr($str) {
        $result = '';
        for ($i = 0, $len = strlen($str); $i < $len; $i++) {
            $ch = substr($str, $i, 1);
            if (strpos(self::$_regExprCharacters, $ch) !== false) {
                $result .= '\\' . $ch;
            }
            else {
                $result .= $ch;
            }
        }
        return $result;
    }

}