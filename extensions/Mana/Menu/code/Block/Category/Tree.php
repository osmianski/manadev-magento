<?php
/** 
 * @category    Mana
 * @package     Mana_Menu
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Menu_Block_Category_Tree extends Mana_Menu_Block_Tree_Container {
    protected function _construct() {
        $xml = <<<EOT
<widget>
    <generators>
        <category>
            <model>mana_menu/generator_category</model>
        </category>
    </generators>
</widget>
EOT;

        $this->setData('xml', $xml);
        parent::_construct();
    }
}