<?php
/**
 * @category    Mana
 * @package     Mana_Social
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Social_Block_Share extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mana/social/share.phtml');
    }

    protected function _prepareLayout()
    {
        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');
        $layoutHelper->delayPrepareLayout($this);

        return $this;
    }

    public function delayedPrepareLayout()
    {
        /* @var $social Mana_Social_Model_Social */
        $social = Mage::getModel('mana_social/social');

        /* @var $layout Mage_Core_Model_Layout */
        $layout = $this->getLayout();

        /* @var $actionModels Mana_Social_Model_Share[] */
        $actionModels = array();
        foreach ($social->getSharingActions() as $actionModel) {
            if ($sortOrder = $this->getData("{$actionModel->getFullCode()}_sort_order")) {
                $actionModels[] = compact('actionModel', 'sortOrder');
            }
        }
        usort($actionModels, array($this, '_compareActionModels'));
        foreach ($actionModels as $key => $value) {
            $actionModels[$key] = $value['actionModel'];
        }

        foreach ($actionModels as $actionModel) {
            $blockName = $this->_getSharingActionLayoutName($actionModel);

            $blockType = $this->_getBlockSuffix() && $actionModel->hasData('block'. $this->_getBlockSuffix())
                ? $actionModel->getData('block' . $this->_getBlockSuffix())
                : $actionModel->getBlock();

            $block = $layout->createBlock($blockType, $blockName, array(
                'model' => $actionModel,
            ));
            if ($this->_initSharingAction($block)) {
                $this->setChild($actionModel->getFullCode() . "_action", $block);
            }
        }
    }

    public function _compareActionModels($a, $b) {
        if ($a['sortOrder'] < $b['sortOrder']) return -1;
        if ($a['sortOrder'] > $b['sortOrder']) return 1;
        return 0;
    }

    /**
     * @param Mana_Social_Model_Share $action
     * @return string
     */
    protected function _getSharingActionLayoutName($action)
    {
        return $this->getNameInLayout().'.'. $action->getFullCode()."_action";
    }

    /**
     * @param Mage_Core_Block_Abstract $action
     * @return Mana_Social_Block_Share
     */
    protected function _initSharingAction($action) {
        return true;
    }

    protected function _getBlockSuffix() {
        return '';
    }

    /**
     * @return Mage_Core_Block_Abstract[]
     */
    public function getSharingActions() {
        $result = array();

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        foreach ($this->getChild() as $alias => $child) {
            if ($core->endsWith($alias, '_action')) {
                $result[$alias] = $child;
            }
        }
        return $result;
    }

    public function getCount() {
        return count($this->getSharingActions());
    }
}