<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Block_Adminhtml_Url_ListContainer extends Mana_Admin_Block_V2_Container {
    public function __construct() {
        parent::__construct();
        $this->_headerText = $this->__('SEO URL Keys');
    }
}