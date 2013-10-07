<?php

class Local_Manadev_Model_Template_Filter extends Mage_Widget_Model_Template_Filter {
    /* (non-PHPdoc)
     * @see Mage_Core_Model_Email_Template_Filter::blockDirective()
     * This method is copied from Mage_Core_Model_Email_Template_Filter::blockDirective(). Changes marked with comments.
     */
    public function blockDirective($construction)
    {
        $skipParams = array('type', 'id', 'output', 'name');
        $blockParameters = $this->_getIncludeParameters($construction[2]);
        /* @var $layout Mage_Core_Model_Layout */ $layout = Mage::app()->getLayout();

        if (isset($blockParameters['type'])) {
            $type = $blockParameters['type'];
            $block = $layout->createBlock($type, null, $blockParameters);
        } elseif (isset($blockParameters['id'])) {
            $block = $layout->createBlock('cms/block');
            if ($block) {
                $block->setBlockId($blockParameters['id']);
            }
        }
        /* MANA BEGIN */ 
        elseif (isset($blockParameters['name'])) {
            $block = $layout->getBlock($blockParameters['name']);
        }
        /* MANA END */

        if ($block) {
            $block->setBlockParams($blockParameters);
            foreach ($blockParameters as $k => $v) {
                if (in_array($k, $skipParams)) {
                    continue;
                }
                $block->setDataUsingMethod($k, $v);
            }
        }

        if (!$block) {
            return '';
        }
        if (isset($blockParameters['output'])) {
            $method = $blockParameters['output'];
        }
        if (!isset($method) || !is_string($method) || !is_callable(array($block, $method))) {
            $method = 'toHtml';
        }
        return $block->$method();
    }
}