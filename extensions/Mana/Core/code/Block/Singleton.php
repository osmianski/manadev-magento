<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Block_Singleton extends Mage_Core_Block_Text_List {
    protected $_singletons = array();
    public function addSingletonBlock($type, $name, $template = null, $before = null) {
        if (!isset($this->_singletons[$name])) {
            $this->_singletons[$name] = $block = $this->getLayout()->createBlock($type, $name);
            if ($template) {
                $block->setTemplate($template);
            }
            if ($before) {
                $this->insert($block, $before, false, $name);
            }
            else {
                $this->append($block, $name);
            }

        }
        return $this;
    }
}