<?php
/**
 * @category    Mana
 * @package     ManaProduct_Tag
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaProduct_Tag_Block_After extends ManaProduct_Tag_Block_Abstract
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('manaproduct/tag/after.phtml');
    }
}