<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Page_Block_Expr extends Mana_Page_Block_Filter {
    /**
     * @return Mana_Page_Block_Filter
     */
    public function prepareProductCollection() {
        $condition = array();
        foreach ($this->getChild() as $childBlock) {
            if ($childBlock instanceof Mana_Page_Block_Filter) {
                if ($childCondition = $childBlock
                    ->setProductCollection($this->_productCollection)
                    ->prepareProductCollection()
                    ->getCondition())
                {
                    $condition[] = "($childCondition)";
                }

            }
        }
        $this->_condition = strtolower($this->getOperation()) == 'or'
            ? implode(' OR ', $condition)
            : implode(' AND ', $condition);

        return $this;
    }
}