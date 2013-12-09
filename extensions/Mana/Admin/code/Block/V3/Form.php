<?php
/** 
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_V3_Form extends Mana_Admin_Block_V2_Form {
    /**
     * @return Mana_Admin_Block_V3_Field
     */
    public function getFieldRenderer() {
        return $this->getLayout()->getBlockSingleton('mana_admin/v3_field');
    }
}