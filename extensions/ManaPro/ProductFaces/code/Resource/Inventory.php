<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Core calculation logic of the module
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Resource_Inventory extends Mage_CatalogInventory_Model_Mysql4_Stock {
	public function getProductStocks($productId) {
		return $this->_getReadAdapter()->fetchAssoc("
			SELECT `stock_id`, `is_qty_decimal`, `qty` FROM {$this->getTable('cataloginventory/stock_item')}
			WHERE `product_id` = $productId
		");
	}
	public function getProductStock($productId) {
		foreach ($this->getProductStocks($productId) as $stockData) {
			return $stockData;
		}
		return array('stock_id' => 0, 'is_qty_decimal' => 0, 'qty' => 0);
		//throw new Exception('Not implemented');
	}
	/**
	 * This method performs core calculation of the whole module. 
	 * @param array $productData Info about represented product. Columns: qty, status, is_qty_decimal
	 * @param array $representingProductData Array of infos about representing products. Columns: linked_product_id, m_parts, m_unit
	 * @return array Computation result. Columns: qties - id => calculated quantity map, messages - text messages to administrator, represented_qty - total represented qty, self_represented_qty - qty represented by base product 
	 */
	
	protected static $_representingProductDataForSortingCallback;
	protected static $_methodPriorities = array('qty' => 1, 'percent' => 2, 'parts' => 3);
	protected static function _getRepresentingProductDataForSortingCallback($entityId, $column) {
		foreach (self::$_representingProductDataForSortingCallback as $index => $data) {
			$dataId = isset($data['entity_id']) ? $data['entity_id'] : $data['linked_product_id'];
			if ($dataId == $entityId) {
				return $column == '_index' ? $index : $data[$column];
			}
		} 
	}
	public static function _representingProductSortingCallback($a, $b) {
		$aIndex = self::_getRepresentingProductDataForSortingCallback($a, '_index');
		$bIndex = self::_getRepresentingProductDataForSortingCallback($b, '_index');
		// sort by method priority
		$aPriority = self::$_methodPriorities[self::_getRepresentingProductDataForSortingCallback($a, 'm_unit')];
		$bPriority = self::$_methodPriorities[self::_getRepresentingProductDataForSortingCallback($b, 'm_unit')];
		if ($aPriority < $bPriority) {
			return -1;
		}
		elseif ($aPriority > $bPriority) {
			return 1;
		}
		
		// if method priority equal, sort by position. All records with not assigned positions go down
		$aPriority = self::_getRepresentingProductDataForSortingCallback($a, 'position');
		$bPriority = self::_getRepresentingProductDataForSortingCallback($b, 'position');
		if ($aPriority) {
			if ($bPriority) {
				if ($aPriority < $bPriority) {
					return -1;
				}
				elseif ($aPriority > $bPriority) {
					return 1;
				}
			}
			else {
				return -1;
			}
		}
		else {
			if ($bPriority) {
				return 1;
			}
		}
		
		return 0;
	}
	public function calculateQuantities($productData, $representingProductData = null, $storeId = null) {
		// fill default parameter values if not provided explicitly
		if ($productData instanceof Mage_Catalog_Model_Product && ($stockItem = $productData->getStockItem())) {
			$productData = array(
				'entity_id' => $productData->getId(),
				'qty' => $stockItem->getQty(),
				'is_qty_decimal' => $stockItem->getIsQtyDecimal(),
				//'status' => $productData->getStatus(),
			);
		}
		elseif (is_int($productData) || is_string($productData)) {
			$stockItem = $this->getProductStock($productData);
			$statuses = Mage::getResourceModel('catalog/product_status')->getProductStatus($productData, $storeId);
			$productData = array(
				'entity_id' => $productData,
				'qty' => $stockItem['qty'],
				'is_qty_decimal' => $stockItem['is_qty_decimal'],
				//'status' => $statuses[$productData],
			);
		}
		if (is_null($representingProductData)) {
			$link = Mage::getResourceModel('manapro_productfaces/link');
			$representingProductData = $link->getRepresentingProductsAndOptions($productData['entity_id']);
		}
		
		// prepare initial results - all representing products get zeroes and no messages for user
		$result = array(
			'qties' => array(),
			'messages' => array(),
		);	
		if (!count($representingProductData)) {
			return $result;
		}
		$ids = array();
		$virtualIds = array();
		$totalParts = 0;
		$idIndex = null;
		foreach ($representingProductData as $key => $link) {
			if (!$idIndex) {
				$idIndex = isset($link['entity_id']) ? 'entity_id' : 'linked_product_id';
			}
			if ($link['m_unit'] == 'parts') {
				$totalParts += $link['m_parts'];
			}

			if(strpos($link['m_unit'], 'virtual_') === 0) {
                $virtualIds[$key] = $link[$idIndex];
			}
			else {
                $ids[$key] = $link[$idIndex];
			}

		}
		$thisIndex = in_array('this', $ids) ? 'this' : $productData['entity_id'];

        // Reinsert parent product to last, so it goes first (highest priority) when sorted with same `m_unit` and `position`
        // Products with same `m_unit` and `position` gets inverted.
        // $representingProductsData [1, 2, 3] becomes [3, 2, 1] after uasort `_representingProductSortingCallback` if products 1, 2, and 3 have the same `m_unit` and `position`
        // That's why 'this'(original) product is reinserted to last, so after uasort `_representingProductSortingCallback`, it will be first in the array.
        if(($key = array_search($thisIndex, $ids)) !== false) {
            end($ids);
            $i = key($ids);

            if($representingProductData[$i][$idIndex] != $thisIndex) {
                unset($ids[$key]);
                array_push($ids, $thisIndex);
                end($ids);
                $i = key($ids);

                if(isset($representingProductData[$i])) {
                    $tmp = $representingProductData[$key];
                    $representingProductData[$key] = $representingProductData[$i];
                    $representingProductData[$i] = $tmp;
                } else {
                    $representingProductData[$i] = $representingProductData[$key];
                    unset($representingProductData[$key]);
                }

                if (isset($virtualIds[$i])) {
                    $tmp = $virtualIds[$i];
                    unset($virtualIds[$i]);
                    $virtualIds[$key] = $tmp;
                }
            }
        }


        // order results by method (qty, then percent, then part-of) and by position
        self::$_representingProductDataForSortingCallback = $representingProductData;
        uasort($ids, array('ManaPro_ProductFaces_Resource_Inventory', '_representingProductSortingCallback'));
        foreach ($ids as $key => $id) {
			$result['qties'][$id] = 0;
            if (!isset($representingProductData[$key]['m_pack_qty']) || $representingProductData[$key]['m_pack_qty'] <= 0) {
                $representingProductData[$key]['m_pack_qty'] = 1;
            }
		}
		foreach ($virtualIds as $key => $id) {
		    if ($representingProductData[$key]['m_unit'] == 'virtual_percent') {
                $qty = ($productData['qty'] * $representingProductData[$key]['m_parts'] / 100) / $representingProductData[$key]['m_pack_qty'];
                $result['qties'][$id] = ($representingProductData[$key]['m_pack_qty'] == 1) ? round($qty) : floor($qty);
                if (empty($productData['is_qty_decimal'])) {
                    $result['qties'][$id] = round($result['qties'][$id]);
                }
            }
		    else {
		        throw new Exception('Not implemented');
		    }
		}
		// first assign qties to products with qty method
		$productsProcessed = 0;
		$qtyLeft = $productData['qty'];
		foreach ($ids as $key => $id) {
			if ($representingProductData[$key]['m_unit'] == 'qty') {
				$productsProcessed++;
				
				$qty = $representingProductData[$key]['m_parts'] / $representingProductData[$key]['m_pack_qty'];
				if (empty($productData['is_qty_decimal'])) {
					$qty = ($representingProductData[$key]['m_pack_qty'] == 1) ? round($qty): floor($qty);
				}
				
				if ($qty <= $qtyLeft) {
					$result['qties'][$id] = $qty;
                    $qtyLeft -= $qty * $representingProductData[$key]['m_pack_qty'];
				}
				else {
					$result['qties'][$id] = $qtyLeft;
					$qtyLeft = 0;
					break;
				}
			}
		}
		
		if ($qtyLeft > 0) {
			// if there is some qty left
			if ($productsProcessed >= count($ids)) {
                $keys = array_keys($ids, $thisIndex);
                $key = reset($keys);
                if($representingProductData[$key]['m_pack_qty'] == 1) {
                    // in case both percent and part methods are NOT present among representing products, we should
                    // distribute qty left among qty method products. We assign this qty to "parent" product
                    $result['qties'][$thisIndex] += $qtyLeft;
                    $qtyLeft = 0;
                } else {
                    $qty = floor($qtyLeft / $representingProductData[$key]['m_pack_qty']);
                    $qtyLeft -= $qty * $representingProductData[$key]['m_pack_qty'];
                    $result['qties'][$thisIndex] += $qty;
                }
			}
			else {
				// assign qty left to products with percent method
				foreach ($ids as $key => $id) {
					if ($representingProductData[$key]['m_unit'] == 'percent') {
						$productsProcessed++;
						
						$qty = ($productData['qty'] / $representingProductData[$key]['m_pack_qty']) * $representingProductData[$key]['m_parts'] / 100;
						if (empty($productData['is_qty_decimal'])) {
                            $qty = ($representingProductData[$key]['m_pack_qty'] == 1) ? round($qty) : floor($qty);
						}
						
						if ($qty <= $qtyLeft) {
							$result['qties'][$id] = $qty;
							$qtyLeft -= $qty * $representingProductData[$key]['m_pack_qty'];
						}
						else {
							$result['qties'][$id] = $qtyLeft;
							$qtyLeft = 0;
							break;
						}
					}
				}
				
				if ($qtyLeft > 0) {
					// if there is still some qty left
					if ($productsProcessed >= count($ids)) {
                        $keys = array_keys($ids, $thisIndex);
                        $key = reset($keys);
                        if($representingProductData[$key]['m_pack_qty'] == 1) {
                            // in case both percent and part methods are NOT present among representing products, we should
                            // distribute qty left among qty method products. We assign this qty to "parent" product
                            $result['qties'][$thisIndex] += $qtyLeft;
                            $qtyLeft = 0;
                        } else {
                            $qty = floor($qtyLeft / $representingProductData[$key]['m_pack_qty']);
                            $qtyLeft -= $qty * $representingProductData[$key]['m_pack_qty'];
                            $result['qties'][$thisIndex] += $qty;
                        }
					}
					else {
						// assign qty left to products with part method
						$qtyTotal = $qtyLeft;
						foreach ($ids as $key => $id) {
							if ($representingProductData[$key]['m_unit'] == 'parts') {
								$productsProcessed++;

								$qty = ($totalParts > 0) ? ($qtyTotal * $representingProductData[$key]['m_parts'] / $totalParts) / $representingProductData[$key]['m_pack_qty']: 0;
								if (empty($productData['is_qty_decimal'])) {
                                    $qty = ($representingProductData[$key]['m_pack_qty'] == 1) ? round($qty) : floor($qty);
								}

								$result['qties'][$id] = $qty;
								$qtyLeft -= $qty * $representingProductData[$key]['m_pack_qty'];
							}
						}

						if (empty($productData['is_qty_decimal'])) {
                            while($qtyLeft > 0) {
                            	$processed = false;
                                // in case we have positive rounding error, do +1 starting from most prioritized
                                foreach ($ids as $key => $id) {
                                    if ($representingProductData[$key]['m_unit'] == 'parts') {
                                        if($representingProductData[$key]['m_pack_qty'] <= $qtyLeft && ($qtyLeft > 0)) {
                                            $result['qties'][$id]++;
                                            $qtyLeft -= $representingProductData[$key]['m_pack_qty'];
                                            $processed = true;
                                        }
                                        if ($qtyLeft <= 0) {
                                            break;
                                        }
                                    }
                                }
                                if(!$processed) break;
                            }
                            while($qtyLeft < 0) {
								$processed = false;
                                // in case we have negative rounding error, do -1 starting from least prioritized
                                foreach (array_reverse($ids, true) as $key => $id) {
                                    if ($representingProductData[$key]['m_unit'] == 'parts') {
                                        if ($representingProductData[$key]['m_pack_qty'] > $qtyLeft && ($qtyLeft < 0)) {
                                            $result['qties'][$id]--;
                                            $qtyLeft += $representingProductData[$key]['m_pack_qty'];
											$processed = true;
										}
                                        if ($qtyLeft >= 0) {
                                            break;
                                        }
                                    }
                                }
								if (!$processed) {
									break;
								}
							}
						}
					}
				}
            }
        }
        if ($qtyLeft > 0) {
            if ($productsProcessed >= count($ids)) {
                $keys = array_keys($ids, $thisIndex);
                $key = reset($keys);
                if ($representingProductData[$key]['m_pack_qty'] == 1) {
                    // in case both percent and part methods are NOT present among representing products, we should
                    // distribute qty left among qty method products. We assign this qty to "parent" product
                    $result['qties'][$thisIndex] += $qtyLeft;
                    $qtyLeft = 0;
                } else {
                    $qty = floor($qtyLeft / $representingProductData[$key]['m_pack_qty']);
                    $qtyLeft -= $qty * $representingProductData[$key]['m_pack_qty'];
                    $result['qties'][$thisIndex] += $qty;
                }
            }
        }
        if ($qtyLeft > 0) {
            $result['messages'][] = array(
                'type' => 'notice',
                'text' => $this->coreHelper()->__("There are still %s remaining items that are unassigned.", $qtyLeft),
            );
        }

        return $result;
	}

    protected function _getRepresentedProductData($representedProductId, $representedProductsData) {
        foreach ($representedProductsData as $productData) {
            if ($productData['linked_product_id'] == $representedProductId) {
                return $productData;
            }
        }
    }

	public function updateRepresentingProducts($productId, $requireTransaction = true, $options = array()) {
		$options = array_merge(array(
			'potentiallyObsoleteIds' => array(),
            'runRelatedIndexes' => true,
		), $options);
		$link = Mage::getResourceModel('manapro_productfaces/link');
		$productIds = array($productId => $productId);
		
		// if product is representing, find represented product
		if ($representedProductId = $link->getRepresentedProductId($productId)) {
			$productId = $representedProductId;
			$productIds[$productId] = $productId;
		}
		
		// if product is represented (or represented product was previously found), retrieve representing products
        if ($requireTransaction) $this->_getWriteAdapter()->beginTransaction();
        try {
        	$sql = '';
        	$entitySql = '';
        	$calculation = $this->calculateQuantities($productId);
            $link = Mage::getResourceModel('manapro_productfaces/link');
            $representedProductsData = $link->getRepresentingProductsAndOptions($representedProductId);

        	$stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
			foreach ($calculation['qties'] as $linkedProductId => $qty) {
                $representingProductData = $this->_getRepresentedProductData($linkedProductId, $representedProductsData);
				$qtyUpdate = $linkedProductId != $representedProductId ? '`qty` = 0, ' : '';
				$sql .= "UPDATE {$this->getTable('cataloginventory/stock_item')}
					SET $qtyUpdate`m_represented_qty` = $qty, {$this->isInStockUpdate("$qty > `min_qty`")}, `m_represents` = 1,
					  `m_pack_qty` = ". ($representingProductData['m_pack_qty'] ? $representingProductData['m_pack_qty'] : 1) ."
					WHERE (`product_id` = {$linkedProductId}) AND (`stock_id` = $stockId);
					";
				$entitySql .= "UPDATE {$this->getTable('catalog/product')} 
					SET `m_represented_qty` = $qty, `m_represents` = 1
					WHERE (`entity_id` = {$linkedProductId});
					";
				if (($obsoleteIndex = array_search($linkedProductId, $options['potentiallyObsoleteIds'])) != false) {
					unset($options['potentiallyObsoleteIds'][$obsoleteIndex]);
				}
				$productIds[$linkedProductId] = $linkedProductId;
			}
			foreach ($options['potentiallyObsoleteIds'] as $linkedProductId) {
                $sql .= "UPDATE {$this->getTable('cataloginventory/stock_item')}
					SET `m_represented_qty` = 0, {$this->isInStockUpdate("0 > `min_qty`")}, `m_represents` = 0,
					  `m_pack_qty` = 1
					WHERE (`product_id` = {$linkedProductId}) AND (`stock_id` = $stockId);
					";
				$entitySql .= "UPDATE {$this->getTable('catalog/product')} 
					SET `m_represented_qty` = 0, `m_represents` = 0
					WHERE (`entity_id` = {$linkedProductId});
					";
				$productIds[$linkedProductId] = $linkedProductId;
			}
			$this->_getWriteAdapter()->multi_query($sql);
            if ($options['runRelatedIndexes']) {
            	Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts($productIds);
            	//Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);
            }
	        $this->_getWriteAdapter()->multi_query($entitySql);
	        $this->updateTextQties($productIds);
        	if ($requireTransaction) $this->_getWriteAdapter()->commit();
        }
        catch (Exception $e) {
        	if ($requireTransaction) $this->_getWriteAdapter()->rollback();
        	throw $e;
        }
		
		return $this;
	}
	public function clearRepresentingProducts($productId, $requireTransaction = true) {
		$link = Mage::getResourceModel('manapro_productfaces/link');
		$productIds = array($productId => $productId);
		
		// if product is representing, find represented product
		if ($representedProductId = $link->getRepresentedProductId($productId)) {
			$productId = $representedProductId;
			$productIds[$productId] = $productId;
		}
		
		// if product is represented (or represented product was previously found), retrieve representing products
        if ($requireTransaction) $this->_getWriteAdapter()->beginTransaction();
        try {
        	$sql = '';
        	$entitySql = '';
        	$stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
			$link = Mage::getResourceModel('manapro_productfaces/link');
			$representingProductData = $link->getRepresentingProductsAndOptions($productId);
			foreach ($representingProductData as $key => $link) {
				$idIndex = isset($link['entity_id']) ? 'entity_id' : 'linked_product_id';
				$sql .= "UPDATE {$this->getTable('cataloginventory/stock_item')} 
					SET `m_represented_qty` = 0, {$this->isInStockUpdate("`qty` > `min_qty`")}, `m_represents` = 0
					WHERE (`product_id` = {$link[$idIndex]}) AND (`stock_id` = $stockId);
					";
				$entitySql .= "UPDATE {$this->getTable('catalog/product')} 
					SET `m_represented_qty` = 0, `m_represents` = 0
					WHERE (`entity_id` = {$link[$idIndex]});
					";
				$productIds[$link[$idIndex]] = $link[$idIndex];
			}
			if ($sql) {
				$this->_getWriteAdapter()->multi_query($sql);
	        	Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts($productIds);
	        	Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);
	        	if ($entitySql) {
	        		$this->_getWriteAdapter()->multi_query($entitySql);
                    $this->updateTextQties($productIds);
                }
			}
			if ($requireTransaction) $this->_getWriteAdapter()->commit();
        }
        catch (Exception $e) {
        	if ($requireTransaction) $this->_getWriteAdapter()->rollback();
        	throw $e;
        }
		
		return $this;
	}
	public function updateRepresentedProduct($productId, $requireTransaction = true) {
		$link = Mage::getResourceModel('manapro_productfaces/link');
		
		// if product is representing, find represented product
		if (!( $representedProductId = $link->getRepresentedProductId($productId))) {
			$this->updateRepresentingProducts($productId);
			return $this;
		}
		$productIds = array($representedProductId => $representedProductId);
		
		if ($requireTransaction) $this->_getWriteAdapter()->beginTransaction();
        try {
        	$sql = '';
			$links = $link->getRepresentingProductsAndOptions($representedProductId);
        	$productStocks = $this->getProductStocks($productId);
        	$representedProductStocks = $this->getProductStocks($representedProductId);
			foreach ($links as $link) {
				if ($link['m_unit'] == 'parts') {
					$totalParts += $link['m_parts'];
				}
				if ($link['linked_product_id'] == $productId) {
					$changedLink = $link;
				}
			}
        	foreach ($productStocks as $stockId => $stock) {
				if ($changedLink['m_unit'] == 'parts') {
					$expectedQty = $totalParts ? $representedProductStocks[$stockId]['qty'] * $changedLink['m_parts'] / $totalParts : 0;
				}
				elseif ($changedLink['m_unit'] == 'percent') {
					$expectedQty = $representedProductStocks[$stockId]['qty'] * $changedLink['m_parts'] / 100;
				}
				
				if (!empty($stock['is_qty_decimal'])) {
					$diff = $stock['qty'] - $expectedQty;
				}
				else {
					$diff = round($stock['qty']) - round($expectedQty);
				}
        		$sql .= "UPDATE {$this->getTable('cataloginventory/stock_item')} 
					SET `qty` = `qty` + $diff, {$this->isInStockUpdate("`qty` + $diff >= `min_qty`")}
					WHERE (`product_id` = {$representedProductId}) AND (`stock_id` = $stockId);
					";
			}
			
			$this->_getWriteAdapter()->multi_query($sql);
        	Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts($productIds);
        	Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);
			$this->updateRepresentingProducts($representedProductId, false);
			if ($requireTransaction) $this->_getWriteAdapter()->commit();
        }
        catch (Exception $e) {
        	if ($requireTransaction) $this->_getWriteAdapter()->rollback();
        	throw $e;
        }
		
		return $this;
	}
	public function updateRepresentedProducts($items, $multiplier, $requireTransaction = true) {
		$link = Mage::getResourceModel('manapro_productfaces/link');
		
		$representedProducts = array();
		foreach ($items as $productId => $qty) {
			if ($representedProductId = $link->getRepresentedProductId($productId)) {
				if (!isset($representedProducts[$representedProductId])) {
					$representedProducts[$representedProductId] = $qty;
				}
				else {
					$representedProducts[$representedProductId] += $qty;
				}
			}
			elseif ($link->isRepresentedProduct($productId)) {
				//if (!isset($representedProducts[$productId])) {
				//	$representedProducts[$productId] = $qty;
				//}
				//else {
				//	$representedProducts[$productId] += $qty;
				//}
			}
		}
		$productIds = array($representedProductId => $representedProductId);
		
		if ($requireTransaction) $this->_getWriteAdapter()->beginTransaction();
        try {
        	$sql = '';
        	$stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
        	foreach ($representedProducts as $representedProductId => $delta) {
        		$diff = $multiplier * $delta;
        		$sql .= "UPDATE {$this->getTable('cataloginventory/stock_item')} 
					SET `qty` = `qty` + $diff, {$this->isInStockUpdate("`qty` + $diff >= `min_qty`")}
					WHERE (`product_id` = {$representedProductId}) AND (`stock_id` = $stockId);
					";
        	}
			$this->_getWriteAdapter()->multi_query($sql);
        	Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts($productIds);
        	Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);
        	foreach ($representedProducts as $representedProductId => $delta) {
        		$this->updateRepresentingProducts($representedProductId, false);
        	}
			if ($requireTransaction) $this->_getWriteAdapter()->commit();
        }
        catch (Exception $e) {
        	if ($requireTransaction) $this->_getWriteAdapter()->rollback();
        	throw $e;
        }
		
		return $this;
	}

    public function updateStockProductMReprepresendedQty($productId, $requireTransaction = true) {
        $sql = "UPDATE {$this->getTable('cataloginventory/stock_item')}
                    SET `m_represented_qty` = `qty`
                    WHERE `product_id` = $productId";
        $this->_getWriteAdapter()->multi_query($sql);

        $entitySql = "UPDATE {$this->getTable('catalog/product')} e, {$this->getTable('cataloginventory/stock_item')} i
                          SET e.m_represented_qty = i.qty
                          WHERE i.product_id = e.entity_id AND i.product_id  = $productId";
        $this->_getWriteAdapter()->multi_query($entitySql);
        $this->updateTextQties(array($productId => $productId));

        try {
            if ($requireTransaction) $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            if ($requireTransaction) $this->_getWriteAdapter()->rollback();
            throw $e;
        }
        return $this;
    }

	public function updateAll($requireTransaction = true) {
		$link = Mage::getResourceModel('manapro_productfaces/link');
		
		if ($requireTransaction) $this->_getWriteAdapter()->beginTransaction();
        $sql = "UPDATE {$this->getTable('cataloginventory/stock_item')}
			    SET `m_represented_qty` = `qty`";
        $this->_getWriteAdapter()->multi_query($sql);

        $sql = "UPDATE {$this->getTable('catalog/product')} e, {$this->getTable('cataloginventory/stock_item')} i
                SET e.m_represented_qty = i.qty
                WHERE i.product_id = e.entity_id";
        $this->_getWriteAdapter()->multi_query($sql);
        $this->updateTextQties();

        if ($productIds = $link->getAllRepresentingProductIds()) {
		    $productIds = implode(',', $productIds);
            $sql = "UPDATE {$this->getTable('cataloginventory/stock_item')}
			SET `m_represented_qty` = 0, {$this->isInStockUpdate("`qty` > `min_qty`")}, `m_represents` = 0
			WHERE `product_id` IN ($productIds)";
            $this->_getWriteAdapter()->multi_query($sql);
        }
		try {
        	if ($productIds = $link->getAllRepresentedProductIds()) {
        		foreach ($productIds as $productId) {
        			$this->updateRepresentingProducts($productId, false, array('runRelatedIndexes' => false));
        		}
        	}
        	Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexAll();
        	Mage::getResourceSingleton('catalog/product_indexer_price')->reindexAll();
            Mage::dispatchEvent('m_product_faces_reindex_all');

			if ($requireTransaction) $this->_getWriteAdapter()->commit();
        }
        catch (Exception $e) {
        	if ($requireTransaction) $this->_getWriteAdapter()->rollback();
        	throw $e;
        }
		
		return $this;
    }

    public function noBackOrders() {
        return Mage_CatalogInventory_Model_Stock::BACKORDERS_NO;
    }

    public function configBackOrders() {
        return (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS);
    }

    public function isInStockUpdate($condition) {
        return "`is_in_stock` = IF((use_config_backorders = 1 AND " .
                "{$this->noBackOrders()} <> {$this->configBackOrders()})" .
                " OR (use_config_backorders = 0 AND backorders <> {$this->noBackOrders()}), 1," .
                " IF ($condition, 1, 0))";

    }

    public function updateTextQties($productIds = null) {
        $db = $this->_getWriteAdapter();
        $attribute = $this->coreHelper()->getAttribute('catalog_product', 'm_represented_qty_text',
            array('attribute_id', 'entity_type_id', 'backend_table', 'backend_type'));

        $fields = array(
             'entity_id' => "`e`.`entity_id`",
             'attribute_id' => $attribute['attribute_id'],
             'store_id' => 0,
             'entity_type_id' => $attribute['entity_type_id'],
             'value' => "TRIM(TRAILING '.' FROM TRIM(TRAILING '0' from CAST(`e`.`m_represented_qty` AS CHAR)))",
        );

        /* @var $select Varien_Db_Select */
        $select = $db->select()
            ->from(array('e' => $this->getTable('catalog/product')), null);

        $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

        if ($productIds !== null && is_array($productIds) && count($productIds) > 0) {
            $select->where("`e`.`entity_id` IN (?)", $productIds);
        }

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $selectSql = $select->__toString();
        $sql = $select->insertFromSelect($this->coreHelper()->getAttributeTable($attribute, 'catalog_product_entity'), array_keys($fields));

        // run the statement
        $db->exec($sql);

    }
    #region Dependencies
    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
        return Mage::helper('mana_core/db');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    #endregion
}