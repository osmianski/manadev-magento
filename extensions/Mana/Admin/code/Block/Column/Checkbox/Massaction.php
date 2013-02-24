<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Column_Checkbox_Massaction extends Mana_Admin_Block_Column_Checkbox {
    public function renderCss() {
        return parent::renderCss().' ct-massaction';
    }
}