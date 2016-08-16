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
    protected $_orderVarName        = 'order2';
    protected $_directionVarName    = 'dir2';
    protected $_modeVarName         = 'mode2';
    protected $_limitVarName        = 'limit2';

    public function getPagerUrl($params = array()) {
        // Pager URL is processed in the custom pager block so that it works well with the option list pager
        return $this->getChild('product_list_toolbar_pager')->getPagerUrl($params);
    }
}