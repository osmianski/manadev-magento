<?php
/**
 * @category    Mana
 * @package     ManaProduct_Tab
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mage_Catalog_Block_Product_View getBlock()
 * @method bool getWrapCollateral()
 * @method string getAlias()
 * @method string getTitle()
 * @method string getDisplay()
 * @method int getPosition()
 *
 * @method ManaProduct_Tab_Model_Tab setBlock(Mage_Catalog_Block_Product_View $value)
 * @method ManaProduct_Tab_Model_Tab setWrapCollateral(bool $value)
 * @method ManaProduct_Tab_Model_Tab setAlias(string $value)
 * @method ManaProduct_Tab_Model_Tab setTitle(string $value)
 * @method ManaProduct_Tab_Model_Tab setDisplay(string $value)
 * @method ManaProduct_Tab_Model_Tab setPosition(int $value)
 */
class ManaProduct_Tab_Model_Tab extends Varien_Object {
    protected $_html;
    protected $_isRendered = false;
    public function getHtml() {
        if (!$this->_isRendered) {
            $this->_html = trim($this->getBlock()->getChildHtml($this->getAlias()));
            $this->_isRendered = true;
        }
        return $this->_html;
    }

    public function getCssAlias() {
        return str_replace('_', '-', str_replace('.', '-', $this->getAlias()));
    }
}