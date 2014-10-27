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
class Mana_Content_Block_Tree extends Mana_Menu_Block_Tree_Container {
    protected function _construct() {
        $xml = <<<EOT
<widget>
    <generators>
        <category>
            <model>mana_content/generator_tree</model>
        </category>
    </generators>
</widget>
EOT;

        $this->setData('xml', $xml);
        parent::_construct();
    }

    protected function _prepareLayout() {
        // create client-side block
        $this->_prepareClientSideBlock();

        return parent::_prepareLayout();
    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');

        $data = array(
            'type' => 'Mana/Content/Tree',
        );

        $this->setData('m_client_side_block', $data);
        return $this;
    }
}