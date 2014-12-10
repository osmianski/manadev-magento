<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Block_Wysiwyg  extends Mana_Admin_Block_V3_Field {
    protected function _construct() {
        $this->setTemplate('mana/content/v3/wysiwyg.phtml');
    }


    protected function _prepareClientSideBlock() {
        $this->setMClientSideBlock(
            array(
                'type' => 'Mana/Content/Wysiwyg',
                'self_contained' => true
            )
        );

        return $this;
    }
}