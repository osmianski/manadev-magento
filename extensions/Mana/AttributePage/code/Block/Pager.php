<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Mana_AttributePage_Block_Pager extends Mage_Page_Block_Html_Pager
{
    public function getPagerUrl($params = array()) {
        /** @var Mana_AttributePage_Block_Option_List $optionListBlock */
        $optionListBlock = $this->getLayout()->getBlock('option_list');
        /** @var Mana_AttributePage_Block_ProductListToolbar $toolbarBlock */
        $toolbarBlock = $this->getLayout()->getBlock('product_list_toolbar');

        /** @var array $paramsToRetain */
        $paramsToRetain = array(
            $optionListBlock->getLimitVarName() => '',
            $optionListBlock->getPageVarName() => '',

            $toolbarBlock->getLimitVarName() => '',
            $toolbarBlock->getPageVarName() => '',
            $toolbarBlock->getModeVarName() => '',
            $toolbarBlock->getOrderVarName() => '',
            $toolbarBlock->getDirectionVarName() => '',
        );

        /** @var array $oldParams */
        $oldParams = array_intersect_key($this->getRequest()->getParams(), $paramsToRetain);
        $params = array_merge($oldParams, $params);

        $urlParams = array();
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;

        return $this->getUrl('*/*/*', $urlParams);
    }
}