<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

class Mana_Sorting_Rewrite_AttributePageToolbar extends Mana_Sorting_Rewrite_Toolbar
{
    protected $_pageVarName         = 'product-list-page';
    protected $_orderVarName        = 'product-list-order';
    protected $_directionVarName    = 'product-list-dir';
    protected $_modeVarName         = 'product-list-mode';
    protected $_limitVarName        = 'product-list-limit';

    public function getPagerUrl($params = array()) {
        // Pager URL is processed in the custom pager block so that it works well with the option list pager
        return $this->getChild('product_list_toolbar_pager')->getPagerUrl($params);
    }
}