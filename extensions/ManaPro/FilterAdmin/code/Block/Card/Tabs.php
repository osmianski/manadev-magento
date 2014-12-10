<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdmin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterAdmin_Block_Card_Tabs extends Mana_Admin_Block_Crud_Card_Tabs {
	public function getActiveTabName() {
		if (($tabName = Mage::app()->getRequest()->getParam('tab')) 
			&& ($tabBlock = $this->getChild($tabName))
			&& !$tabBlock->isHidden()) 
		{
			return $tabName;
		}
		else {
			return 'general';
		}
	}
	public function getActiveTabBlock() {
		return $this->getChild($this->getActiveTabName());
	}
	protected function _beforeToHtml() {
		foreach ($this->getSortedChildren() as $tabName) {
			$tabBlock = $this->getChild($tabName);
            $this->addTab($tabName, $tabBlock);
//            if ($tabName == $this->getActiveTabName()) {
//				$this->addTab($tabName, $tabBlock);
//			}
//			else {
//				$this->addTab($tabName, array(
//					'id' => $tabBlock->getNameInLayout(),
//					'label' => $tabBlock->getTabLabel(),
//					'title' => $tabBlock->getTabTitle(),
//					'class' => 'ajax',
//					'url' => $tabBlock->getAjaxUrl(),
//					'is_hidden' => $tabBlock->isHidden(),
//				));
//			}
		}
		$this->setActiveTab($this->getActiveTabName());
		return parent::_beforeToHtml();
	}
}