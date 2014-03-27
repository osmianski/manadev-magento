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
abstract class Mana_Menu_Block_Container extends Mana_Menu_Block_Abstract {
    protected $_contentBlockType;
    protected function _construct() {
        $this->setTemplate('mana/menu/container/sidebar.phtml');
        parent::_construct();
    }

    public  function delayedPrepareLayout() {
        $id = 'content';
        $data = $this->getData();
        $childBlock = $this->getLayout()->createBlock($this->_contentBlockType, $this->getNameInLayout().'_'. $id, $data);
        $this->setChild($id, $childBlock);
    }

    public function getTitle() {
        if ($title = $this->_getData('title')) {
            return $title;
        }
        elseif ($xml = $this->getXml()) {
            return (string)$xml->container->title;
        }
        else {
            return '';
        }
    }
}