<?php
/**
 * @category    Mana
 * @package     Mana_ProductLists
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once  BP.DS.'app'.DS.'code'.DS.'core'.DS.'Mage'.DS.'Adminhtml'.DS.'controllers'.DS.'Catalog'.DS.'ProductController.php';

/**
 * Base class for specific product list admin actions
 * @author Mana Team
 *
 */
class Mana_ProductLists_Controller_Admin  extends Mage_Adminhtml_Catalog_ProductController {
	protected $_linkType = '';
	
	/**
	 * AJAX action, renders initial tab markup
	 */
	public function tabAction() {
        $product = $this->_initProduct();
        
        $this->loadLayout();
	    $this->getLayout()->getBlock('catalog.product.edit.tab.'.$this->_linkType)
	    	->setClientData($this->getRequest()->getPost('selected_'.$this->_linkType, null));

        $this->renderLayout();
	}
	public function gridAction() {
        $product = $this->_initProduct();

        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.'.$this->_linkType)
            ->setClientData($this->getRequest()->getPost('selected_'.$this->_linkType, null));
        
        $this->renderLayout();
	}
}