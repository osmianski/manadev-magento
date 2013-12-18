<?php
/**
 * @category    Mana
 * @package     ManaProduct_Tag
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mage_Catalog_Block_Product_Abstract getTargetBlock()
 * @method ManaProduct_Tag_Block_Abstract setTargetBlock(Mage_Catalog_Block_Product_Abstract $value)
 * @method Mage_Catalog_Model_Product getProduct()
 * @method ManaProduct_Tag_Block_Abstract setProduct(Mage_Catalog_Model_Product $value)
 * @method int|bool getWidth()
 * @method ManaProduct_Tag_Block_Abstract setWidth(mixed $value)
 * @method int|bool getHeight()
 * @method ManaProduct_Tag_Block_Abstract setHeight(mixed $value)
 */
class ManaProduct_Tag_Block_Abstract extends Mage_Core_Block_Template
{
    public function getTagClasses() {
        $result = array();

        $sizeClasses = array();
        if (($xml = $this->tagHelper()->getSizeBasedClassXml()) && ($this->getWidth() || $this->getHeight())) {
            foreach ($xml->children() as $ruleXml) {
                if ($this->getWidth()) {
                    if (isset($ruleXml['min-width']) && ((int)(string)$ruleXml['min-width'] >= $this->getWidth())) {
                        continue;
                    }
                    if (isset($ruleXml['max-width']) && ((int)(string)$ruleXml['max-width'] < $this->getWidth())) {
                        continue;
                    }
                }
                if ($this->getHeight()) {
                    if (isset($ruleXml['min-height']) && ((int)(string)$ruleXml['min-height'] >= $this->getHeight())) {
                        continue;
                    }
                    if (isset($ruleXml['max-height']) && ((int)(string)$ruleXml['max-height'] < $this->getHeight())) {
                        continue;
                    }
                }
                $sizeClasses[] = (string)$ruleXml['class'];
            }
        }
        $sizeClasses = implode(' ', $sizeClasses);

        if ($this->getProduct()->getPrice() != $this->getProduct()->getFinalPrice()) {
            $result[] = 'm-tag m-sale '. $sizeClasses;
        }

        return $result;
    }

    #region Dependencies
    /**
     * @return ManaProduct_Tag_Helper_Data
     */
    public function tagHelper() {
        return Mage::helper('manaproduct_tag');
    }
    #endregion
}